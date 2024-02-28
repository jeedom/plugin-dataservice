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
    <div class="form-group gshmode jeedom">
      <label class="col-lg-3 control-label">{{Envoyer configuration au market}}</label>
      <div class="col-lg-2">
        <a class="btn btn-default" id="bt_sendConfigToMarket"><i class="fa fa-paper-plane" aria-hidden="true"></i> {{Envoyer}}</a>
      </div>
    </div>
   
  </fieldset>
</form>

<script>
$('#bt_sendConfigToMarket').on('click', function () {
  $.ajax({
    type: "POST",
    url: "plugins/dataservice/core/ajax/dataservice.ajax.php",
    data: {
      action: "sendConfig",
    },
    dataType: 'json',
    error: function (request, status, error) {
      handleAjaxError(request, status, error);
    },
    success: function (data) {
      if (data.state != 'ok') {
        $('#div_alert').showAlert({message: data.result, level: 'danger'});
        return;
      }
      $('#div_alert').showAlert({message: '{{Configuration envoyée avec succès}}', level: 'success'});
    }
  });
});
</script>
