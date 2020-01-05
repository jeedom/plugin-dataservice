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
    $url = config::byKey('service_url','dataservice').'/user/';
    $url .= sha512(mb_strtolower(config::byKey('market::username')).':'.config::byKey('market::password'));
    $url .= '/service/twilio';
    $request_http = new com_http($url);
    $request_http->setHeader(array('Content-Type: application/json'));
    $request_http->setPost(json_encode($data));
    $datas = json_decode($request_http->exec(30,1),true);
    if($datas['state'] != 'ok'){
      throw new \Exception(__('Erreur sur la récuperation des données : ',__FILE__).json_encode($datas));
    }
  }
  
}
