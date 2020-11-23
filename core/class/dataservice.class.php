<?php

/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';
include_file('core', 'dataservice_mail', 'class', 'dataservice');
include_file('core', 'dataservice_twilio', 'class', 'dataservice');
include_file('core', 'dataservice_enedis', 'class', 'dataservice');
include_file('core', 'dataservice_fitbit', 'class', 'dataservice');

class dataservice extends eqLogic {
  /*     * *************************Attributs****************************** */
  
  /*     * ***********************Methode static*************************** */
  
  public static function sendJeedomConfig() {
    $market = repo_market::getJsonRpc();
    if (!$market->sendRequest('dataservice::configCallback', array('dataservice::apikey' => jeedom::getApiKey('dataservice'),'dataservice::url' => network::getNetworkAccess('external')))) {
      throw new Exception($market->getError(), $market->getErrorCode());
    }
  }
  
  public function getShareDataService(){
    return array(
      'temperature_ext' => array('name' => __('Température extérieure',__FILE__),'key' => 'sharedata::temperature_ext','unit' => array('°C')),
      'humidity_ext' => array('name' => __('Humidité extérieure',__FILE__),'key' => 'sharedata::humidity_ext','unit' => array('%')),
      'luminosity_ext' => array('name' => __('Luminosité exterieure',__FILE__),'key' => 'sharedata::luminosity_ext'),
      'pressure_ext' => array('name' => __('Pression extérieure',__FILE__),'key' => 'sharedata::pressure_ext'),
      'rain' => array('name' => __('Pluie',__FILE__),'key' => 'sharedata::rain','unit' => array('mm')),
      'wind' => array('name' => __('Vent',__FILE__),'key' => 'sharedata::wind','unit' => array('km/h'),'convert' => array('m/s' => '#value#*3.6')),
      'consumption_electricity' => array('name' => __('Consommation journaliere éléctrique',__FILE__),'key' => 'sharedata::consumption_electricity','unit' => array('kWh'),'occupantDepend' => true,'history' => 'electricity'),
      'consumption_gaz' => array('name' => __('Consommation journaliere gaz',__FILE__),'key' => 'sharedata::consumption_gaz','unit' => array('kWh'),'convert' => array('m3' => '#value#*10.91'),'occupantDepend' => true,'history' => 'gaz'),
      'consumption_water' => array('name' => __('Consommation journaliere eau',__FILE__),'key' => 'sharedata::consumption_water','unit' => array('m3'),'occupantDepend' => true,'history' => 'water')
    );
  }
  
  public function cron15(){
    if(date('H') == 0 && date('i') < 15){
      return;
    }
    $data = array(
      'lat' => config::byKey('info::latitude'),
      'long' => config::byKey('info::longitude'),
      'datas' => array()
    );
    if($data['lat'] == '' || $data['long'] == ''){
      return;
    }
    sleep(rand(0,60));
    $occupant = config::byKey('info::nbOccupant');
    $shareDataService = dataservice::getShareDataService();
    foreach ($shareDataService as $key => $service) {
      $cmd = cmd::byId(str_replace('#','',config::byKey($service['key'],'dataservice')));
      if(!is_object($cmd)){
        continue;
      }
      $value = $cmd->execCmd();
      if(in_array($cmd->getEqType(),array('dataservice','weather'))){
        continue;
      }
      if(isset($service['unit']) && !in_array($cmd->getUnite(),$service['unit'])){
        $convert = false;
        if(isset($service['convert'])){
          foreach ($service['convert'] as $unit => $calcul) {
            if($unit == $cmd->getUnite()){
              $value = evaluate(str_replace('#value#',$value,$calcul));
              $convert = true;
            }
          }
        }
        if(!$convert){
          continue;
        }
      }
      if(isset($service['occupantDepend']) && $service['occupantDepend']){
        if($occupant == '' || $occupant < 1){
          continue;
        }
        $value = $value / $occupant;
      }
      $data['datas'][$key] = array(
        'value' => $value
      );
    }
    if(count($data['datas']) == 0){
      return;
    }
    $url = config::byKey('service::cloud::url').'/service/sharedata';
    $request_http = new com_http($url);
    $request_http->setHeader(array(
      'Content-Type: application/json',
      'Autorization: '.sha512(mb_strtolower(config::byKey('market::username')).':'.config::byKey('market::password'))
    ));
    $request_http->setPost(json_encode($data));
    try {
      $request_http->exec(10,1);
    } catch (\Exception $e) {
      
    }
  }
  
  public static function getShareHistory($_history,$_radius,$_startDate,$_endDate){
    $url = config::byKey('service::cloud::url').'/service/sharedata';
    $url .='?history='.$_history;
    $url .='&radius='.$_radius;
    $url .='&startTime='.strtotime($_startDate);
    $url .='&endTime='.strtotime($_endDate);
    $url .='&lat='.config::byKey('info::latitude');
    $url .='&long='.config::byKey('info::longitude');
    $request_http = new com_http($url);
    $request_http->setHeader(array(
      'Autorization: '.sha512(mb_strtolower(config::byKey('market::username')).':'.config::byKey('market::password'))
    ));
    $datas = json_decode($request_http->exec(30,1),true);
    if($datas['state'] != 'ok'){
      throw new \Exception(__('Erreur sur la récuperation des données : ',__FILE__).json_encode($datas));
    }
    $return = array();
    foreach ($datas['data'] as $value) {
      $return[] = array((strtotime($value['time']) - 86400)*1000,$value[$_history]);
    }
    return $return;
  }
  
  
  public static function tts($_filename,$_text) {
    try {
      $url = config::byKey('service::cloud::url').'/user/';
      $url .= sha512(mb_strtolower(config::byKey('market::username')).':'.config::byKey('market::password'));
      $url .= '/service/tts';
      $url .= '?lang='.config::byKey('language', 'core', 'fr_FR');
      $url .= '&text='.urlencode($_text);
      $request_http = new com_http(trim($url,'&'));
      $datas = $request_http->exec();
      if(is_json($datas)){
        throw new \Exception(__('Erreur sur la récuperation des données : ',__FILE__).$datas);
      }
      file_put_contents($_filename, $datas);
    } catch (Exception $e) {
      log::add('dataservice', 'error', '[TTS] ' . $e->getMessage());
    }
  }
  
  public static function updateData(){
    foreach (eqLogic::byType('dataservice',true) as $eqLogic) {
      $cron = $eqLogic->getConfiguration('cron');
      if ($cron != '') {
        try {
          $c = new Cron\CronExpression(checkAndFixCron($cron), new Cron\FieldFactory);
          if ($c->isDue()) {
            $eqLogic->refreshData();
          }
          $eqLogic->setCache('refresh_error',0);
        } catch (Exception $e) {
          $eqLogic->setCache('refresh_error',$eqLogic->getCache('refresh_error',0) + 1);
          if($eqLogic->getCache('refresh_error',0) > 3){
            log::add('dataservice', 'error', __('Erreur sur ', __FILE__) . $eqLogic->getHumanName() . ' : ' . $e->getMessage());
          }
        }
      }
    }
  }
  
  public static function devicesParameters($_device = '') {
    $return = array();
    foreach (ls(dirname(__FILE__) . '/../config/services', '*') as $file) {
      try {
        $content = file_get_contents(dirname(__FILE__) . '/../config/services/' . $file);
        if (is_json($content)) {
          $return += json_decode($content, true);
        }
      } catch (Exception $e) {
        
      }
    }
    if (isset($_device) && $_device != '') {
      if (isset($return[$_device])) {
        return $return[$_device];
      }
      return array();
    }
    return $return;
  }
  
  /*     * *********************Méthodes d'instance************************* */
  
  public function preInsert(){
    $services = dataservice::devicesParameters();
    foreach ($services as $key => $service) {
      foreach ($service['configuration'] as $key2 => $value) {
        if(isset($value['default'])){
          $this->setConfiguration($key.'::'.$key2,$value['default']);
        }else if($key2 == 'lat'){
          $this->setConfiguration($key.'::'.$key2,config::byKey('info::latitude'));
        }else if($key2 == 'long'){
          $this->setConfiguration($key.'::'.$key2,config::byKey('info::longitude'));
        }else if($key2 == 'departement'){
          $this->setConfiguration($key.'::'.$key2,substr(config::byKey('info::postalCode'),0,2));
        }else if($key2 == 'country'){
          $this->setConfiguration($key.'::'.$key2,config::byKey('info::stateCode'));
        }
      }
    }
  }
  
  public function postSave() {
    if ($this->getConfiguration('applyService') != $this->getConfiguration('service')) {
      $this->applyModuleConfiguration();
    }
  }
  
  public function applyModuleConfiguration() {
    $this->setConfiguration('applyService', $this->getConfiguration('service'));
    $this->save();
    if ($this->getConfiguration('service') == '') {
      return true;
    }
    $device = self::devicesParameters($this->getConfiguration('service'));
    $device = $device['eqLogic'];
    if (!is_array($device)) {
      return true;
    }
    $this->import($device);
  }
  
  public function refreshData(){
    if($this->getConfiguration('service') == ''){
      return;
    }
    $service = $this->getConfiguration('service');
    $device = self::devicesParameters($service);
    if(isset($device['noRefreshData'])){
      return;
    }
    $class='dataservice_'.$service;
    $function = 'refreshData';
    if(class_exists($class) && method_exists($class,$function)){
      return $class::$function($this);
    }
    $url = config::byKey('service::cloud::url').'/service/'.$this->getConfiguration('service');
    if(count($device['configuration']) > 0){
      $url .= '?';
      foreach ($device['configuration'] as $key => $value) {
        $url .= $key.'='.urlencode($this->getConfiguration($service.'::'.$key)).'&';
      }
    }
    if(isset($device['translation']) && $device['translation']){
      $url .= 'lang='.substr(config::byKey('language'),0,2);
    }
    $request_http = new com_http(trim($url,'&'));
    $request_http->setHeader(array('Autorization: '.sha512(mb_strtolower(config::byKey('market::username')).':'.config::byKey('market::password'))));
    
    $datas = json_decode($request_http->exec(10),true);
    if($datas['state'] != 'ok'){
      throw new \Exception(__('Erreur sur la récuperation des données : ',__FILE__).json_encode($datas));
    }
    log::add('dataservice','debug',json_encode($datas));
    foreach ($this->getCmd('info') as $cmd) {
      $paths = explode('::',$cmd->getLogicalId());
      $value = $datas['data'];
      foreach ($paths as $path) {
        if(!isset($value[$path])){
          continue(2);
        }
        $value = $value[$path];
      }
      $cmd->event($value);
    }
  }
  
  public function getImage() {
    $file = dirname(__FILE__) . '/../img/' . $this->getConfiguration('service') . '.png';
    if (file_exists($file)) {
      return 'plugins/dataservice/core/img/' . $this->getConfiguration('service') . '.png';
    } else {
      return 'plugins/dataservice/plugin_info/dataservice_icon.png';
    }
  }
  
  /*     * **********************Getteur Setteur*************************** */
}

class dataserviceCmd extends cmd {
  /*     * *************************Attributs****************************** */
  
  
  /*     * ***********************Methode static*************************** */
  
  
  /*     * *********************Methode d'instance************************* */
  
  
  public function execute($_options = array()) {
    $eqLogic = $this->getEqLogic();
    if($this->getLogicalId() == 'refresh'){
      return $eqLogic->refreshData();
    }
    $service = $eqLogic->getConfiguration('service');
    $class='dataservice_'.$service;
    $function = 'cmd_execute';
    if(class_exists($class) && method_exists($class,$function)){
      return $class::$function($this,$_options);
    }
  }
  
  /*     * **********************Getteur Setteur*************************** */
}
