<?php

/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/

$verificationURL = $d['verificationURL']; 
//subject starts here
?> Password Reset Request From your IWP admin panel. <?php

//subject ends here
echo '+-+-+-+-+-+-+-+-+-+-+-+-+-mailTemplates+-+-+-+-+-+-+-+-+-+-+-';

//message starts here

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
<style></style>
</head>

<body bgcolor="#dce1e3" style="padding:0; margin:0; font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#555;">
<div style="background:#dce1e3;">
  <table bgcolor="#FFFFFF" width="600" border="0" cellspacing="0" cellpadding="0" style="border-collapse:collapse; margin:auto; border-bottom:2px solid #1d292c;">
    <tr style="width:600px; height:35px; background:#1d292c;">
      <td><div style="width:90px; height:13px; float:left; margin:10px; color:#fff; border:1px solid #1d292c; font-family:Arial, Helvetica, sans-serif; font-size:12px; font-weight:bold;">INFINITE<span style="color:#f2583e;">WP</span></div></td>
      <td><div style="font-family:Arial, Helvetica, sans-serif; font-size:11px; text-transform:uppercase; float:right; padding:10px; color:#fff; font-weight:bold; letter-spacing:0.5px;">MULTIPLE WORDPRESS MANAGEMENT</div></td>
    </tr>
    
    <tr>
      <td  colspan="2"><div style="padding:20px"><br>Hey there, <br>You had requested to reset the password to your InfiniteWP admin panel.<br> <br> <br>

To reset your password, click on the button below: <br>

<a class="btn" href="<?php echo $verificationURL; ?>" target="_blank" style="margin: 0; padding: 11px 20px 9px;  font-family: 'Helvetica Neue','Helvetica',Helvetica,Arial,sans-serif;  background: #4ea210;  border-radius: 4px; border:0;  display: block;  line-height: 1.5;  border-bottom: 3px solid rgba(0,0,0,0.1);  text-decoration: none;  color: #fff;   width: 230px;  text-align:center;  margin: 30px auto 40px;">
Reset Password</a>
 <br> <br>
Thanks,<br>
<br>
Note: If you didn't ask to reset the password, you can safely ignore this email.
<br>

<br>
--
<br>

This email was sent from your admin panel at <?php echo APP_URL; ?>

</div>


</td>
    </tr>
    
  </table>  
</div>
</body>
</html>