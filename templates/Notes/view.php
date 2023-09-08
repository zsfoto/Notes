<?php // Baked at 2023.09.07. 07:15:40  ?>
<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Note $note
 */
	use Cake\Core\Configure;

	$session 			= $this->getRequest()->getSession();
	$prefix 			= strtolower( $this->request->getParam('prefix') );	
	$controller 		= $this->request->getParam('controller');	// for DB click on <tr>
	$action 			= $this->request->getParam('action');		// for DB click on <tr>
	//$passedArgs 		= $this->request->getParam('pass');			// for DB click on <tr>
	
	$config = Configure::read('Theme.' . $prefix);	
	//-------> More config from \config\jeffadmin.php <------
	//$config['index_show_id'] 			= true;
	//
	//$url = $this->Url->build(['prefix' => $prefix, 'controller' => $controller , 'action' => $config['index_db_click_action']]);

	if(!isset($layoutNotesLastId)){
		$layoutNotesLastId = 0;
	}
	
?>
		<div class="view col-sm-10 notes">
			<div class="card card-lightblue">
				<div class="card-header">
					<h3 class="card-title"><?= $title ?>: <?= h($note->name) ?></h3>
				</div><!-- /.card-header -->
				<div class="card-body">
				
					<div class="form-group row"><!-- 1. -->
						<label for="name" class="col-sm-2 col-form-label"><?= __('Category') ?>:</label>
						<div class="col-sm-9">
							<div class="view-field associated">
								<?= $note->has('category') ? $this->Html->link($note->category->name, ['controller' => 'Categories', 'action' => 'view', $note->category->id], ['escape' => false, 'class' => 'btn btn-sm btn-default']) : '&nbsp;' ?>
							</div>
						</div>
					</div>

					<div class="form-group row"><!-- 2. -->
						<label for="name" class="col-sm-2 col-form-label"><?= __('Name') ?>:</label>
						<div class="col-sm-9">
							<div class="view-field non-associated">
								<?php 
									if(!empty($note->name)){
										echo h($note->name);
									}else{
										echo "&nbsp;";
									}
								?>
							</div>
						</div>
					</div>


					<div class="form-group row"><!-- 7. -->
						<label for="body" class="col-sm-2 col-form-label"><?= __('Body') ?>:</label>
						<div class="col-sm-9">
							<div class="view-field text show-more">
								<?php // $this->Text->autoParagraph(h($note->body)); ?>
								<?php
									if(!empty($note->body)){
										//echo $this->Text->autoParagraph($note->body) . "<br>";
										echo $note->body . "<br>";
									}else{
										echo "&nbsp;";
									}
								?>
							</div>
						</div>
					</div>
	
				</div><!-- /.card-body -->
				
				<div class="card-footer">
					<?= $this->Html->link('<span class="btn-label"><i class="fa fa-arrow-left"></i></span>' . __('Back to list'), ['action' => 'index', '#' => $id], ['class'=>'offset-sm-2 btn btn-info', 'role'=>'button', 'escape'=>false,  'data-bs-tooltip'=>'tooltip', 'data-bs-placement'=>'top', 'title' => __('Back to list') ] ) ?>
				</div><!-- /.card-footer -->
				
			</div><!-- /. card -->
		</div><!-- /. col-sm-10 -->
		<!-- ################################################################################################################ -->


<!-- ######################################################################################################################## -->
<!-- ######################################################################################################################## -->
<!-- ######################################################################################################################## -->

<?php
	$this->Html->css(
		[
			'JeffAdmin./plugins/multiline-truncation-ellipsis-toggle/src/index',
			'JeffAdmin./plugins/Collapse-Long-Content-View-More-jQuery/showmore-default',
		],
		['block' => true]
	);

	$this->Html->script(
		[
			'JeffAdmin./plugins/multiline-truncation-ellipsis-toggle/src/jquery.multiTextToggleCollapse',
			'JeffAdmin./plugins/Collapse-Long-Content-View-More-jQuery/jquery.show-more',			
			'JeffAdmin./dist/js/sweetalert_delete',
		],
		['block' => 'scriptBottom']
	);
?>

<?php $this->Html->scriptStart(['block' => 'javaScriptBottom']); ?>

	$(document).ready( function(){
		//$(".view .text").multiTextToggleCollapse({
		//	line:4
		//});
		
		$('.show-more').showMore({
			minheight: 100,
			buttontxtmore: '<?= __('&darr;&nbsp;more content&nbsp;&darr;') ?>',
			buttontxtless: '<?= __('&uarr;&nbsp;less content&nbsp;&uarr;') ?>',
			//buttoncss: 'my-button-css',
			animationspeed: 250
		});
		
	});

<?php $this->Html->scriptEnd(); ?>

