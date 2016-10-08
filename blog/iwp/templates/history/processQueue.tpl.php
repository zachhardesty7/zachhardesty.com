<?php 
/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/
?>
<div class="site_bar_btn rep_sprite float-right" style="margin-right:10px;">
<div id="process_queue" class="historyToolbar"><div class="<?php if($d['showInProgress']){?> in_progress<?php } ?> historyToolbar processQueueMoveOut"><span class="processQueueMove"></span> </div>Process Queue</div>
  <div class="queue_cont" id="historyQueue">
<?php
$statusMessages = array(
"writingRequest" => "Adding to queue",
"pending" => "Pending",
"initiated" => "Initiated",
"running" => "Running",
"scheduled" => "Waiting in queue",
"processingResponse" => "Processing response",
"multiCallWaiting" => "Running",
"retry" => "waiting for retry"
  );
if(!empty($d['actionsHistoryData'])){
	$sitesData = Reg::tplGet('sitesData');
foreach($d['actionsHistoryData'] as $actionID => $actionHistory){
  $showByDetailedActionGroup = ( ($actionHistory['type'] == 'PTC')|| ( ($actionHistory['action'] == 'manage' || $actionHistory['action'] == 'install') && ($actionHistory['type'] == 'plugins' || $actionHistory['type'] == 'themes') ) );
  $showByDetailedActionGroup2 = true;

  if(empty($actionHistory)){
    continue;
  }
  
  $actionIDHTML = str_replace('.', '', $actionID);
  //stagingTweakInProcessQueue($actionHistory);
  if($actionHistory['status'] == 'pending' || $actionHistory['status'] == 'multiCallWaiting') $actionOverallStatus = '';  
  elseif($actionHistory['statusSummary']['total'] == $actionHistory['statusSummary']['success']) $actionOverallStatus = 'success';
  else $actionOverallStatus = 'failure';
  $percentageDone = (((int)$actionHistory['historyStatusSummary']['success']+(int)$actionHistory['historyStatusSummary']['error'])/(float)$actionHistory['historyStatusSummary']['total'])*100;

  $percentageDone = ($percentageDone<2)?2:$percentageDone;
  if($actionHistory['historyStatusSummary']['total'] == 1) $percentageDone = 100;//99.9;
  $inProgress=0;
  if(in_array($actionHistory['status'], array('pending', 'running', 'initiated', 'processingResponse','multiCallWaiting','retry'))){$inProgress=1;}

  $stoppingClass = '';
  $where = array(
              'query' =>  "actionID = ':actionID'",
              'params' => array(
                   ':actionID'=>$actionID
            )
        );
  $statusArrTemp = DB::getArray("?:history", "status", $where);
  $statusArr = array();
  foreach($statusArrTemp as $actualStatus){
    array_push($statusArr, $actualStatus['status']);
  }
  // $stopping = count(array_intersect($statusArr, array('pending','multiCallWaiting','scheduled') ) );
  $stopping = count(array_diff($statusArr, array('completed','error','netError') ) );
  if( $stopping ){
    $stoppingClass = 'stop_pending';
    if($actionHistory['status'] == 'multiCallWaiting'){
      $stoppingClass .= ' stop_multicall';
    }
  }

?>
<?php TPL::captureStart('processQueueRowSummary'); ?>
<?php echo TPL::captureGet('processQueueRowSummary');
$titleTweak = processQueueTweak($actionHistory, 'staging', 'title');
if($titleTweak){
  $TPLPrepareHistoryBriefTitle = $titleTweak;
} else {
  $TPLPrepareHistoryBriefTitle = TPLPrepareHistoryBriefTitle($actionHistory);
}
?>
<div class="queue_ind_item historyItem <?php if($stoppingClass != 'stop_pending'){ echo ' '.$actionOverallStatus; }?>" did="<?php echo $actionIDHTML; ?>"  actionID="<?php echo $actionID; ?>" onclick=""><?php if($inProgress){ ?><div class="in_progress" style="width: <?php echo $percentageDone; ?>%"></div> <?php } ?> <?php if($stoppingClass != ''){ ?>  <div class="rep_sprite btn_stop_rep_sprite" ><span class = "rep_sprite_backup btn_stop_progress <?php echo $stoppingClass; ?>"  mechanism = "pending" actionID = "<?php echo $actionID; ?>"></span> </div><?php } ?> <?php if($percentageDone<100){ ?><div style="position:relative;"><?php } ?><div class="queue_ind_item_title"><?php  echo $TPLPrepareHistoryBriefTitle; ?></div><div class="timestamp float-right"><?php echo @date(Reg::get('dateFormatYearLess'), $actionHistory['time']); ?></div><?php if($percentageDone<100){ ?></div><?php } ?>
<div class="clear-both"></div>
</div>
<?php TPL::captureStop('processQueueRowSummary'); ?>

    <div class="queue_detailed nano" id="<?php echo $actionIDHTML; ?>" style="display:none;"  actionID="<?php echo $actionID; ?>">
      <div class="content">
        <div class="item_title"><span class="droid700" style="padding: 12px; float: left; line-height: 20px; padding-bottom: 0;"><?php echo $TPLPrepareHistoryBriefTitle; ?></span>
          <div class="time_suc_fail"><span class="timestamp"><?php echo @date(Reg::get('dateFormatLong'), $actionHistory['time']); ?></span>
          <?php if($actionHistory['statusSummary']['success']) { ?><span class="success"><?php echo $actionHistory['statusSummary']['success']; ?></span><?php } ?>
          <?php if($errorCount = ($actionHistory['statusSummary']['error'] + $actionHistory['statusSummary']['netError'])) { ?><span class="failure"><?php echo $errorCount; ?></span><?php } ?>
          <a class="btn_send_report float-right droid400 sendReport" actionid="<?php echo $actionID; ?>">Report Issue</a>
          </div>
          <div class="clear-both"></div>
        </div>
<?php 
//Grouping by siteID, detailedAction, status
$fullGroupedActions = array();

$siteWithErrors = array();
foreach($actionHistory['detailedStatus'] as $singleAction){
	//to display plugin slug instead of plugin main file say hello-dolly/hello_dolly.php => hello-dolly
	if(($actionHistory['type'] == 'PTC' || $actionHistory['type'] == 'staging') && $singleAction['detailedAction'] == 'plugin'){
		$singleAction['uniqueName'] = reset(explode('/', $singleAction['uniqueName']));
		$singleAction['uniqueName'] = str_replace('.php', '', $singleAction['uniqueName']);
	}
	
	if(in_array($actionHistory['type'], array('themes', 'plugins')) && $actionHistory['action'] == 'install' && strpos($singleAction['uniqueName'], '%20') !== false){//this to replace %20 in the file name
		$singleAction['uniqueName'] = str_replace('%20', ' ', $singleAction['uniqueName']);
	}
	if($singleAction['status'] == 'success'){
		$fullGroupedActions[ $singleAction['siteID'] ][ $singleAction['detailedAction'] ][ 'success' ] [] = array('name' => $singleAction['uniqueName'],'detailedAction' => $singleAction['detailedAction'],'type' => $actionHistory['type'], 'action' => $actionHistory['action'] );
	} elseif($singleAction['status'] == 'error' || $singleAction['status'] == 'netError'){		
		//if($singleAction['error'] == 'main_plugin_connection_error'){ $singleAction['errorMsg'] = 'Plugin connection error.'; }
		$fullGroupedActions[ $singleAction['siteID'] ][ $singleAction['detailedAction'] ][ 'error' ] [] = array('name' => $singleAction['uniqueName'], 'errorMsg' => $singleAction['errorMsg'], 'error' => $singleAction['error'], 'type' => $actionHistory['type'], 'action' => $actionHistory['action'], 'detailedAction' => $singleAction['detailedAction'], 'microtimeInitiated' => $singleAction['microtimeInitiated'], 'status' => $singleAction['status']);
		$siteWithErrors[$singleAction['siteID']] = $singleAction['historyID'];
	}	else{
		$fullGroupedActions[ $singleAction['siteID'] ][ $singleAction['detailedAction'] ][ 'others' ] [] = array('name' => $singleAction['uniqueName'], 'detailedAction' => $singleAction['detailedAction'], 'errorMsg' => $singleAction['mainStatus'], 'microtimeInitiated' => $singleAction['microtimeInitiated'], 'status' => $singleAction['status'], 'historyID' => $singleAction['historyID'], 'type' => $actionHistory['type'], 'action' => $actionHistory['action']);
	}
	$sitesDataTemp[$singleAction['siteID']]['name'] = isset($sitesData[$singleAction['siteID']]['name']) ?  $sitesData[$singleAction['siteID']]['name'] : $singleAction['URL'];
}?>
<?php foreach($fullGroupedActions as $siteID => $siteGroupedActions){ ?>  
        <div class="queue_detailed_ind_site_cont">
          <div class="site_name droid700"><?php echo $sitesDataTemp[$siteID]['name']; ?><?php if(!empty($siteWithErrors[$siteID])){ ?><a style="float:right;" class="moreInfo" historyID="<?php echo $siteWithErrors[$siteID]; ?>">View site response</a><?php } ?></div>
     <?php foreach($siteGroupedActions as $detailedAction => $statusGroupedActions){ ?>
     	<?php
        if(($actionHistory['type'] == 'PTC' || $actionHistory['type'] == 'staging') && $detailedAction == 'plugin'){
				
			}
		?>
     
          <div class="item_cont">
            <?php if($showByDetailedActionGroup){ ?><div class="item_label float-left"><span><?php echo ucfirst($detailedAction); ?></span></div><?php } ?>
              
            <div class="item_details float-left">

              <?php if(!empty($statusGroupedActions['success'])){ ?>
                <div class="item_details_success"> 
                  <?php foreach($statusGroupedActions['success'] as $oneAction){
                    if ($oneAction['type'] == 'staging') {
                        processQueueTweak($oneAction, 'staging', 'content');
                    }
                    if($showByDetailedActionGroup){
                      echo '<span>'.ucfirst($oneAction['name']).'</span>'; 
                    }else{ 
                      if ($oneAction['isStage']  == 'staging') {
                        echo "<span>".TPLActionTitle($oneAction)." (Staging site will not be displayed in the site list)</span>"; 

                    }else{ 

                        echo "<span>".TPLActionTitle($oneAction)."</span>"; 

                      }
                    }
                  }
                  ?>
                  <div class="clear-both"></div>
                </div>
              <?php }    ?>

              <?php if(!empty($statusGroupedActions['others'])){ ?>
              <div class="<?php if($inProgress == 1) {?>running_task<?php } else { ?> item_details_fail <?php } ?> ">
              <?php foreach($statusGroupedActions['others'] as $oneAction){
                     if ($oneAction['type'] == 'staging'){
                          processQueueTweak($oneAction, 'staging', 'content');
                    } ?>
                <?php if($showByDetailedActionGroup2){ ?><div class="name"><?php echo TPLActionTitle($oneAction); ?>
                <?php $singleTaskStoppingClass = 'single stop_pending'; if($oneAction['errorMsg'] == 'multiCallWaiting'){$singleTaskStoppingClass .= ' stop_multicall'; } ?>
<div class="rep_sprite btn_stop_rep_sprite" ><span class = "rep_sprite_backup btn_stop_progress <?php echo $singleTaskStoppingClass; ?>"  mechanism = "pending" historyID = "<?php echo $oneAction['historyID']; ?>"></span> </div>

              </div><?php } ?>
                <div class="reason<?php if(!$showByDetailedActionGroup2){ ?> only<?php } ?>"><?php echo $statusMessages[$oneAction['errorMsg']];  ?></div>
                <div class="clear-both"></div><?php } ?>
              </div>
              <?php } ?>             
              <?php if(!empty($statusGroupedActions['error'])){ ?> 
              <div class="item_details_fail">
              <?php foreach($statusGroupedActions['error'] as $oneAction){
                  if ($oneAction['type'] == 'staging'){
                    processQueueTweak($oneAction, 'staging', 'content');
                  }  
                  ?>
                <?php if($showByDetailedActionGroup2){ ?><div class="name"><?php echo TPLActionTitle($oneAction); ?></div><?php } ?>
                <div class="reason<?php if(!$showByDetailedActionGroup2){ ?> only<?php } ?>"><?php echo TPLAddErrorHelp($oneAction); ?></div>
                <div class="clear-both"></div>
                <?php } ?>
              </div>
              <?php } ?>
            </div>
            <div class="clear-both"></div>
          </div>
      <?php } //END foreach($siteGroupedActions as $detailedAction => $statusGroupedActions) ?> 
        </div>
<?php } //END foreach($fullGroupedActions as $siteID => $siteGroupedActions) ?>
        
      </div>
    </div>
<?php } //END foreach($d['actionsHistoryData'] as $actionHistory) ?>
<?php } //if(!empty($d['actionsHistoryData']))
else{ ?>

<?php TPL::captureStart('processQueueRowSummary'); ?>
	<div class="empty_data_set websites"><div class="line1">Operations that you initiate will be queued and processed here.</div></div>
<?php TPL::captureStop('processQueueRowSummary'); ?>
<?php	
}
 ?>
    <div class="queue_list">
      <div class="th rep_sprite">
        <div class="title droid700">PROCESS QUEUE</div><div class="float-left" id="historyQueueUpdateLoading"></div>
        <div class="history"><a class="navLinks" page="history">View Activity Log</a></div>
      </div>
      <div class="queue_ind_item_cont nano">
        <div class="content">
          <?php echo TPL::captureGet('processQueueRowSummary'); ?>
        </div>
      </div>
    </div>
  </div>
</div>