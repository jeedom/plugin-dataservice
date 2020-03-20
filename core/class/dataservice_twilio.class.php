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

class dataservice_twilio {
  
  public static function cmd_execute($_cmd,$_options){
    $data = array(
      'to' => $_cmd->getConfiguration('to'),
      'text' => $_options['message']
    );
    $url = config::byKey('service::cloud::url').'/service/twilio';
    $request_http = new com_http($url);
    $request_http->setHeader(array('Content-Type: application/json','Autorization: '.sha512(mb_strtolower(config::byKey('market::username')).':'.config::byKey('market::password'))));
    $request_http->setPost(json_encode($data));
    $datas = json_decode($request_http->exec(30,1),true);
    if($datas['state'] != 'ok'){
      throw new \Exception(__('Erreur sur la récuperation des données : ',__FILE__).json_encode($datas));
    }
  }
  
  public static function callback($_data){
    $return = null;
    foreach (eqLogic::byType('dataservice',true) as $eqLogic) {
      if($eqLogic->getConfiguration('service') != 'twilio'){
        continue;
      }
      $match = null;
      foreach ($eqLogic->getCmd('action') as $cmd) {
        if($cmd->getConfiguration('to') != $_data['from'] && '0'.substr($_data['from'],3) != $cmd->getConfiguration('to')){
          continue;
        }
        $match = $cmd;
        break;
      }
      
      if($match == null){
        continue;
      }
      if(!is_object($cmd)){
        $cmd = new dataserviceCmd();
        $cmd->setLogicalId('receivedMessage'.$match->getId());
        $cmd->setIsVisible(1);
        $cmd->setName(__('Message de ', __FILE__).$match->getName());
        $cmd->setType('info');
        $cmd->setSubType('string');
        $cmd->setEqLogic_id($eqLogic->getId());
        $cmd->save();
      }
      $cmd->event($_data['text']);
      if($eqLogic->getConfiguration('twilio::allowInteract') == 1){
        $params = array('plugin' => 'dataservice');
        $reply = interactQuery::tryToReply(trim($_data['text']), $params);
        if (trim($reply['reply']) != '') {
          $return = $reply;
        }
      }
    }
    return json_encode($return);
  }
  
}
