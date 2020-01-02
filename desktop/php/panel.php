<?php
if (!isConnect()) {
  throw new Exception('{{401 - Accès non autorisé}}');
}
$services = dataservice::devicesParameters();
$shareDataService = dataservice::getShareDataService();
$occupant = config::byKey('info::nbOccupant');
$date = array(
  'start' => date('Y-m-d', strtotime(config::byKey('history::defautShowPeriod') . ' ' . date('Y-m-d'))),
  'end' => date('Y-m-d'),
);
?>
<div class="row row-overflow">
  <?php
  if (init('report') != 1) {
    echo '<div class="col-lg-2 col-md-3 col-sm-4">';
  } else {
    echo '<div class="col-lg-2 col-md-3 col-sm-4" style="display:none;">';
  }
  ?>
  <div class="bs-sidebar">
    <legend>{{Je veux comparer ma}}</legend>
    <ul class="nav nav-list bs-sidenav">
      <?php
      foreach ($shareDataService as $key => $value) {
        if(!isset($value['history'])){
          continue;
        }
        $cmd = cmd::byId(str_replace('#','',config::byKey($value['key'],'dataservice')));
        if(!is_object($cmd)){
          continue;
        }
        if(in_array($cmd->getEqType(),array('dataservice','weather'))){
          continue;
        }
        $history_calcul = '#'.$cmd->getHumanName().'#';
        if(isset($value['unit']) && !in_array($cmd->getUnite(),$value['unit'])){
          $convert = false;
          if(isset($value['convert'])){
            foreach ($value['convert'] as $unit => $calcul) {
              if($unit == $cmd->getUnite()){
                $history_calcul = str_replace('#value#','#'.$cmd->getHumanName().'#',$calcul);
                $convert = true;
              }
            }
          }
          if(!$convert){
            continue;
          }
        }
        $history_calcul = '('.$history_calcul.')/'.$occupant;
        echo '<li class="cursor li_myhistorydata" ><a data-calcul="' . $history_calcul . '">' . $value['name'] . '</a></li>';
      }
      ?>
    </ul>
    <legend>{{Avec}}</legend>
    <ul class="nav nav-list bs-sidenav">
      <?php
      foreach ($shareDataService as $key => $value) {
        if(!isset($value['history'])){
          continue;
        }
        echo '<li class="cursor li_sharehistorydata" ><a data-history="' . $value['history'] . '">' . $value['name'] . '</a></li>';
      }
      ?>
    </ul>
    <legend>{{Dans un rayon de}}</legend>
    <select class="form-control" id="sel_radius">
      <?php
      foreach ($services['sharedata']['configuration']['radius']['options'] as $key => $name) {
        echo '<option value="'.$key.'">'.$name.'</option>';
      }
      ?>
    </select>
    <legend>{{Période}}</legend>
    <input id="in_startDate" class="form-control input-sm in_datepicker" style="width: 90px;display:inline-block;" value="<?php echo $date['start'] ?>"/> à
    <input id="in_endDate" class="form-control input-sm in_datepicker" style="width: 90px;display:inline-block;" value="<?php echo $date['end'] ?>"/>
    <br/><br/>
    <a class="btn btn-sm btn-success" id="bt_validateCompareData" style="width:100%"><i class="fas fa-check"></i> {{Valider}}</a>
  </div>
</div>
<?php
if (init('report') != 1) {
  echo '<div class="col-lg-10 col-md-9 col-sm-8" id="div_displayObject">';
} else {
  echo '<div class="col-lg-12 col-md-12 col-sm-12" id="div_displayObject">';
}
?>
<div id="div_graphCompareHistory" style="margin-top: 10px;height:calc(100% - 40px)"></div>
</div>
</div>


<?php include_file('desktop', 'panel', 'js', 'dataservice');?>
