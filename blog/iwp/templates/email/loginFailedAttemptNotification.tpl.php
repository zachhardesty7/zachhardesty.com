<?php

/************************************************************
 * InfiniteWP Admin panel                 *
 * Copyright (c) 2012 Revmakx               *
 * www.revmakx.com                      *
 *                              *
 ************************************************************/
$loginMailDetails = $d['loginDetails']; 
//subject starts here
?>InfiniteWP - 3 failed login attempts in the last 30 minutes <?php
//subject ends here
echo '+-+-+-+-+-+-+-+-+-+-+-+-+-mailTemplates+-+-+-+-+-+-+-+-+-+-+-';

//message starts here
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<!-- If you delete this tag, the sky will fall on your head -->
<meta name="viewport" content="width=device-width" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>InfiniteWP</title>
</head>
<body bgcolor="#e4e4e4" style="font-family: 'Open Sans', 'Helvetica Neue', Helvetica, Arial, 'Lucida Grande', sans-serif; line-height: 1.1; margin: 0; padding: 0; color:#000; -webkit-font-smoothing:antialiased; -webkit-text-size-adjust:none; 	width: 100%!important; 	height: 100%; background-color:#e4e4e4;">
<table class="head-wrap" style="width:100%;">
  <tr>
    <td></td>
    <td class="header container" style="display: block!important;
    max-width: 600px!important;
    margin: 0 auto!important;
    clear: both!important;"><div class="content">
        <table style="    width: 100%;">
          <tr>
            <td align="center"><img src="https://s3.amazonaws.com/iwp_emails/2.5Duo/iwp_mailer_hdr_logo.jpg" style="max-width: 100%; margin: 30px;" />
            <h3 style="text-align: center; font-weight: normal; font-size: 22px; padding: 10px 0 20px; margin:0;">3 failed login attempts in the last 30 minutes</h3></td>
          </tr>
        </table>
      </div></td>
    <td></td>
  </tr>
</table>
<table class="body-wrap" style="width:100%;">
  <tr>
    <td></td>
    <td class="container" bgcolor="#FFFFFF" style="display: block!important; max-width: 600px!important; margin: 0 auto!important; clear: both!important;   padding: 0;  border-radius: 5px;"><div class="content" style="padding: 15px; max-width: 600px; margin: 0 auto; display: block;">
        <table style="width: 100%;">
          <tr>
            <td><p style="line-height: 2em; font-size: 13px; margin:0;">Someone tried to login to your InfiniteWP admin panel with incorrect credentials 3 times in the last 30 minutes.<br /><br />
            <?php echo @date('M d, Y @ h:ia', $loginMailDetails['time']); ?><br />
              IP:	<?php echo $loginMailDetails['loginIP'] ?><br />
              <?php
              if (!empty($loginMailDetails['locationDetails']['city']) && isset($loginMailDetails['locationDetails']['city']) ) {
                  ?> Location: <?php echo $loginMailDetails['locationDetails']['city']; ?>, <?php $loginMailDetails['locationDetails']['country'] ?>.<br /> 
              <?php }
                ?>
                Browser: <?php echo $loginMailDetails['browserInfo'];?>
                <br />
                <br />
If the information above looks familiar, you can disregard this email. <br />
If you have not recently not tried to login to your panel, someone may be trying to.<br /><br />
There are additional steps that you can take to secure your admin panel. In your admin panel, go to Settings &rarr; Security.</p>
<a href= <?php echo APP_URL;?> style="background-color: #DF5A49; color: #fff; font-size: 14px; font-weight: 600; padding: 16px 0; cursor: pointer; display: block; width: 270px;
    margin: 40px auto 20px; border-radius: 5px; text-align: center; text-decoration: none; ">OPEN MY ADMIN PANEL</a>
              <!-- /social & contact --></td>
          </tr>
        </table>
      </div></td>
    <td></td>
  </tr>
</table>
<!-- /BODY --> 

<!-- FOOTER -->
<table class="footer-wrap" style="width: 100%;	clear:both!important;">
  <tr>
    <td></td>
    <td class="container" style="    display: block!important;
    max-width: 600px!important;
    margin: 0 auto!important;
    clear: both!important;"><!-- content -->
      
      <div class="content">
        <table style="    width: 100%;">
          <tr>
            <td align="center"><div style="text-align:center; color:#7d8895; font-size:11px; line-height: 24px; padding-top: 30px; padding-bottom:20px;">InfiniteWP - Self-hosted Multiple WordPress Management platform<br>
    SP-152, 4th Lane, 1st Main Road, Chennai 600058 TN India<br>
     </div></td>
          </tr>
        </table>
      </div>
      <!-- /content --></td>
    <td></td>
  </tr>
</table>
<!-- /FOOTER -->

</body>
</html>