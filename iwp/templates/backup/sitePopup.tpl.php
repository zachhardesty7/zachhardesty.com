<?php
/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/
?>
<?php 
$sitesData = Reg::tplGet('sitesData'); 
$restrictions = array();
if(function_exists('multiUserAllowAccess')) {
  $restrictions = multiUserAllowAccess();
  $restrictions = $restrictions['restrict'];
}
?>
<div class="dialog_cont view_backup">
<div class="steps_container"> <div class="th rep_sprite"> <div class="title droid700">BACKUPS - <?php echo $sitesData[$d['siteID']]['name'] ?> </div>   <a class="cancel rep_sprite_backup">cancel</a> </div> <?php 

if(isset($d['scheduleBackups'])) { ?>
  <div class="th_sub rep_sprite"> <ul class="th_sub_nav" style="margin-left: 340px"> <li><a class="rep_sprite active" id="normalBackupSingle">Manual</a></li> <li><a class="rep_sprite" id="scheduledBackupSingle">Schedule</a></li> </ul> </div><?php 
} ?><div class="clear-both"></div><div class="clear-both"></div></div>

<div id="scheduledbackshow" style="display:none"> 
<?php
if (isset($d['scheduleBackups'])) {
  if(!empty($d['scheduleBackups']) && is_array($d['scheduleBackups'])){
    foreach($d['scheduleBackups'] as $backup){ ?>
          <div  class="item_ind float-left topBackup " >
          <div class="backup_name stats"><?php echo $backup['backupName']; ?></div>
          <div class="rep_sprite_backup stats files delConfHide"><?php if($backup['what'] == 'full'){ ?>Files + DB<?php } elseif($backup['what'] == 'db'){ ?>DB<?php } elseif($backup['what'] == 'files'){ ?>Files<?php }?></div>
          <div class="rep_sprite_backup stats size delConfHide"><?php if(!empty($backup['size'])) {echo $backup['size']; }?></div>
          <div class="rep_sprite_backup stats time delConfHide"><?php if(!empty($backup['time'])) {echo @date(Reg::get('dateFormatLong'), $backup['time']); }?></div>
          <div class="row_backup_action rep_sprite" style="float:right;"><a class="trash rep_sprite_backup removeBackup" sid="<?php echo $backup['siteID']; ?>"  referencekey="<?php echo $backup['referenceKey']; ?>"></a><div class="del_conf" style="display:none;"><div class="label">Sure?</div><div class="yes deleteBackup">Yes</div><div class="no deleteBackup">No</div></div></div>    
			<?php if(!empty($backup['downloadURL']) && (!is_array($backup['downloadURL']))){ ?> <div class="row_backup_action rep_sprite delConfHide" style="float:right;"><a class="download rep_sprite_backup" href="<?php echo $backup['downloadURL']; ?>"></a></div> <?php } ?>
			<?php if(!empty($backup['downloadURL']) && is_array($backup['downloadURL'])){ ?>
				<div class="row_backup_action rep_sprite delConfHide" style="float:right;"><a data-downloads='<?php echo json_encode($backup);?>' data-downcount='<?php echo count($backup['downloadURL'])?>' class="download multiple_downloads rep_sprite_backup"></a></div>
			<?php
			}
			?>
          <div class="row_action float-left delConfHide"><a class="restoreBackup needConfirm" sid="<?php echo $backup['siteID']; ?>" referencekey="<?php echo $backup['referenceKey']; ?>">Restore</a></div>
          </div><?php
    }
  }else {
    ?> <div class="empty_data_set"><div class="line2">Looks like there are <span class="droid700">no backups here</span>.Create a <a sid="<?php echo $d['siteID']; ?>" class="singleScheduleBackupNow">Backup Schedule</a>.</div></div><?php
  }
}
?>
</div>
<div id="normalbackshow"> 
<?php
if(!empty($d['siteBackups'])){
  foreach($d['siteBackups'] as $siteID => $siteTaskType){ 
  	TPL::captureClear('oldBackups');
  	foreach($siteTaskType as $key => $siteBackups){
  		if($key != 'backupNow'){
  			TPL::captureStart('oldBackups');
  			echo TPL::captureGet('oldBackups');
  		}
  		foreach($siteBackups as $siteBackup){ ?> 
        <div class="item_ind float-left topBackup ">
      	<div class="backup_name stats"><?php echo $siteBackup['backupName']; ?></div>
        <div class="rep_sprite_backup stats files delConfHide"><?php if($siteBackup['what'] == 'full'){ ?>Files + DB<?php } elseif($siteBackup['what'] == 'db'){ ?>DB<?php } elseif($siteBackup['what'] == 'files'){ ?>Files<?php }?></div>
        <div class="rep_sprite_backup stats size delConfHide"><?php echo $siteBackup['size']; ?></div>
        <div class="rep_sprite_backup stats time delConfHide"><?php echo @date(Reg::get('dateFormatLong'), $siteBackup['time']); ?></div>
        <?php if(empty($restrictions) || !in_array("restoreDeleteDownloadBackup", $restrictions)){ ?> 
        <div class="row_backup_action rep_sprite" style="float:right;"><a class="trash rep_sprite_backup removeBackup" sid="<?php echo $siteBackup['siteID']; ?>" taskName="<?php echo $siteBackup['data']['scheduleKey']; ?>" referencekey="<?php echo $siteBackup['referenceKey']; ?>"></a><div class="del_conf" style="display:none;"><div class="label">Sure?</div><div class="yes deleteBackup">Yes</div><div class="no deleteBackup">No</div></div></div>
        <?php if(!empty($siteBackup['downloadURL']) && (!is_array($siteBackup['downloadURL']))){ ?> <div class="row_backup_action rep_sprite delConfHide" style="float:right;"><a class="download rep_sprite_backup" href="<?php echo $siteBackup['downloadURL']; ?>"></a></div> <?php } ?>
                        <?php 
                        if(!empty($siteBackup['downloadURL']) && is_array($siteBackup['downloadURL']))
                        {?>
                          <div class="row_backup_action rep_sprite delConfHide" style="float:right;"><a data-downloads='<?php echo json_encode($siteBackup);?>' data-downcount='<?php echo count($siteBackup['downloadURL'])?>' class="download multiple_downloads rep_sprite_backup"></a></div>
                        <?php
                        }
                        ?>
        <div class="row_action float-left delConfHide"><a class="restoreBackup needConfirm" sid="<?php echo $siteBackup['siteID']; ?>" taskName="<?php echo $siteBackup['data']['scheduleKey']; ?>" referencekey="<?php echo $siteBackup['referenceKey']; ?>">Restore</a></div>
         <?php } ?>
        </div><?php 
      }	
  		if($key != 'backupNow'){
  			TPL::captureStop('oldBackups');
  		}
 	 }
	  if($oldBackupsHTML = trim(TPL::captureGet('oldBackups'))){
		  ?> <div style="margin-top: -1px;font-weight: 700;margin-left: -33px;padding: 40px 0px 16px 49px;">Other backups <span style="font-weight: 500">(Backups from deleted schedules & backups from reconnected sites are shown below) </span></div><?php
		  echo $oldBackupsHTML;
	  }
  }
} else { 
  if(empty($restrictions) || !in_array("createBackup", $restrictions)){ ?>
    <div class="empty_data_set"><div class="line2">Looks like there are <span class="droid700">no backups here</span>. Create a <a sid="<?php echo $d['siteID']; ?>" id="singleBackupNow">Backup Now</a>.</div></div><?php
  }else { 
    ?><div class="empty_data_set"><div class="line2">Looks like there are <span class="droid700">no backups here</span>.</div></div>
  	<?php 
  }
}
?>
</div>
<div class="clear-both"></div>
<div class="th rep_sprite" style="border-top:1px solid #c6c9ca; height: 28px;"><div class="btn_action float-right"></div></div>
</div> 