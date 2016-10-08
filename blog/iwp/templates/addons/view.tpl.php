<?php 
/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/

?>
<?php TPL::captureStart('newAddons'); ?>

<?php
$appRegisteredUser = getOption("appRegisteredUser");
if(!empty($appRegisteredUser)){ ?>
<table cellpadding="0" cellspacing="0" border="0" class="addon-bundle-wrapper">
	<tr class="addon-bundle-row">
		<td>Your infinitewp.com account:</td>
		<td>&nbsp;<span class="bundle-name"><?php echo $appRegisteredUser; ?></span></td>		
	</tr>
<?php 
if(!empty($d['addonSuiteOrMiniPurchased'])) {
	switch($d['addonSuiteOrMiniPurchased']) {
		case 'addonSuite':
			$bundleName='Addon Suite';
		break;
		case 'addonSuiteMini':
			$bundleName='Addon Suite Mini';
		break;
	}
?>
	<tr class="addon-bundle-row">
		<td>Your addon bundle:</td>
		<td>&nbsp;<span class="bundle-name"><?php echo($bundleName); ?></span></td>		
	</tr>
<?php
		if($d['addonSuiteOrMiniPurchased']=='addonSuiteMini' && $d['addonSuiteMiniActivity']=='installed') {
			if($d['isMiniExpired']) {
				$upgrade_url = IWP_SITE_URL.'?add-to-cart='.$d['IDForAddonSuite'].'&utm_source=application&utm_medium=userapp&utm_campaign=addonsuite';
				$upgrade_label = 'Upgrade to Addon Suite for $'.$d['priceForAddonSuite'];
			} else {
				$upgrade_url = IWP_SITE_URL.'?add-to-cart='.$d['IDToBeUpgradedFromMini'].'&utm_source=application&utm_medium=userapp&utm_campaign=addonsuite';
				$upgrade_label = 'Upgrade to Addon Suite for $'.$d['priceForSuiteUpgradedFromMini'];				
			}
?>
	<tr class="addon-bundle-row">
		<td></td>
		<td>
			<div class="addon-suite-mini-instructions-wrapper">
				&nbsp;Want to manage unlimited sites?
			</div>
			<div class="addon-suite-mini-instructions-wrapper">
				&nbsp;<a href="<?php echo($upgrade_url); ?>" target="_blank"><?php echo($upgrade_label); ?></a>
			</div>			
		</td>		
	</tr>	
<?php
		}
	}
?>
</table>
<?php }
else{ ?>
	<div style="padding: 10px;">Your infinitewp.com account: <span style="font-weight: 700;">You have not connected your account. </span> <a <?php if(!$d['isAppRegistered']){ ?>register="no" actionvar="register"<?php } ?> onclick="$('#checkNowAddons').click();">Connect Now</a></div>
<?php 
}
$enterpriseAddons = array();
?>
<div class="result_block shadow_stroke_box purchased_addons">
  <div class="th rep_sprite">
    <div class="title"><span class="droid700">YOUR PURCHASED ADDONS</span></div>
    <div class="btn_reload rep_sprite" style=" width: 103px;float: left;margin: 7px;height: 23px;border-radius: 20px;border-right-width: 1px;"><a class="rep_sprite_backup" <?php if(!$d['isAppRegistered']){ ?>register="no" actionvar="register"<?php } ?>  id="checkNowAddons"  style="width:63px;background-position: -5px -685px;padding: 5px 12px 6px 28px;height: 12px;box-shadow: 0 2px 1px rgba(0, 0, 0, 0.1), 1px 1px 1px rgba(255, 255, 255, 0.5) inset;border-radius: 20px;"><i class="fa fa-repeat" style="position: absolute;left: 9px;font-size: 15px;top: 3px;"></i>Check Now</a></div>
    <div class="btn_action float-right <?php if(empty($d['newAddons'])){ ?> disabled<?php } ?>"><a class="rep_sprite" id="installIWPAddons"  actionvar="installAddons">Install Addons</a></div>
    
  </div>
  <div class="rows_cont" style="margin-bottom:-1px;">
  <?php if(!empty($d['newAddons'])){
	  foreach($d['newAddons'] as  $addon){ ?>
	  	<div class="addons_cont"><?php echo $addon['addon']; ?></div>
<?php  }
	 } else{ ?>
	  <div class="addons_empty_cont">You have installed all purchased addons / You have not purchased any new addons. You can purchase addons from the <a href="<?php echo(IWP_SITE_URL); ?>addons/?utm_source=application&utm_medium=userapp&utm_campaign=purchaseAddon" target="_blank">InfiniteWP addon store</a>.</div>
  <?php } ?>
    <div class="clear-both"></div>
  </div>
</div>
<?php TPL::captureStop('newAddons');

 TPL::captureStart('installedAddons'); ?>
<div class="result_block shadow_stroke_box addons">
  <div class="th rep_sprite">
    <div class="title" style="margin-left: 82px;"><span class="droid700">INSTALLED ADDONS</span></div>
     <div class="title" style="margin-left: 410px;"><span class="droid700">VALID TILL</span></div>
    <?php
	if(!empty($d['installedAddons'])){
		$updateBulkAddons = array();
		foreach($d['installedAddons'] as  $addon){
			if(!empty($addon['updateAvailable']) && !$addon['isValidityExpired']){
				$updateBulkAddons[] = $addon['slug'].'__AD__'.$addon['updateAvailable']['version'];
			}
		}
	}
	if(!empty($updateBulkAddons)){	
	$updateBulkAddonsString = implode('__IWP__', $updateBulkAddons);
	?>
    <div class="btn_action float-right"><a class="rep_sprite updateIWPAddons needConfirm" authlink="updateAddons&addons=<?php echo $updateBulkAddonsString; ?>">Update All Addons</a></div>
    <?php } ?>
  </div>
  <div class="rows_cont">
  <?php
   reset($d['installedAddons']);
   $isBundleClassName = '';
   if(!empty($d['installedAddons'])){
	   if(in_array($d['addonSuiteOrMiniPurchased'],array('addonSuite','addonSuiteMini'))) {
			$isBundleClassName = 'bundle-rows';
			
			$daysRemaining = ''; $today='';
			
			list($key, $value) = each($d['installedAddons']);
			
			if($key=='multiUser') {
				list($key, $value) = each($d['installedAddons']);
			}
			
			$validTill = getValidTill($addon);
			reset($d['installedAddons']);			
?>
		<div class="ind_row_cont <?php echo $validTill['class']; ?>">
			<div class="row_no_summary">
				<div class="view_list addon_view_list on_off">
				</div>
				<div class="row_name bundle-name"><?php echo($bundleName); ?></div>
				<span class="row_valid_till additional-padding-left">
				<?php 
					if($d['installedAddons'][$key]['isLifetime'] == true){
						echo "Lifetime"; 
					} else if($validTill['class']!='gp_over') { 
						echo date("M d, Y",$addon['validityExpires']).'<br />'.$validTill['extra_info']; 
					} else { 
						echo 'Expired';
					}
				?>				
				</span>
			<?php
				if(strtotime("+30 day", time()) >= $d['installedAddons'][$key]['validityExpires']){ 
			?>            
				<div class="row_action float-right">
					<a href="<?php echo(IWP_SITE_URL); ?>my-account/?utm_source=application&utm_medium=userapp&utm_campaign=renewAddon" target="_blank">
						<?php echo $d['installedAddons'][$key]['isValidityExpired'] ? "Renew" : ""; ?>
					</a>
				</div>
            <?php 
				}
			?>				
				<div class="clear-both"></div>
			</div>
		</div>
<?php
	   }
	  foreach($d['installedAddons'] as  $addon){ 
		$daysRemaining = ''; $today='';
            if($addon['slug']=='multiUser') {
                array_push($enterpriseAddons,$addon);
                continue;
            }
			$validTill = getValidTill($addon);
		?>
    		<div class="ind_row_cont <?php echo $validTill['class']; ?>">
    	
          <div class="row_no_summary">
            <div class="view_list addon_view_list on_off">
              <div class="cc_mask cc_addon_mask" addonSlug="<?php echo $addon['slug']; ?>"><div class="cc_img cc_addon_img <?php echo $addon['status'] == 'active' ? 'on' : 'off'; ?>"></div></div>
            </div>
            <div class="row_name addon_list <?php echo($isBundleClassName); ?>"><?php echo $addon['addon']; ?> <?php echo 'v'.$addon['version']; ?></div>
            <span class="row_valid_till">
			<?php  
				if($addon['isLifetime'] == true){
					echo "Lifetime"; 
				} else if($validTill['class']!='gp_over') { 
					echo date("M d, Y",$addon['validityExpires']).'<br />'.$validTill['extra_info']; 
				} else { 
					echo 'Expired';
				} 
			?>
			</span>
            
            <?php if(!empty($addon['updateAvailable']) && !$addon['isValidityExpired']){ ?>			
			
             <div class="row_action float-right">
        <a href="<?php echo $addon['updateAvailable']['changeLogLink']; ?>" target="_blank" style="padding-left: 5px;"><?php echo $addon['updateAvailable']['version']; ?></a>
        </div>
        <span style="float: right; padding-top: 10px;"> - </span>
        <div class="row_action float-right">
        	<a authlink="updateAddons&addon=<?php echo $addon['slug'].'__AD__'.$addon['updateAvailable']['version']; ?>" addonslug="<?php echo $addon['slug'].'__AD__'.$addon['updateAvailable']['version']; ?>" class="updateIWPAddons needConfirm<?php if($addon['isValidityExpired']){ ?> disabled<?php }?>" style="padding-right: 5px;">Update</a></div>
                            
            <?php } 
			
			if((strtotime("+30 day", time()) >= $addon['validityExpires']) && !in_array($d['addonSuiteOrMiniPurchased'],array('addonSuite','addonSuiteMini'))){ 
			?>            
            <div class="row_action float-right"><a href="<?php echo(IWP_SITE_URL); ?>my-account/?utm_source=application&utm_medium=userapp&utm_campaign=renewAddon" target="_blank"><?php echo (!empty($addon['updateAvailable']) && $addon['isValidityExpired']) ? "Renew to update" : "Renew"; ?></a></div>
            <?php }
			
			?>
            
            <div class="clear-both"></div>
          </div>
        </div>
 <?php }
 } else{ ?>
	   <div class="addons_empty_cont">You have not installed any addons yet.</div>
  <?php } ?>
  </div>
</div>
<?php if(count($enterpriseAddons)!=0) { ?>        
<div class="result_block shadow_stroke_box addons">
  <div class="th rep_sprite">
    <div class="title" style="margin-left: 82px;"><span class="droid700">ENTERPRISE</span></div>
     <div class="title" style="margin-left: 410px;"><span class="droid700">VALID TILL</span></div>
  </div>
<div class="rows_cont">
  <?php
   reset($enterpriseAddons);
   if(!empty($enterpriseAddons)){

	  foreach($enterpriseAddons as  $addon){ $daysRemaining = ''; $today='';
	   $validTill = getValidTill($addon);
	?>
    		<div class="ind_row_cont <?php echo $validTill['class']; ?>">
    	
          <div class="row_no_summary">
            <div class="view_list addon_view_list on_off">
              <div class="cc_mask cc_addon_mask" addonSlug="<?php echo $addon['slug']; ?>"><div class="cc_img cc_addon_img <?php echo $addon['status'] == 'active' ? 'on' : 'off'; ?>"></div></div>
            </div>
            <div class="row_name addon_list"><?php echo $addon['addon']; ?> <?php echo 'v'.$addon['version']; ?></div>
            <span class="row_valid_till">
			<?php 
				if($addon['isLifetime'] == true){
					echo "Lifetime"; 
				} else if($validTill['class']!='gp_over') { 
					echo date("M d, Y",$addon['validityExpires']).'<br />'.$validTill['extra_info']; 
				} else { 
					echo 'Expired';
				} 
			?>
			</span>
            
            <?php if(!empty($addon['updateAvailable']) && !$addon['isValidityExpired']){ ?>			
			
             <div class="row_action float-right">
        <a href="<?php echo $addon['updateAvailable']['changeLogLink']; ?>" target="_blank" style="padding-left: 5px;"><?php echo $addon['updateAvailable']['version']; ?></a>
        </div>
        <span style="float: right; padding-top: 10px;"> - </span>
        <div class="row_action float-right">
        	<a authlink="updateAddons&addon=<?php echo $addon['slug'].'__AD__'.$addon['updateAvailable']['version']; ?>" addonslug="<?php echo $addon['slug'].'__AD__'.$addon['updateAvailable']['version']; ?>" class="updateIWPAddons needConfirm<?php if($addon['isValidityExpired']){ ?> disabled<?php }?>" style="padding-right: 5px;">Update</a></div>
                            
            <?php } 
			
			if((strtotime("+30 day", time()) >= $addon['validityExpires'])){ 
			?>            
            <div class="row_action float-right"><a href="<?php echo(IWP_SITE_URL); ?>my-account/?utm_source=application&utm_medium=userapp&utm_campaign=renewAddon" target="_blank"><?php echo (!empty($addon['updateAvailable']) && $addon['isValidityExpired']) ? "Renew to update" : "Renew"; ?></a></div>
            <?php }
			
			?>
            
            <div class="clear-both"></div>
          </div>
        </div>
 <?php }
 } else{ ?>
	   <div class="addons_empty_cont">You have not installed any addons yet.</div>
  <?php } ?>
  </div>
</div>
<?php } ?>
<?php TPL::captureStop('installedAddons'); 

if(!empty($d['promoAddons'])){
 TPL::captureStart('promoAddons');?>
 
<div class="result_block shadow_stroke_box addons">
  <div class="th rep_sprite">
    <div class="title"><span class="droid700">OTHER USEFUL ADDONS</span></div>
  </div>
  <div class="rows_cont" style="margin-bottom:-1px">
  <?php
    foreach($d['promoAddons'] as  $addon){ ?>
    <div class="buy_addons_cont">
      <div class="addon_name"><?php echo $addon['addon']; ?></div>
      <div class="addon_descr"><?php echo $addon['descr']; ?></div>
      <div class="th_sub rep_sprite">
        <div class="price_strike"><?php $addon['listPrice'] = (float)$addon['listPrice']; echo (!empty($addon['listPrice'])) ? '$ '.$addon['listPrice'] : ''; ?></div>
        <div class="price">$<?php echo $addon['price']; ?></div>
        <div class="full_details"><a href="<?php echo $addon['URL']."?utm_source=application&utm_medium=userapp&utm_campaign=purchaseAddon"; ?>" target="_blank">Full Details</a></div>
      </div>
    </div>    
	<?php } ?>
   <div class="clear-both"></div>
  </div>
</div>
<?php TPL::captureStop('promoAddons'); } 

//===================================================================================================================> 

if(!empty($d['promos']['addon_page_top'])){ echo '<div id="addon_page_top">'.$d['promos']['addon_page_top'].'</div>'; }
echo TPL::captureGet('newAddons');
echo TPL::captureGet('installedAddons');
echo TPL::captureGet('promoAddons');
if(!empty($d['promos']['addon_page_bottom'])){ echo '<div id="addon_page_top">'.$d['promos']['addon_page_bottom'].'</div>'; }

?>