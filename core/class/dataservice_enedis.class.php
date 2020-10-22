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

class dataservice_enedis {
  
  public static function refreshData($_eqLogic){
    $start_date = date('Y-m-d',strtotime('now -1 day'));
    $end_date = date('Y-m-d');
    
    $data = self::getData('/metering_data/daily_consumption?start='.$start_date.'&end='.$end_date.'&usage_point_id='.$_eqLogic->getConfiguration('enedis::usage_point_id'));
    if(isset($data['meter_reading']) && isset($data['meter_reading']['interval_reading'])){
      $value = end($data['meter_reading']['interval_reading']);
      $_eqLogic->checkAndUpdateCmd('daily_consumption', $value['value'],$value['date']);
    }
    
    $data = self::getData('/metering_data/daily_consumption_max_power?start='.$start_date.'&end='.$end_date.'&usage_point_id='.$_eqLogic->getConfiguration('enedis::usage_point_id'));
    if(isset($data['meter_reading']) && isset($data['meter_reading']['interval_reading'])){
      $value = end($data['meter_reading']['interval_reading']);
      $_eqLogic->checkAndUpdateCmd('daily_consumption_max_power', $value['value'],$value['date']);
    }
    
    $data = self::getData('/metering_data/daily_production?start='.$start_date.'&end='.$end_date.'&usage_point_id='.$_eqLogic->getConfiguration('enedis::usage_point_id'));
    if(isset($data['meter_reading']) && isset($data['meter_reading']['interval_reading'])){
      $value = end($data['meter_reading']['interval_reading']);
      $_eqLogic->checkAndUpdateCmd('daily_production', $value['value'],$value['date']);
    }
    
    $data = self::getData('/metering_data/daily_production_max_power?start='.$start_date.'&end='.$end_date.'&usage_point_id='.$_eqLogic->getConfiguration('enedis::usage_point_id'));
    if(isset($data['meter_reading']) && isset($data['meter_reading']['interval_reading'])){
      $value = end($data['meter_reading']['interval_reading']);
      $_eqLogic->checkAndUpdateCmd('daily_production_max_power', $value['value'],$value['date']);
    }
  }
  
  public static function getData($_path){
    $url = config::byKey('service::cloud::url').'/service/enedis?path='.urlencode($_path);
    $request_http = new com_http($url);
    $request_http->setHeader(array('Content-Type: application/json','Autorization: '.sha512(mb_strtolower(config::byKey('market::username')).':'.config::byKey('market::password'))));
    $result = json_decode($request_http->exec(30,1),true);
    if(isset($result['error']) && !in_array($result['error'],array('Not found'))){
      throw new \Exception($result['error'].' => '.$result['error_description']);
    }
    return $result;
  }
  
}
