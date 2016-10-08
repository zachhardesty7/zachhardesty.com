<?php
/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/
 
$isLoginPage = true; 
define('USER_SESSION_NOT_REQUIRED', true);
include("includes/app.php");
//This function is used to identify the error msg to display the user
function getLoginPageMsg($key) {
    $errorMsg = array();
    $errorMsg['invalid'] 			= 'Invalid credentials.';
    $errorMsg['access'] 			= 'Access restricted.';
    $errorMsg['reactive'] 			= 'You are deactivated by admin.';
    $errorMsg['onlyAdmin'] 			= 'Only admin can log in.';
    $errorMsg['passcodeValidity'] 	= 'Your Passcode has been expired.';
    $errorMsg['passcodeInvalid']	= 'Invalid passcode.';
    $errorMsg['passcodeMailError'] 			= 'Mail send error on two factor authentication.';
    $errorMsg['accountLock'] 		= 'You account has been locked. Please wait %s to regain access to your panel. ';
    $errorMsg['resetPasswordLinkExpired'] 		= 'Reset link expired. Reinitiate the password reset.';
    $errorMsg['resetPasswordLinkInvalid'] 		= 'Reset link expire or invalid. Reinitiate the password reset.';
    $errorMsg['resetPasswordMailError'] 		= 'Unable to send email. Check email settings.';
    $errorMsg['resetPasswordEmailNotFound'] 	= 'Oops. We weren\'t able to find that email.<br>Please make sure this is email that you used before.';
    $errorMsg['resetPasswordFailed'] 	= 'Error while resetting the password.Try again!';
    $errorMsg['resetPasswordInvalidPassword']	= 'Please enter valid password.';//NEED BETTER CONTENT    
    setHook('errorMsgTemplate', $errorMsg);

    $successMsg = array();    
    $successMsg['logout'] 				= 'You have successfully logged out.';
    $successMsg['passcodeMailSent']		= 'We have sent you an email.<br>Check your email now.';
    $successMsg['resetPasswordChanged'] = 'Password reset successfully.<br> You can login now with your new password.';    
    $successMsg['resetPasswordMailSent']= 'We have sent you a password reset link.<br>Check your email now.';  

    
    setHook('successMsgTemplate', $successMsg);
        
    if(isset($errorMsg[$key])){
    	return $errorMsg[$key];
    }
    elseif(isset($successMsg[$key])){
    	return $successMsg[$key];
    }
    return '';
}

function printLoginPageMsg(){
	$successMsg = $errorMsg = '';
	if(!empty($_GET['errorMsg'])) {
	    $errorMsg = getLoginPageMsg($_GET['errorMsg']);
	    $errorMsg = formateMsg($errorMsg);
	    echo '<div class="errorMsg">'.$errorMsg.'</div>';
	} elseif(!empty($_GET['successMsg'])) {
	    $successMsg = getLoginPageMsg($_GET['successMsg']);
	    $successMsg = formateMsg($successMsg);
	    echo '<div class="successMsg">'.$successMsg.'</div>';
	}
}

function formateMsg($msgStr) {
    if(!empty($_GET['lockOut'])) {
        $lockOutString = base64_decode($_GET['lockOut']) - time();
        if($lockOutString<0) {
            header('Location: '.APP_URL.'login.php');
            exit;
        }
        return sprintf($msgStr, convertToMinSec($lockOutString));
    }
    return $msgStr;
}

//controllers start here
if(isset($_POST['sig_response'])){
    if(function_exists('verifyDuoSign')){
        if(!verifyDuoSign($_POST)) {
            $_GET['errorMsg'] = "duoFailed";
        }
    }
}elseif(!empty($_GET['passlink'])){
    verifyPasscode($_GET['passlink'], 'link');
}elseif(!empty($_POST['passcode'])){
    verifyPasscode($_POST);
}elseif(!empty($_POST['email']) && !empty($_POST['password'])){
    userLogin($_POST);
}
elseif(!empty($_GET['logout'])){
    userLogout(true);
}
elseif(!empty($_POST['action']) && ($_POST['action'] == 'resetPasswordSendMail' || $_POST['action'] == 'resetPasswordChange')){
	userLoginResetPassword($_POST);
}
elseif(!empty($_GET['view']) && $_GET['view'] == 'resetPasswordChange'){
	userLoginResetPassword($_GET);
}
//controllers ends here

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="noindex">
<title>InfiniteWP</title>
<link href='https://fonts.googleapis.com/css?family=Droid+Sans:400,700' rel='stylesheet' type='text/css'>
<link rel="stylesheet" type="text/css" href="css/core.css?<?php echo APP_VERSION; ?>" />
<script src="js/jquery.min.js?<?php echo APP_VERSION; ?>" type="text/javascript" charset="utf-8"></script>
<script>
$(document).ready(function(){
	$(".showPass").live('mousedown',function(){			
		var btn=document.getElementById('newPasswordOrg');
  		btn.setAttribute('type', 'text');
  		$(this).text("Hide");  		
	}).live('mouseup',function(){			
		var btn=document.getElementById('newPasswordOrg');
  		btn.setAttribute('type', 'password');
		$(this).text("Show");			
	});

    $('.first-element').focus();
});

</script>
</head>
<body>
<div class="signin_cont">
<form action="login.php" method="post" name="loginForm">
<div id="logo_signin"></div>

<?php

if($_GET['view'] == 'resetPassword'){//show email form for resetting password
	showResetPasswordForm();
} elseif($_GET['view'] == 'resetPasswordChange'){//show new password form for resetting via email link
	showResetPasswordChangeForm();
}elseif($_GET['view'] == 'getPasscode'){//Get the passcode from user from mail
	showGetPasscodeForm();
}elseif($_GET['view'] == 'duoFrame'){
	showDuoFrame();
} else{//show login form
	showLoginForm();
}

function showResetPasswordForm(){
	?>
	<div class="copy simple">Enter your email address and <br>we&#39ll send you a link to reset your password.</div>
	<?php printLoginPageMsg(); ?>
	<input type="text" name="email" placeholder="Email" class="first-element" />
    <input type="hidden" name="action" value="resetPasswordSendMail" />
    <input type="submit" id="loginSubmitBtn" name="loginSubmit" value="Send Reset Link" class="btn rep_sprite" />
	<div class="copy simple" style="margin-top: 50px;">If you don't receive an email within a few minutes, check your spam filter as sometimes they end up in there.</div>
	<?php
}


function showResetPasswordChangeForm(){
	?>
	<div class="copy simple">Create a new password below</div>
	<?php printLoginPageMsg(); ?>
	<div style="position:relative;">
		<input type="password" class="reset newPasswordOrg first-element" id="newPasswordOrg" item="pass" placeholder="New password" name="newPassword" />
		<a style="position: absolute; right: 10px; top: 10px; font-size: 12px;" class="showPass" >Show</a>
	</div>
	<input type="hidden" name="transID" value="<?php echo $_GET['transID']; ?>" />
	<input type="hidden" name="resetHash" value="<?php echo $_GET['resetHash']; ?>" />
    <input type="hidden" name="action" value="resetPasswordChange" />
    <input type="submit" id="loginSubmitBtn" name="loginSubmit" value="Reset Password" class="btn rep_sprite" />
	<?php
}

function showLoginForm(){
	?>
	<div class="copy">Sign In to manage your WordPress sites</div>
	<?php printLoginPageMsg(); ?>
	<input type="text" name="email" placeholder="Email" id="email" class="first-element"/>
	<div style="position:relative;">
		<input type="password" name="password" placeholder="Password" id="password"/>
		<a href="login.php?view=resetPassword" style="position:absolute; right:10px; top:10px; font-size:12px;">Forgot?</a>
	</div>
	<input type="submit" id="loginSubmitBtn" name="loginSubmit" value="Log in" class="btn rep_sprite" />
	<?php
}

function showGetPasscodeForm() {
	?>
	<div class="copy">Sign In to manage your WordPress sites</div>
	<?php printLoginPageMsg(); ?>
        <input type="text" name="passcode" class="loginOnEnter" placeholder="Passcode" class="first-element">
        <input type="hidden" name="auth" value="passcode" />
	<input type="submit" id="loginSubmitBtn" name="loginSubmit" value="Log in" class="btn rep_sprite" />
        <div class="copy simple" style="margin-top: 50px;">If you don't receive an email within a few minutes, check your spam filter as sometimes they end up in there.</div>
	<?php
}

function showDuoFrame(){
	?>
	<div class="copy simple">Select the authentication mode &amp; hit the Log in button</div>
	<?php
	echo $GLOBALS['duoFrameStr'];
}
?>
</form>
</div>
</body>
</html>