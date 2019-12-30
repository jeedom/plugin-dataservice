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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

function dataservice_install() {
  if(config::byKey('info::latitude') != ''){
    config::save('sharedata::lat',config::byKey('info::latitude'),'dataservice');
  }
  if(config::byKey('info::longitude') != ''){
    config::save('sharedata::long',config::byKey('info::longitude'),'dataservice');
  }
  if(config::byKey('info::nbOccupants') != ''){
    config::save('sharedata::nbOccupant',config::byKey('info::nbOccupant'),'dataservice');
  }
  
  
}

function dataservice_update() {
  
}


function dataservice_remove() {
  
}

?>
