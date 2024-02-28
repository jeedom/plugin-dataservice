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
include_file('core', 'dataservice_twilio', 'class', 'dataservice');

class dataservice extends eqLogic {
  /*     * *************************Attributs****************************** */
  
  /*     * ***********************Methode static*************************** */
  
  public static function sendJeedomConfig() {
    $market = repo_market::getJsonRpc();
    if (!$market->sendRequest('dataservice::configCallback', array('dataservice::apikey' => jeedom::getApiKey('dataservice'),'dataservice::url' => network::getNetworkAccess('external')))) {
      throw new Exception($market->getError(), $market->getErrorCode());
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
