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
  
  
  /*     * **********************Getteur Setteur*************************** */
}

class dataserviceCmd extends cmd {
  /*     * *************************Attributs****************************** */
  
  
  /*     * ***********************Methode static*************************** */
  
  
  /*     * *********************Methode d'instance************************* */
  
  
  public function execute($_options = array()) {
    
  }
  
  /*     * **********************Getteur Setteur*************************** */
}
