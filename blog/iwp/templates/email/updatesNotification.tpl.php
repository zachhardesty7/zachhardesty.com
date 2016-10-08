<?php

/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/

$showUpdate = $d['updatesNotificationMail']; 
$siteView = $d['sitesUpdates']['siteView'];
$updateNotificationDynamicContent = $d['updateNotificationDynamicContent'];
$siteViewCount = $d['sitesUpdates']['siteViewCount'];
//subject starts here
if(empty($siteView)){ ?>InfiniteWP | Everything is up to date.<?php } else { ?>InfiniteWP | New updates available.<?php }

//subject ends here
echo '+-+-+-+-+-+-+-+-+-+-+-+-+-mailTemplates+-+-+-+-+-+-+-+-+-+-+-';

//message starts here

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
<style>
a{
  color: #49a1de;
  text-decoration: none;
  cursor: pointer;
}

a:hover {
  text-decoration: underline;
}

</style>
</head>

<body bgcolor="#dce1e3" style="padding:0; margin:0; font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#555;">
<div style="background:#dce1e3;">
  <table bgcolor="#FFFFFF" width="600" border="0" cellspacing="0" cellpadding="0" style="border-collapse:collapse; margin:auto; border-bottom:2px solid #1d292c;">
    <tr style="width:600px; height:35px; background:#1d292c;">
      <td><div style="width:90px; height:13px; float:left; margin:10px; color:#fff; border:1px solid #1d292c; font-family:Arial, Helvetica, sans-serif; font-size:12px; font-weight:bold;">INFINITE<span style="color:#f2583e;">WP</span></div></td>
      <td><div style="font-family:Arial, Helvetica, sans-serif; font-size:11px; text-transform:uppercase; float:right; padding:10px; color:#fff; font-weight:bold; letter-spacing:0.5px;">MULTIPLE WORDPRESS MANAGEMENT</div></td>
    </tr>
    
    <tr>
      <td colspan="2"><table width="100%" border="0" cellpadding="0" cellspacing="0">
        <tr>
          <td width="11%" align="center" valign="top">
            <div style="width:49px; min-height:72px; margin-left:10px">
            <div style="color: #414a4e;font-size:28px;font-weight:bold;letter-spacing:-1px;text-align:center;padding-right:4px;padding-top:7px"><?php echo @date("j", time()); ?></div>
            <div style="color: #414a4e;font-size:14px;text-align:center;text-transform:uppercase"><?php echo @date("M", time()); ?></div></div></td>
          <td width="89%" style="padding-left:10px; padding-right:20px;">
          <div style="padding: 16px 0 16px;"><span style="color: #414A4E; font-size: 16px; font-weight: bold; letter-spacing: 2px; text-rendering: optimizelegibility; float:left; padding-top: 6px;">UPDATES NOTIFICATION</span> 
          <a href="<?php echo APP_URL; ?>" style="display: inline-block;   float: right;  border: 0;  background-color: #2988b4;  color: #fff;  font-weight: bold;  font-size: 11px; padding: 5px 11px 6px;  border-radius: 20px; cursor:pointer;">OPEN ADMIN PANEL</a> 
          <div style="clear:both;"></div> <?php if(!empty($d['sitesUpdates']['totalVulnsCount'])){ ?> <div  style="border-left: 2px solid #de4637; padding: 10px; background-color: #fee2e4;margin-top: 15px;"><img style="position: relative;width: 13px;padding: 0px; background-color:#fee2e4;margin-right: 6px;" src="https://s3.amazonaws.com/iwp_images/vulnsIcon.png">There <span style="color:#f44336"> <?php echo ($d['sitesUpdates']['totalVulnsCount'] == 1)? "is 1" :" are ".$d['sitesUpdates']['totalVulnsCount'];  ?> vulnerability updates</span>.</div> <?php } ?></div> 

          
  <?php 
 
 
 if(empty($siteView)){ ?>
      <table width="100%" border="0" cellspacing="50" style="border-top:1px solid #cdcdcd;">
      <tr>
      <td align="center">Everything is up to date.</td>
      </tr>
     </table>
<?php  }
else{
	
	$siteIDs = array_keys($siteView);
    $where = array(
              'query' =>  "siteID IN (:siteID)",
              'params' => array(
                   ':siteID'=>implode(",",$siteIDs)
            )
        );
  	$sitesName = DB::getFields("?:sites", "name, siteID", $where, "siteID");
	
    foreach($siteView as $siteID => $updateData){
		?> 
        
         <table width="100%" border="0" cellspacing="10" style="border-top:1px solid #cdcdcd;margin-bottom:10px;">
		<?php
?>
  <tr>
    <td colspan="2">
      <div style="padding-bottom: 10px;"><a href="<?php echo $sitesName[$siteID]; ?>" style="color:#465053;font-size:13px;font-weight:bold;text-decoration:none"><?php echo $sitesName[$siteID]; ?></a>

   <?php if(!empty($siteViewCount[$siteID]) && $siteViewCount[$siteID]['vulnsCount'] != 0){?>  | <span style="color:#f44336;font-weight:bold;"> <img style="position: relative;width: 13px;padding: 0px; margin-right: 3px;" src="https://s3.amazonaws.com/iwp_images/vulnsIcon.png">  <?php echo $siteViewCount[$siteID]['vulnsCount'];  ?> Vulnerability Updates</span> <?php } ?>

      </div></td>
    </tr>
    
    <?php if((!empty($showUpdate['coreUpdates']) || $showUpdate['WPVulnsUpdates']) && !empty($updateData['core'])){
		foreach($updateData['core'] as $type => $update){
		 ?>
   <tr>
    <td width="130" align="right" valign="top"><span style="background-color:#aeb4ba; color:#fff; padding:1px 5px 3px; font-size:11px; border-radius:2px; float: right;">wordpress</span></td>
    <td width="340"><span <?php if(!empty($update['vulnerability'])){ echo 'style="color:#f44336;"';} ?> ><?php echo 'v'. strip_tags($update['current_version'])?> </span> <?php echo ' to '.'<a href="'.WP_CHANGELOG_URL.'Version_'.strip_tags($update['current']).' "style="color: #49a1de;">v'. strip_tags($update['current']);?> <?php if(!empty($update['vulnerability'])){ ?> <img style="width: 13px;float: inherit;margin-left: 7px;" src="https://s3.amazonaws.com/iwp_images/vulnsIcon.png"> <?php  } ?></td>
  </tr>
  <?php } }
  ?>
  
   <?php if((!empty($showUpdate['pluginUpdates']) || $showUpdate['WPVulnsUpdates']) && !empty($updateData['plugins'])){ 
      $i = 0;
    foreach($updateData['plugins'] as $type => $update){ ?>

   <?php if($i == 0){ ?>
     <tr>
    <td width="130" align="right" valign="top" >
    <span style="background-color:#aeb4ba; color:#fff; padding:1px 5px 3px; font-size:11px; border-radius:2px;margin-top: 8px; float: right;">plugins</span></td>
    <td width="340" style="padding-top:10px;"><span <?php if(!empty($update['vulnerability'])){ echo 'style="color:#f44336;"';} ?> >  <?php echo strip_tags($update['name']) . ' - v'. strip_tags($update['old_version']).' to '?> </span> <?php echo '<a href="'.WP_PLUGIN_CHANGELOG_URL.''. strip_tags($update['slug']).'/changelog/" style="color: #49a1de;">v'. strip_tags($update['new_version']); ?> <?php if(!empty($update['vulnerability'])){ ?> <img style="width: 13px;float: inherit;margin-left: 7px;" src="https://s3.amazonaws.com/iwp_images/vulnsIcon.png"> <?php  } ?> </td>
  </tr>
 <?php $i = 1; } 
   else{ ?>
  <tr>
    <td align="right" valign="top">&nbsp;</td>
    <td><span <?php if(!empty($update['vulnerability'])){ echo 'style="color:#f44336;"';} ?> ><?php echo strip_tags($update['name']).' - v'. strip_tags($update['old_version']).' to '?> </span> <?php echo'<a href="'.WP_PLUGIN_CHANGELOG_URL.''. strip_tags($update['slug']).'/changelog/" style="color: #49a1de;">v'. strip_tags($update['new_version']); ?> <?php if(!empty($update['vulnerability'])){ ?> <img style="width: 13px;float: inherit;margin-left: 7px;" src="https://s3.amazonaws.com/iwp_images/vulnsIcon.png"> <?php  } ?></td>
  </tr><?php }
    } 
  } ?>
  
  
   <?php if((!empty($showUpdate['themeUpdates']) || $showUpdate['WPVulnsUpdates']) && !empty($updateData['themes'])){
   $i = 0;
    foreach($updateData['themes'] as $type => $update){ ?>
 
  <?php if($i == 0){ ?>
   <tr>
    <td width="130" align="right" valign="top">
      <span style="background-color:#aeb4ba; color:#fff; padding:1px 5px 3px; font-size:11px; border-radius:2px;margin-top: 8px; float: right;">themes</span></td>
    <td width="340" style="padding-top:10px;"><span <?php if(!empty($update['vulnerability'])){ echo 'style="color:#f44336;"';} ?> >  <?php echo strip_tags($update['name']).' - v'. strip_tags($update['old_version']).' to '.'v'. strip_tags($update['new_version']);  ?></span> <?php if(!empty($update['vulnerability'])){ ?> <img style="width: 13px;float: inherit;margin-left: 7px;" src="https://s3.amazonaws.com/iwp_images/vulnsIcon.png"> <?php  } ?></td>
  </tr>
  <?php $i = 1; } 
   else{ ?>
  <tr>  
    <td align="right" valign="top">&nbsp;</td>
    <td><span <?php if(!empty($update['vulnerability'])){ echo 'style="color:#f44336;"';} ?> ><?php echo strip_tags($update['name']).' - v'. strip_tags($update['old_version']).' to '.'v'. strip_tags($update['new_version']); ?></span> <?php if(!empty($update['vulnerability'])){ ?> <img style="width: 13px;float: inherit;margin-left: 7px;" src="https://s3.amazonaws.com/iwp_images/vulnsIcon.png"> <?php  } ?> </td>
  </tr>
  <?php }
     } 
	} 

   if(!empty($showUpdate['translationUpdates'])  && !empty($updateData['translations'])){
     ?>
   <tr>
    <td width="130" align="right" valign="top"><span style="background-color:#aeb4ba; color:#fff; padding:1px 5px 3px; font-size:11px; border-radius:2px; float: right;">translations</span></td>
    <td width="340">Translation updates are available.
  </td>
  </tr>
 <?php  }

	if(!empty($updateData['error'])){ ?>
	<tr>
    <td width="130" align="right" valign="top">
      <span style="background-color:#aeb4ba; color:#fff; padding:1px 5px 3px; font-size:11px; border-radius:2px; float: right;">wordpress</span></td>
    <td width="340" style="color:#a92a2a;" ><?php echo $updateData['error']; ?></td>
	</tr>
	<?php }?>
    </table>
 <?php }
 
} ?>         
       </td>
        </tr>
      </table></td>
    </tr>
    <tr>
      <td colspan="2">
      	<table border="0">
          <tr>
            <td><?php if(!empty($updateNotificationDynamicContent)){ echo $updateNotificationDynamicContent; }else{ ?>&nbsp;<?php } ?></td>
          </tr>
        </table>
	  </td>
    </tr>
  </table>  
</div>
</body>
</html>
