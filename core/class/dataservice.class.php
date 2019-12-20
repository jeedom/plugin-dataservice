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

class dataservice extends eqLogic {
  /*     * *************************Attributs****************************** */
  
  /*     * ***********************Methode static*************************** */
  
  public function getShareDataService(){
    return array(
      'temperature_ext' => array('name' => __('Température extérieure',__FILE__),'key' => 'sharedata::temperature_ext'),
      'humidity_ext' => array('name' => __('Humidité extérieure',__FILE__),'key' => 'sharedata::humidity_ext'),
      'luminosity_ext' => array('name' => __('Luminosité exterieure',__FILE__),'key' => 'sharedata::luminosity_ext'),
      'pressure_ext' => array('name' => __('Pression extérieure',__FILE__),'key' => 'sharedata::pressure_ext'),
      'rain' => array('name' => __('Pluie',__FILE__),'key' => 'sharedata::rain'),
      'wind' => array('name' => __('Vent',__FILE__),'key' => 'sharedata::wind'),
      'consumption_electricity' => array('name' => __('Consommation éléectrique',__FILE__),'key' => 'sharedata::consumption_electricity'),
      'consumption_gaz' => array('name' => __('Consommation gaz',__FILE__),'key' => 'sharedata::consumption_gaz'),
      'consumption_water' => array('name' => __('Consommation eau',__FILE__),'key' => 'sharedata::consumption_water')
    );
  }
  
  public function cron15(){
    $data = array(
      'lat' => config::byKey('sharedata::lat','dataservice'),
      'long' => config::byKey('sharedata::long','dataservice'),
      'datas' => array()
    );
    if($data['lat'] == '' || $data['long'] == ''){
      return;
    }
    $shareDataService = dataservice::getShareDataService();
    foreach ($shareDataService as $key => $value) {
      $cmd = cmd::byId(str_replace('#','',config::byKey($value['key'],'dataservice')));
      if(!is_object($cmd)){
        continue;
      }
      if(in_array($cmd->getEqType(),array('dataservice','weather'))){
        continue;
      }
      $data['datas'][$key] = array(
        'value' => $cmd->execCmd()
      );
    }
    if(count($data['datas']) == 0){
      return;
    }
    sleep(rand(0,60));
    $url = config::byKey('service_url','dataservice').'/user/';
    $url .= sha512(mb_strtolower(config::byKey('market::username')).':'.config::byKey('market::password'));
    $url .= '/service/sharedata';
    $request_http = new com_http($url);
    $request_http->setHeader(array('Content-Type: application/json'));
    $request_http->setPost(json_encode($data));
    try {
      $request_http->exec(10,1);
    } catch (\Exception $e) {
      
    }
  }
  
  public static function tts($_text) {
    try {
      $url = config::byKey('service_url','dataservice').'/user/';
      $url .= sha512(mb_strtolower(config::byKey('market::username')).':'.config::byKey('market::password'));
      $url .= '/service/tts';
      $url .= '?lang='.config::byKey('language', 'core', 'fr_FR');
      $url .= '&text='.urlencode($_text);
      $request_http = new com_http(trim($url,'&'));
      $datas = $request_http->exec();
      if(is_json($datas)){
        throw new \Exception(__('Erreur sur la récuperation des données : ',__FILE__).$datas);
      }
      file_put_contents(jeedom::getTmpFolder('tts') . '/' . md5($_text) . '.mp3', $datas);
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
        } catch (Exception $exc) {
          log::add('dataservice', 'error', __('Expression cron non valide pour ', __FILE__) . $eqLogic->getHumanName() . ' : ' . $cron);
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
    $url = config::byKey('service_url','dataservice').'/user/';
    $url .= sha512(mb_strtolower(config::byKey('market::username')).':'.config::byKey('market::password'));
    $url .= '/service/'.$this->getConfiguration('service');
    $device = self::devicesParameters($service);
    if(count($device['configuration']) > 0){
      $url .= '?';
      foreach ($device['configuration'] as $key => $value) {
        $url .= $key.'='.urlencode($this->getConfiguration($service.'::'.$key)).'&';
      }
    }
    $request_http = new com_http(trim($url,'&'));
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
  
  /*     * **********************Getteur Setteur*************************** */
}

class dataserviceCmd extends cmd {
  /*     * *************************Attributs****************************** */
  
  
  /*     * ***********************Methode static*************************** */
  
  
  /*     * *********************Methode d'instance************************* */
  
  
  public function execute($_options = array()) {
    $eqLogic = $this->getEqLogic();
    if($this->getLogicalId() == 'refresh'){
      $eqLogic->refreshData();
    }
  }
  
  /*     * **********************Getteur Setteur*************************** */
}
