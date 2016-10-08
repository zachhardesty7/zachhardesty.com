<?php

/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/
$verificationURL = $d['passcodeLink']; 
$passcode = $d['passcode']; 

//subject starts here
?> Email Authentication Request From your IWP admin panel. <?php

//subject ends here
echo '+-+-+-+-+-+-+-+-+-+-+-+-+-mailTemplates+-+-+-+-+-+-+-+-+-+-+-';

//message starts here

?><!doctype html>
<html>
<head>
<meta charset="UTF-8">
<style>
@media only screen and (max-width: 540px) {
table {
    width: 90% !important;
}
}
</style>
</head>

<body bgcolor="#dce1e3" style="padding:20px 0; margin:0; font-family:'Helvetica Neue','Helvetica',Helvetica,Arial,sans-serif; font-size:13px;">
<table bgcolor="#FFF" width="500" border="0" style="margin:20px auto; line-height: 22px; border-collapse:collapse; box-shadow: 0 1px 1px rgba(0,0,0,0.2);" cellpadding="10">
  <tr bgcolor="#1d292c">
    <td  width="27%" align="left"><strong style="color:white;">INFINITE<span style="color: #f2583e;">WP</span></strong></td>
    <td  width="73%" align="right"><span style="color:#fff; font-size:10px;">MULTIPLE WORDPRESS MANAGEMENT</span></td>
  </tr>
  <tr>
    <td colspan="2"><div style="padding:10px; font-size: 14px;">
        <div style=" font-size:20px; font-weight:bold; margin-bottom:20px; text-align:center;">Login request</div>
        <div style="text-align:center; font-size:12px; line-height:24px;"><?php echo $d['appUrl']?>  <br>
          <?php echo $d['userEmail']?> &bull; <?php echo $d['requestIp']?><br>
          Request valid till <?php echo $d['expired']?></div>
        <a class="btn" href="<?php echo $verificationURL; ?>" target="_blank" style="margin: 0; padding: 11px 20px 9px;  font-family: 'Helvetica Neue','Helvetica',Helvetica,Arial,sans-serif;  background: #4ea210;  border-radius: 4px; border:0;  display: block;  line-height: 1.5;  border-bottom: 3px solid rgba(0,0,0,0.1);  text-decoration: none;  color: #fff;   width: 230px;  text-align:center;  margin: 30px auto 40px;">
        Approve Login Request</a>
        <hr style="border:0; border-top:1px solid #ddd;">
        <div style="text-align:center; padding:10px;"> You can also use this passcode to login.<br>
          <br>
          Passcode<br>
        <span style="  background-color: #eee;  padding: 4px;  display: block;  width: 64px;  margin: 5px auto 0;  font-size: 18px;  border-radius: 5px;  border: 1px dashed #ccc;"><?php echo $passcode;?></span></div>
      </div></td>
  </tr>
</table>
</body>
</html>