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

$(".in_datepicker").datepicker();

$('.li_myhistorydata').off('click').on('click',function(){
  $('.li_myhistorydata').removeClass('active')
  $(this).addClass('active')
})

$('.li_sharehistorydata').off('click').on('click',function(){
  $('.li_sharehistorydata').removeClass('active')
  $(this).addClass('active')
})

$('#bt_validateCompareData').off('click').on('click',function(){
  var colors = Highcharts.getOptions().colors
  if (jeedom.history.chart['div_graphCompareHistory']) {
    while(jeedom.history.chart['div_graphCompareHistory'].chart.series.length > 0){
      jeedom.history.chart['div_graphCompareHistory'].chart.series[0].remove(true);
    }
    delete jeedom.history.chart['div_graphCompareHistory'];
  }
  jeedom.history.drawChart({
    cmd_id: $('.li_myhistorydata.active a').attr('data-calcul'),
    el: 'div_graphCompareHistory',
    dateRange : 'all',
    dateStart : $('#in_startDate').value(),
    dateEnd :  $('#in_endDate').value(),
    height : $('#div_graphCompareHistory').height(),
    option : {groupingType : 'high::day',graphType:'column'},
    success : function(){
      $.ajax({
        type: "POST",
        url: "plugins/dataservice/core/ajax/dataservice.ajax.php",
        data: {
          action: "getShareHistory",
          history: $('.li_sharehistorydata.active a').attr('data-history'),
          dateStart : $('#in_startDate').value(),
          dateEnd :  $('#in_endDate').value(),
          radius :  $('#sel_radius').value(),
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
          var series = {
            dataGrouping: 'high::day',
            type: 'column',
            id:'sahreData',
            cursor: 'pointer',
            name :$('.li_sharehistorydata.active a').text(),
            color: colors[1],
            data: data.result,
            tooltip: {
              valueDecimals: 2
            }
          };
          jeedom.history.chart['div_graphCompareHistory'].chart.addSeries(series);
        }
      });
    }
  });
});
