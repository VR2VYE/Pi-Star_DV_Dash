<?php
// Load the language support
require_once('config/language.php');
// Load the Pi-Star Release file
$pistarReleaseConfig = '/etc/pistar-release';
$configPistarRelease = array();
$configPistarRelease = parse_ini_file($pistarReleaseConfig, true);
// Load the Version Info
require_once('config/version.php');
// Sanity Check that this file has been opened correctly
if ($_SERVER["PHP_SELF"] == "/admin/config_backup.php") {
  // Sanity Check Passed.
  header('Cache-Control: no-cache');
  session_start();
?>
  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" lang="en">
  <head>
    <meta name="robots" content="index" />
    <meta name="robots" content="follow" />
    <meta name="language" content="English" />
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta name="Author" content="Andrew Taylor (MW0MWZ)" />
    <meta name="Description" content="Pi-Star Power" />
    <meta name="KeyWords" content="Pi-Star" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="pragma" content="no-cache" />
<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <meta http-equiv="Expires" content="0" />
    <title>Pi-Star - <?php echo $lang['digital_voice']." ".$lang['dashboard']." - ".$lang['backup_restore'];?></title>
    <link rel="stylesheet" type="text/css" href="css/pistar-css.php" />
  </head>
  <body>
  <div class="container">
  <div class="header">
  <div style="font-size: 8px; text-align: right; padding-right: 8px;">Pi-Star:<?php echo $configPistarRelease['Pi-Star']['Version']?> / <?php echo $lang['dashboard'].": ".$version; ?></div>
  <h1>Pi-Star <?php echo $lang['digital_voice']." - ".$lang['backup_restore'];?></h1>
  <p style="padding-right: 5px; text-align: right; color: #ffffff;">
    <a href="/" style="color: #ffffff;"><?php echo $lang['dashboard'];?></a> |
    <a href="/admin/" style="color: #ffffff;"><?php echo $lang['admin'];?></a> |
    <a href="/admin/power.php" style="color: #ffffff;"><?php echo $lang['power'];?></a> |
    <a href="/admin/update.php" style="color: #ffffff;"><?php echo $lang['update'];?></a> |
    <a href="/admin/configure.php" style="color: #ffffff;"><?php echo $lang['configuration'];?></a>
  </p>
  </div>
  <div class="contentwide">
<?php if (!empty($_POST)) {
  echo '<table width="100%">'."\n";

        
        if ( escapeshellcmd($_POST["action"]) == "restore" ) {
          echo "<tr><th colspan=\"2\">Config Restore</th></tr>\n";
          $output = "Uploading your Config data\n";

          $target_dir = "/tmp/config_restore/";
          shell_exec("sudo rm -rf $target_dir 2>&1");
          shell_exec("mkdir $target_dir 2>&1");
          if($_FILES["fileToUpload"]["name"]) {
                  $filename = $_FILES["fileToUpload"]["name"];
	  	  $source = $_FILES["fileToUpload"]["tmp_name"];
	          $type = $_FILES["fileToUpload"]["type"];
	
	          $name = explode(".", $filename);
	          $accepted_types = array('application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed');
	          foreach($accepted_types as $mime_type) {
		          if($mime_type == $type) {
			          $okay = true;
			          break;
			  }
		  }
	  }
		
			
			// Stop the DV Services
			shell_exec('sudo systemctl stop cron.service 2>&1');		//Cron
			shell_exec('sudo systemctl stop dstarrepeater.service 2>&1');	//D-Star Radio Service
			shell_exec('sudo systemctl stop mmdvmhost.service 2>&1');	//MMDVMHost Radio Service
			shell_exec('sudo systemctl stop ircddbgateway.service 2>&1');	//ircDDBGateway Service
			shell_exec('sudo systemctl stop timeserver.service 2>&1');	//Time Server Service
			shell_exec('sudo systemctl stop pistar-watchdog.service 2>&1');	//PiStar-Watchdog Service
			shell_exec('sudo systemctl stop pistar-remote.service 2>&1');	//PiStar-Remote Service
			shell_exec('sudo systemctl stop ysfgateway.service 2>&1');	//YSFGateway
			shell_exec('sudo systemctl stop ysf2dmr.service 2>&1');		//YSF2DMR
			shell_exec('sudo systemctl stop p25gateway.service 2>&1');	//P25Gateway
			
			// Make the disk Writable
			shell_exec('sudo mount -o remount,rw / 2>&1');
			
		
		
			// Start the services
			$output .= "Starting Services.\n";
			shell_exec('sudo systemctl start dstarrepeater.service 2>&1');		//D-Star Radio Service
			shell_exec('sudo systemctl start mmdvmhost.service 2>&1');		//MMDVMHost Radio Service
			shell_exec('sudo systemctl start ircddbgateway.service 2>&1');		//ircDDBGateway Service
			shell_exec('sudo systemctl start timeserver.service 2>&1');		//Time Server Service
			shell_exec('sudo systemctl start pistar-watchdog.service 2>&1');	//PiStar-Watchdog Service
			shell_exec('sudo systemctl start pistar-remote.service 2>&1');		//PiStar-Remote Service
			if (substr(exec('grep "pistar-upnp.service" /etc/crontab | cut -c 1'), 0, 1) !== '#') {
				shell_exec('sudo systemctl start pistar-upnp.service 2>&1');		//PiStar-UPnP Service
			}
			shell_exec('sudo systemctl start ysfgateway.service 2>&1');		//YSFGateway
			shell_exec('sudo systemctl start ysf2dmr.service 2>&1');		//YSF2DMR
			shell_exec('sudo systemctl start p25gateway.service 2>&1');		//P25Gateway
			shell_exec('sudo systemctl start cron.service 2>&1');			//Cron
			
			// Complete
			$output .= "Configuration Restore Complete.\n";
		}
		else {
			$output .= "There was a problem with the upload. Please try again.<br />";
			$output .= "\n".'<button onclick="goBack()">Go Back</button><br />'."\n";
			$output .= '<script>'."\n";
			$output .= 'function goBack() {'."\n";
			$output .= '    window.history.back();'."\n";
			$output .= '}'."\n";
			$output .= '</script>'."\n";
		}
	  echo "<tr><td align=\"left\"><pre>$output</pre></td></tr>\n";
  };                  

  echo "</table>\n";
  } else { ?>
  <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
  <table width="100%">
  <tr>
    <th colspan="2"><?php echo $lang['backup_restore'];?></th>
  </tr>
  <tr>
    
    <td align="center" valign="top">Restore Configuration<br />
	<button style="border: none; background: none;" name="action" value="restore"><img src="/images/restore.png" border="0" alt="Restore Config" /></button><br />
    	<input type="file" name="fileToUpload" id="fileToUpload" />
    </td>
  </tr>
  <tr>
  <td colspan="2" align="justify">
	  <br />
	  <b>WARNING:</b><br />
	  Editing the files outside of Pi-Star *could* have un-desireable side effects.<br />
	  <br />
	  This backup and restore tool, will backup your config files to a Zip file, and allow you to restore them later<br />
	  either to this Pi-Star or another one.<br />
	  <ul>
		  <li>System Passwords / Dashboard passwords are NOT backed up / restored.</li>
		  <li>Wireless Configuration IS backed up and restored</li>
	  </ul>
  </td>
  </tr>
  </table>
  </form>
<?php } ?>
  </div>
  <div class="footer">
  Pi-Star web config, &copy; Andy Taylor (MW0MWZ) 2014-<?php echo date("Y"); ?>.<br />
  Need help? Click <a style="color: #ffffff;" href="https://www.facebook.com/groups/pistar/" target="_new">here for the Support Group</a><br />
  Get your copy of Pi-Star from <a style="color: #ffffff;" href="http://www.pistar.uk/downloads/" target="_blank">here</a>.<br />
  <br />
  </div>
  </div>
  </body>
  </html>
<?php
}
?>
