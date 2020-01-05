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
require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";
if (!jeedom::apiAccess(init('apikey'), 'dataservice')) {
  echo __('Vous n\'etes pas autorisé à effectuer cette action', __FILE__);
  die();
}
if (isset($_GET['test'])) {
  echo 'OK';
  die();
}
if (!isset($_GET['service'])) {
  echo 'Aucun service fourni';
  die();
}
$data = json_decode(file_get_contents("php://input"), true);
log::add('dataservice','debug','Received data for '.$_GET['service'].' => '.json_encode($data));
$class='dataservice_'.$_GET['service'];
$function = 'callback';
if(class_exists($class) && method_exists($class,$function)){
  echo $class::$function($data);
}
