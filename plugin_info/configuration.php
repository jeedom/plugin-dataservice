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
include_file('core', 'authentification', 'php');
if (!isConnect()) {
  include_file('desktop', '404', 'php');
  die();
}
?>
<form class="form-horizontal" id="configureShareData">
  <fieldset>
    <legend>{{Partage de données}}</legend>
    <div class="alert alert-info">{{Le partage de données permet d'envoyer certaines données (que vous choisissez) à Jeedom, celle-ci sont anonymisées et permettent de comparer entre vous ces données. En échange de ce partage de données Jeedom augmentera votre quota de requete au service data}}</div>
    <div class="form-group">
      <label class="col-lg-2 control-label">{{Latitude}}</label>
      <div class="col-lg-1">
        <input class="configKey form-control" data-l1key="sharedata::lat"/>
      </div>
      <label class="col-lg-1 control-label">{{Longitude}}</label>
      <div class="col-lg-1">
        <input class="configKey form-control" data-l1key="sharedata::long"/>
      </div>
    </div>
    <?php
    $shareDataService = dataservice::getShareDataService();
    foreach ($shareDataService as $key => $value) {
      echo '<div class="form-group">';
      echo '<label class="col-lg-2 control-label">'.$value['name'].'</label>';
      echo '<div class="col-lg-3">';
      echo '<div class="input-group">';
      echo '<input class="configKey form-control" data-l1key="'.$value['key'].'"/>';
      echo '<span class="input-group-btn">';
      echo '<a class="btn btn-default listCmdInfo roundedRight"><i class="fas fa-list-alt"></i></a>';
      echo '</span>';
      echo '</div>';
      echo '</div>';
      echo '</div>';
    }
    ?>
  </fieldset>
</form>

<script>
$("#configureShareData").off('click','.listCmdInfo').on('click','.listCmdInfo', function () {
  var el = $(this).closest('.form-group').find('.configKey');
  jeedom.cmd.getSelectModal({cmd: {type: 'info'}}, function (result) {
    el.value(result.human);
  });
});
</script>
