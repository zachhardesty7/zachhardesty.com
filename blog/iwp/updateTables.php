<?php

/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/

include('includes/app.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link rel="stylesheet" type="text/css" href="css/core.css">
<link href='//fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,700italic,800italic,400,300,700,800' rel='stylesheet' type='text/css'>
<link href="//netdna.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.css" rel="stylesheet">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Converting</title>
</head>
<body style="font-family: 'Open Sans', sans-serif; font-size:12px; color: #555; line-height:24px; background: #dce1e3;">

<?php
if($_GET['action'] =='InnoDBConversion'){ ?>

<div style="background: white;width: 290px; height: 125px; position: absolute; top: 50%; margin-left: -150px;left: 50%;border-radius: 3px;">
<div style="padding: 19px 60px;">Upgrading database engine...</div>
<span class="loadingSp" style="position: absolute;top: 60px;left: 126px;"> </span>
</div>

<?php
$retun = processInnoDBConversion();
if (is_string ($retun) && $retun == 'redirect') {
		?>
<script>
 	window.location = 'updateTables.php?action=InnoDBConversion';
    
</script>

<?php
} elseif ($retun == true) {
?>
<script>
    window.location = 'updateTables.php?action=InnoDBSuccess';
</script>

<?php
}elseif ($retun == false) {
?>
<script>
    window.location = 'updateTables.php?action=InnoDBfailure';
</script>

<?php
}
?>

<?php }
if ($_GET['action'] =='InnoDBSuccess') { ?>
<div style="background: white;width: 290px; height: 125px; position: absolute; top: 50%; margin-left: -150px;left: 50%;border-radius: 3px;">
<div style="padding: 19px 86px;color: #069414;"><i class="fa fa-check" style="top: 23px;left: 62px;"></i>Successfully updated</div><div class="btn_action " style="margin-right: -27px;left: 89px;cursor:pointer;"><a class="btn_blue confirmAction" style="color: #6C7277;cursor:pointer;background: #49a1de; padding: 6px 11px 13px;margin-left: 14px;" href="<?php echo APP_URL ?>">Reload App.</a></div>
</div>
<?php }
if ($_GET['action'] =='InnoDBfailure') { ?>
<div style="background: white;width: 410px;height: 200px;position: absolute;top: 50%;margin-left: -205px;left: 50%;border-radius: 3px;">
<div style="padding: 19px 157px;color: #F41111;"><i class="fa fa-exclamation-triangle" style="font-size: 14px;color: #f41111;left: 137px;top: 22px;"></i>Process failure...</div><div style="color:#6C7277;text-align:center"> <?php echo showInnoDBConversionError(); ?></div> <div class="btn_action " style="margin-right: -27px;left: 89px;cursor:pointer;"><a class="btn_blue confirmAction" style="color: #6C7277;cursor:pointer;background: #49a1de;padding: 6px 11px 13px;margin-left: 63px;margin-top: 24px;" href="<?php echo APP_URL ?>">Reload App.</a></div>
</div>
<?php }
?>
</body>
</html>

	