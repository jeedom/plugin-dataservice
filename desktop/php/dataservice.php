<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('dataservice');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
$services = dataservice::devicesParameters();
uasort($services, function ($a, $b) {
	return @$a['name'] - $b['name'];
});
sendVarToJS('dataservice_services',$services);
?>

<div class="row row-overflow">
	<div class="col-xs-12 eqLogicThumbnailDisplay">
		<legend><i class="fas fa-cog"></i>  {{Gestion}}</legend>
		<div class="eqLogicThumbnailContainer">
			<div class="cursor eqLogicAction logoPrimary" data-action="add">
				<i class="fas fa-plus-circle"></i>
				<br>
				<span>{{Ajouter}}</span>
			</div>
			<div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
				<i class="fas fa-wrench"></i>
				<br>
				<span>{{Configuration}}</span>
			</div>
		</div>
		<legend><i class="fas fa-table"></i> {{Mes datas}}</legend>
		<input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
		<div class="eqLogicThumbnailContainer">
			<?php
			foreach ($eqLogics as $eqLogic) {
				$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
				echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
				echo '<img src="' . $eqLogic->getImage() . '"/>';
				echo '<br>';
				echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
				echo '</div>';
			}
			?>
		</div>
	</div>
	
	<div class="col-xs-12 eqLogic" style="display: none;">
		<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
				<a class="btn btn-default btn-sm eqLogicAction roundedLeft" data-action="configure"><i class="fa fa-cogs"></i> {{Configuration avancée}}</a><a class="btn btn-default btn-sm eqLogicAction" data-action="copy"><i class="fas fa-copy"></i> {{Dupliquer}}</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a><a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
			</span>
		</div>
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
			<li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
			<li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Commandes}}</a></li>
			<li role="presentation"><a href="#advancetab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-vial"></i> {{Avancée}}</a></li>
		</ul>
		<div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<br/>
				<form class="form-horizontal">
					<fieldset>
						<div class="form-group">
							<label class="col-sm-3 control-label">{{Nom de l'équipement}}</label>
							<div class="col-sm-3">
								<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
								<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}"/>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label" >{{Objet parent}}</label>
							<div class="col-sm-3">
								<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
									<option value="">{{Aucun}}</option>
									<?php
									$options = '';
									foreach ((jeeObject::buildTree(null, false)) as $object) {
										$options .= '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $object->getConfiguration('parentNumber')) . $object->getName() . '</option>';
									}
									echo $options;
									?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">{{Catégorie}}</label>
							<div class="col-sm-9">
								<?php
								foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
									echo '<label class="checkbox-inline">';
									echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
									echo '</label>';
								}
								?>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label"></label>
							<div class="col-sm-9">
								<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
								<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">{{Service}}</label>
							<div class="col-sm-3">
								<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="service">
									<option value="">{{Aucun}}</option>
									<?php
									foreach ($services as $key => $service) {
										echo '<option value="'.$key.'">'.$service['name'].'</option>';
									}
									?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">{{Description}}</label>
							<?php
							foreach ($services as $key => $service) {
								echo '<div class="col-sm-3 serviceConfig '.$key.'" style="display:none;">';
								if(isset($service['description'])){
									echo $service['description'];
								}
								echo '</div>';
							}
							?>
						</div>
						<?php
						foreach ($services as $key => $service) {
							echo '<div class="serviceConfig '.$key.'" style="display:none;">';
							if(isset($service['oauth']) && $service['oauth']){
								echo '<div class="form-group">';
								echo '<label class="col-sm-3 control-label">{{Lier}} '.$service['name'].' {{avec Jeedom}}</label>';
								echo '<div class="col-sm-3">';
								$oauth_multi_user = (isset($service['oauth_multi_user']) && $service['oauth_multi_user']) ? 1 : 0;
								if(file_exists(__DIR__.'/../../core/img/link_'.$key.'.png')){
									echo '<a class="bt_oauth" data-service="'.$key.'" data-multiuser="'.$oauth_multi_user.'" data-href="'.config::byKey('service::cloud::url').'/frontend/login.html"><img src="plugins/dataservice/core/img/link_'.$key.'.png" /></a>';
								}else{
									echo '<a class="btn btn-default bt_oauth" data-service="'.$key.'" data-multiuser="'.$oauth_multi_user.'" data-href="'.config::byKey('service::cloud::url').'/frontend/login.html"><i class="fas fa-link"></i> {{Lier}}</a>';
								}
								echo '</div>';
								echo '</div>';
							}
							if(isset($service['configuration']) && is_array($service['configuration'])){
								foreach ($service['configuration'] as $key2 => $value) {
									if($value['type'] == 'input'){
										echo '<div class="form-group">';
										echo '<label class="col-sm-3 control-label">'.$value['name'].'</label>';
										echo '<div class="col-sm-3">';
										echo '<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="'.$key.'::'.$key2.'"/>';
										echo '</div>';
										echo '</div>';
									}elseif($value['type'] == 'number'){
										$option = '';
										if(isset($value['max'])){
											$option .= ' max="'.$value['max'].'"';
										}
										if(isset($value['min'])){
											$option .= ' min="'.$value['min'].'"';
										}
										if(isset($value['step'])){
											$option .= ' step="'.$value['step'].'"';
										}
										echo '<div class="form-group">';
										echo '<label class="col-sm-3 control-label">'.$value['name'].'</label>';
										echo '<div class="col-sm-3">';
										echo '<input type="number" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="'.$key.'::'.$key2.'" '.$option.' />';
										echo '</div>';
										echo '</div>';
									}elseif($value['type'] == 'select'){
										echo '<div class="form-group">';
										echo '<label class="col-sm-3 control-label">'.$value['name'].'</label>';
										echo '<div class="col-sm-3">';
										echo '<select type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="'.$key.'::'.$key2.'">';
										foreach ($value['options'] as $key3 => $name) {
											echo '<option value="'.$key3.'">'.$name.'</option>';
										}
										echo '</select>';
										echo '</div>';
										echo '</div>';
									}elseif($value['type'] == 'checkbox'){
										echo '<div class="form-group">';
										echo '<label class="col-sm-3 control-label">'.$value['name'].'</label>';
										echo '<div class="col-sm-3">';
										echo '<input type="checkbox" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="'.$key.'::'.$key2.'"/>';
										echo '</div>';
										echo '</div>';
									}
								}
							}
							echo '</div>';
						}
						?>
					</fieldset>
				</form>
			</div>
			<div role="tabpanel" class="tab-pane" id="commandtab">
				<a class="btn btn-success btn-sm cmdAction pull-right" data-action="add" style="margin-top:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter une commande}}</a><br/><br/>
				<table id="table_cmd" class="table table-bordered table-condensed">
					<thead>
						<tr>
							<th>{{Nom}}</th>
							<th>{{Type}}</th>
							<th>{{Options}}</th>
							<th style="width: 300px;">{{Paramètres}}</th>
							<th>{{Action}}</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
			<div role="tabpanel" class="tab-pane" id="advancetab">
				<br/>
				<form class="form-horizontal">
					<fieldset>
						<div class="form-group">
							<label class="col-sm-3 control-label">{{Cron de mise à jour}}</label>
							<div class="col-sm-3">
								<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cron"/>
							</div>
						</div>
					</fieldset>
				</form>
			</div>
		</div>
		
	</div>
</div>

<?php include_file('desktop', 'dataservice', 'js', 'dataservice');?>
<?php include_file('core', 'plugin.template', 'js');?>
