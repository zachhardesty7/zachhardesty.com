<?php

/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/


if ($d['logDetails']) {
  foreach ($d['logDetails'] as $details) {
?><div class="ind_row_cont " loginID="<?php echo $details['ID']; ?>">
  <div class="row_summary" style="display: block;">
   <div class="row_arrow"></div> 
   <div class="timestamp"><?php echo @date('M d, Y @ h:ia', $details['time']); ?></div> 
   <div class="row_name"><?php echo $details['email'] ?></div>
   <?php if ($details['loginAttemptStatus'] == 'success') { ?>
    <div class="success_bu rep_sprite_backup"><?php echo 'Success' ?></div>
   <?php } else { ?>  
    <div class=" rep_sprite_backup" style="padding: 12px 11px 0 34px; border-left: 0 !important; background-position: 0 -238px; color: #c41111;height: 22px;"><?php echo 'Error' ?></div>
   <?php } ?>
   <div class="clear-both"></div>
   </div>
   <div class="row_detailed" style="display: none;">
    <div class="rh">
      <div class="row_arrow"></div>
     <div class="timestamp"><?php echo @date('M d, Y @ h:ia', $details['time']); ?></div>
      <div class="row_name"><?php echo $details['email'] ?></div>
      <?php if ($details['loginAttemptStatus'] == 'success') { ?>
    <div class="success_bu rep_sprite_backup"><?php echo 'Success' ?></div>
   <?php } else { ?>  
    <div class=" rep_sprite_backup" style="padding: 12px 11px 0 34px; border-left: 0 !important; background-position: 0 -238px; color: #c41111;height: 22px;"><?php echo 'Error' ?></div>
   <?php } ?>  
      <div class="clear-both"></div>
    </div>
           <div class="rd">
   <?php 
       foreach ($details as $value) { 
        if(!empty($value) && $details['time'] != $value && $details['ID'] != $value){?>
      <div class="row_updatee">
                <div class="row_updatee_ind">
        <div class="label_updatee" >
        <div class="label float-left"><?php  echo loginLogHistoryViewBeautifier(array_search ($value, $details));  ?></div>

          <div class="clear-both"></div>
        </div>
        <div class="item_cont_right_cont">
          <div class="clear-both">
        </div>
        <div class="item_ind " >
        <div class="float-left"><div class="droid700  float-left" style="padding-top: 10px;"><span><?php if($value == 'authNone'){ echo 'Default Login';}elseif($value == 'authBasic'){echo "Email Authentication";}elseif($value =='authDuo'){echo "Duo Security";}else{echo ucfirst($value);}  ?></span></div></div>
          
        </div>
       

      </div>

      <div class="clear-both"></div>
     
    </div> </div> <?php }} ?></div></div></div>
             
<?php }
}
$pagination = Reg::tplget('logPagination');

if(empty($pagination['totalPage'])){ ?>
<div class="empty_data_set"> <div class="line2">New subsequent login activity will be logged here.</div></div>
<?php } ?>
<script>
<?php if($pagination['page'] == 1){ ?>
$("#logHistoryPagination").show().jPaginator({
  nbVisible:5,
  nbPages:<?php echo $pagination['totalPage']; ?>,
  selectedPage:<?php echo $pagination['page']; ?>,
  overBtnLeft:'#logHistoryPagination_o_left',
  overBtnRight:'#logHistoryPagination_o_right',
  maxBtnLeft:'#logHistoryPagination_m_left',
  maxBtnRight:'#logHistoryPagination_m_right',
  withSlider: false,
  widthPx: 25,
  marginPx: 0,
  onPageClicked: function(a,num) {
      tempArray={};
      tempArray['requiredData']={};
      tempArray['requiredData']['getLogPageHTML']={};
      tempArray['requiredData']['getLogPageHTML']['page']=num;

      doCall(ajaxCallPath,tempArray,'loadLogHistoryPageContent');
    }
  });
<?php } ?>
<?php if(empty($pagination['totalPage'])){ ?>
$("#logHistoryPagination").hide();
<?php } ?>


</script>
