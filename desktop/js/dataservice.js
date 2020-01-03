
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

$('.eqLogicAttr[data-l1key=configuration][data-l2key=service]').off('change').on('change',function(){
  $('.serviceConfig').hide();
  if($(this).value() != ''){
    $('.serviceConfig.'+$(this).value()).show();
  }
  $('.cmdAction[data-action=add]').hide();
  $('.eqLogicAttr[data-l2key=cron]').closest('.form-group').show();
  if(dataservice_services[$(this).value()]){
    if(dataservice_services[$(this).value()].canAddCmd){
      $('.cmdAction[data-action=add]').show();
    }
    if(dataservice_services[$(this).value()].noRefreshData){
      $('.eqLogicAttr[data-l2key=cron]').closest('.form-group').hide();
    }
  }
});

$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});

function addCmdToTable(_cmd) {
  var addSpecificCmd = 'dataservice_'+$('.eqLogicAttr[data-l1key=configuration][data-l2key=service]').value()+'_addCmdToTable';
  if(typeof window[addSpecificCmd] == 'function'){
    return window[addSpecificCmd](_cmd);
  }
  if (!isset(_cmd)) {
    var _cmd = {configuration: {}};
  }
  if (!isset(_cmd.configuration)) {
    _cmd.configuration = {};
  }
  var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
  tr += '<td>';
  tr += '<span class="cmdAttr" data-l1key="id" style="display:none;"></span>';
  tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" style="width : 140px;" placeholder="{{Nom}}">';
  tr += '</td>';
  tr += '<td>';
  tr += '<span class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>';
  tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>';
  tr += '</td>';
  tr += '<td>';
  tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isVisible" />{{Afficher}}</label>';
  tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isHistorized" />{{Historiser}}</label>';
  tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="display" data-l2key="invertBinary"/>{{Inverser}}</label></span> ';
  tr += '</td>';
  tr += '<td>';
  tr += '<input class="cmdAttr form-control input-sm" data-l1key="unite" style="width:30%;display:inline-block;" placeholder="{{Unité}}" title="{{Unité}}" >';
  tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Min}}" style="width:30%;display:inline-block;">';
  tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Max}}" style="width:30%;display:inline-block;">';
  tr += '</td>';
  tr += '<td>';
  if (is_numeric(_cmd.id)) {
    tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fa fa-cogs"></i></a> ';
    tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
  }
  tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i>';
  tr += '</td>';
  tr += '</tr>';
  $('#table_cmd tbody').append(tr);
  $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
  if (isset(_cmd.type)) {
    $('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
  }
  jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));
}

function dataservice_mail_addCmdToTable(_cmd){
  if (!isset(_cmd)) {
    var _cmd = {configuration: {}};
  }
  if (!isset(_cmd.configuration)) {
    _cmd.configuration = {};
  }
  var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
  tr += '<td>';
  tr += '<span class="cmdAttr" data-l1key="id" style="display:none;"></span>';
  tr += '<span class="cmdAttr" data-l1key="type" style="display:none;">action</span>';
  tr += '<span class="cmdAttr" data-l1key="subType" style="display:none;">message</span>';
  tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" style="width : 140px;" placeholder="{{Nom}}">';
  tr += '</td>';
  tr += '<td>';
  tr += '</td>';
  tr += '<td>';
  tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isVisible" />{{Afficher}}</label>';
  tr += '</td>';
  tr += '<td>';
  tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="to" placeholder="{{Enboyer à}}">';
  tr += '</td>';
  tr += '<td>';
  if (is_numeric(_cmd.id)) {
    tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fa fa-cogs"></i></a> ';
    tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
  }
  tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i>';
  tr += '</td>';
  tr += '</tr>';
  $('#table_cmd tbody').append(tr);
  $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
}
