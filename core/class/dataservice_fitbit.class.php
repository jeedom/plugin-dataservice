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

class dataservice_fitbit {
  
  public static function refreshData($_eqLogic){
    $data = array();
    foreach ($_eqLogic->getCmd('info') as $cmd) {
      if(!isset($data[$cmd->getConfiguration('path')])){
        $data[$cmd->getConfiguration('path')] = self::getData($cmd->getConfiguration('path'),$_eqLogic->getId());
        $data[$cmd->getConfiguration('path')]['datetime'] = date('Y-m-d H:i:s');
        if($data[$cmd->getConfiguration('path')]['date'] && $data[$cmd->getConfiguration('path')]['time']){
          $data[$cmd->getConfiguration('path')]['datetime'] = $data[$cmd->getConfiguration('path')]['date'].' '.$data[$cmd->getConfiguration('path')]['time'];
        }
        if($data[$cmd->getConfiguration('path')][0]['date'] && $data[$cmd->getConfiguration('path')][0]['time']){
          $data[$cmd->getConfiguration('path')][0]['datetime'] = $data[$cmd->getConfiguration('path')][0]['date'].' '.$data[$cmd->getConfiguration('path')]['time'];
        }
      }
      $value = self::getValue($cmd->getLogicalId(),$data[$cmd->getConfiguration('path')]);
      if($value['value'] !== null && $value['value'] != ''){
        $_eqLogic->checkAndUpdateCmd($cmd, $value['value'],$value['datetime']);
      }
    }
  }
  
  public static function getValue($_key,$_data,$_default = null){
    $datetime = date('Y-m-d H:i:s');
    $value = $_data;
    $keys = explode('::',$_key);
    foreach ($keys as &$key) {
      if(!isset($value[$key])){
        return array('value' => null);
      }
      $value = $value[$key];
      if(isset($value['date']) && isset($value['time'])){
        $datetime = $value['date'].' '.$value['time'];
      }
    }
    return array('value' => $value,'datetime' => $datetime);
  }
  
  public static function getData($_path,$_user_logical){
    $_path = str_replace('#date#',date('Y-m-d'),$_path);
    $url = config::byKey('service::cloud::url').'/service/fitbit?path='.urlencode($_path);
    $url .= '&user_logical='.$_user_logical;
    $request_http = new com_http($url);
    $request_http->setHeader(array('Content-Type: application/json','Autorization: '.sha512(mb_strtolower(config::byKey('market::username')).':'.config::byKey('market::password'))));
    $result = json_decode($request_http->exec(30,1),true);
    if(!is_array($result)){
      throw new Exception(__('[Fitbit] Erreur lors de la récuperation des données : ',__FILE__).$result);
    }
    return $result;
  }
  
  
}
