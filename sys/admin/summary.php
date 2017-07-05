<?php

wincache_ucache_delete('bookingsettingsdat');
wincache_ucache_delete('bookingdatesdat');
wincache_ucache_delete('bookingtimetable');

if(!$AD->isAdmin())
{
	header('Location: ' . $SITE->path . '/index.php?page=home');
	exit();
}

if(isset($_GET['save']) && htmlspecialchars($_GET['save']) == 1)
{
	wincache_ucache_delete('bookingsettingsdat');

	$settings = array('site' => array());
	
	foreach($SETTINGS->getIni()['site'] as $key => $value)
	{
		$settings['site'][$key] = $_POST['site-' . $key];
	}
	
	foreach($SETTINGS->getIni()['yearblocks'] as $key => $value)
	{
		$settings['yearblocks'][$key] = $SETTINGS->getIni()['yearblocks'][$key];
	}

	$SETTINGS->rawWrite($settings, 'settings.ini', true);

	addLog('Saved site settings', 5);

	header('Location: ' . $SITE->path . '/index.php?' . base64_decode($_GET['ret']));
	exit();
}
if(isset($_GET['save']) && htmlspecialchars($_GET['save']) == 2)
{
	wincache_ucache_delete('bookingsettingsdat');
	
	$settings = array('site' => array());
	
	foreach($SETTINGS->getIni()['site'] as $key => $value)
	{
		$settings['site'][$key] = $SETTINGS->getIni()['site'][$key];
	}
	
	foreach($SETTINGS->getIni()['yearblocks'] as $key => $value)
	{ 
		$settings['yearblocks'][$key] = $_POST['yearblocks-' . $key];
	}

	$SETTINGS->rawWrite($settings, 'settings.ini', true);
	
	addLog('Saved year blocks', 5);

	header('Location: ' . $SITE->path . '/index.php?' . base64_decode($_GET['ret']));
	exit();
}

ob_start();
?>
<img src="<?php echo $SITE->path; ?>/theme/gfx/plus.png" class="prefetch" width="512" height="512"/>
<img src="<?php echo $SITE->path; ?>/theme/gfx/minus.png" class="prefetch" width="512" height="512"/>

<script type="text/javascript">
$(document).ready(function(){
	$('#settings span').click(function(){
		$('#settings').toggleClass("expanded").find("form").toggle();
	});
	$('#yearblocks span').click(function(){
		$('#yearblocks').toggleClass("expanded").find("form").toggle();
	});
	$('#housekeeping span').click(function(){
		$('#housekeeping').toggleClass("expanded").find("div").toggle();
	});
	$('#settingtests span').click(function(){
		$('#settingtests').toggleClass("expanded").find("form").toggle();
	});
});
</script>

<?php include('adminmenu.php'); ?>

<div id="settings" class="collapsable">
<span>Settings</span>
	<form action="<?php echo htmlspecialchars($SITE->path); ?>/index.php?page=admin-summary&ret=<?php echo base64_encode($_SERVER['QUERY_STRING']); ?>&save=1" method="post">
	<table>
		<?php
		foreach($SETTINGS->getIni()['site'] as $key => $value)
		{
			echo '<tr><td><label for="site-' . $key . '">';
			if(isset($LANG['settings-' . $key]))
			{	
				echo $LANG['settings-' . $key];
			}else{
				echo $key;
			}
			echo '</label></td><td><input type="';
			if($value == 'true' || $value == 'false')
			{
				echo 'text';
			}else{
				echo 'text';
			}
			echo '" value="' . $SITE->$key . '" size="40" id="site-' . $key . '" name="site-' . $key .'"/></td></tr>';
		}
		?>
		<tr><td></td><td><input type="submit" value="Save" /></td></tr>
	</table>
	</form>
</div>

<div id="yearblocks" class="collapsable">
<span>Year Block Outs</span>
	<form action="<?php echo htmlspecialchars($SITE->path); ?>/index.php?page=admin-summary&ret=<?php echo base64_encode($_SERVER['QUERY_STRING']); ?>&save=2" method="post">
	<table>
		<tr><td>Year 7</td><td><input class="dateField" type="text" value="<?php echo  $SITE->yearblocks['y07s']; ?>" size="12" id="yearblocks-y07s" name="yearblocks-y07s"/> - <input class="dateField" type="text" value="<?php echo  $SITE->yearblocks['y07e']; ?>" size="12" id="yearblocks-y07e" name="yearblocks-y07e"/></td></tr>
		<tr><td>Year 8</td><td><input class="dateField" type="text" value="<?php echo  $SITE->yearblocks['y08s']; ?>" size="12" id="yearblocks-y08s" name="yearblocks-y08s"/> - <input class="dateField" type="text" value="<?php echo  $SITE->yearblocks['y08e']; ?>" size="12" id="yearblocks-y08e" name="yearblocks-y08e"/></td></tr>
		<tr><td>Year 9</td><td><input class="dateField" type="text" value="<?php echo  $SITE->yearblocks['y09s']; ?>" size="12" id="yearblocks-y09s" name="yearblocks-y09s"/> - <input class="dateField" type="text" value="<?php echo  $SITE->yearblocks['y09e']; ?>" size="12" id="yearblocks-y09e" name="yearblocks-y09e"/></td></tr>
		<tr><td>Year 10</td><td><input class="dateField" type="text" value="<?php echo  $SITE->yearblocks['y10s']; ?>" size="12" id="yearblocks-y10s" name="yearblocks-y10s"/> - <input class="dateField" type="text" value="<?php echo  $SITE->yearblocks['y10e']; ?>" size="12" id="yearblocks-y10e" name="yearblocks-y10e"/></td></tr>
		<tr><td>Year 11</td><td><input class="dateField" type="text" value="<?php echo  $SITE->yearblocks['y11s']; ?>" size="12" id="yearblocks-y11s" name="yearblocks-y11s"/> - <input class="dateField" type="text" value="<?php echo  $SITE->yearblocks['y11e']; ?>" size="12" id="yearblocks-y11e" name="yearblocks-y11e"/></td></tr>
		<tr><td>Year 12</td><td><input class="dateField" type="text" value="<?php echo  $SITE->yearblocks['y12s']; ?>" size="12" id="yearblocks-y12s" name="yearblocks-y12s"/> - <input class="dateField" type="text" value="<?php echo  $SITE->yearblocks['y12e']; ?>" size="12" id="yearblocks-y12e" name="yearblocks-y12e"/></td></tr>
		<tr><td>Year 13</td><td><input class="dateField" type="text" value="<?php echo  $SITE->yearblocks['y13s']; ?>" size="12" id="yearblocks-y13s" name="yearblocks-y13s"/> - <input class="dateField" type="text" value="<?php echo  $SITE->yearblocks['y13e']; ?>" size="12" id="yearblocks-y13e" name="yearblocks-y13e"/></td></tr>
		<tr><td></td><td><input type="submit" value="Save" /></td></tr>
		<script type="text/javascript">
		$(document).ready(function(){
			$(".dateField").datepicker({dateFormat: 'dd/mm/yy', beforeShowDay: $.datepicker.noWeekends, minDate: '<?php echo $minDate; ?>', maxDate: '<?php echo $maxDate; ?>'});
		});
		</script>
	</table>
	</form>
</div>

<?php

$bookingcountresult = $SQL->query("SELECT * FROM dbo.BKLessons;");
$timetablecountresult = $SQL->query("SELECT * FROM dbo.TTLessons;");

$bookingcount = 0;
$timetablecount = 0;

while(sqlsrv_fetch_array($bookingcountresult, SQLSRV_FETCH_ASSOC)){$bookingcount++;}
while(sqlsrv_fetch_array($timetablecountresult, SQLSRV_FETCH_ASSOC)){$timetablecount++;}

?>

<div id="housekeeping" class="collapsable">
<span>House Keeping</span>
	<div>
	<table>
		<tr><td>No of Bookings</td><td><?php echo $bookingcount; ?></td></tr>
		<tr><td>No of Timetabled periods</td><td><?php echo $timetablecount; ?></td></tr>
	</table>
	</div>
</div>

<?php
$LDAPtest = new LDAP($SITE->ldap);
$LDAPtestBind = $LDAPtest->bind($SITE->ldapuser, $SITE->ldappass);
?>

<div id="settingtests" class="collapsable">
<span>Tests</span>
	<form>
	<table>
		<tr><td>LDAP Bind : </td><td><?php var_dump($LDAPtestBind); ?></td></tr>
		<tr><td>LDAP Error : </td><td><?php echo $LDAPtest->getExtendedError(); ?></td></tr>
	</table>
	</form>
</div>

<?php
$body = ob_get_clean();
?>
