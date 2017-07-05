<?php
ob_start();

/* Booking types

1 - Normal booking
2 - Updated booking
3 - Override
4 - Room Closure 
5 - Block booking 
6 - Recurring weekly
7 - Recurring fortnightly */

function getDisplayNames()
{
	global $SITE, $LDAP;
	
	$ous = ['Teachers', 'Technicians', 'Support Staff', 'Admin'];
	
	foreach($ous as $ou)
	{
		$sr = $LDAP->search('OU=' . $ou . ',OU=User Accounts,' . $SITE->ldapDN, '(objectClass=*)', array("displayName"));

		$info = $LDAP->get_entries($sr);

		$names = [];
		
		foreach($info as $entry)
		{
			if(isset($entry["displayname"][0]))
			{
				$names[] = $entry["displayname"][0];
			}
		}
	}

	return $names;
}

if(isset($_POST['action']) && htmlspecialchars($_POST['action']) == 'book')
{	
	$type = 1;
	$period = $day . ':' . $_POST['time'];
	
	if($_POST['recur'] == 'weekly' || $_POST['recur'] == 'fortnight')
	{
		if($_POST['recur'] == 'weekly')
		{
			$type = 6;
		}
		if($_POST['recur'] == 'fortnight')
		{
			$type = 7;
		}
	}
	
	$query = 'INSERT INTO dbo.BKLessons (BK_Room, BK_Period, BK_ClassName, BK_Teacher, BK_NoOfStudents, BK_Date, BK_BookedBy, BK_BookedTime, BK_Type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);';
	
	$tt = $SQL->query($query, array($_POST['room'], $period, $_POST['lesson'], $_POST['bookedfor'], $_POST['noofstudents'], $_POST['date'], $AD->getUser(), date('d/m/Y H:i'), $type));
	
	if($tt == false)
	{
		echo "FAIL!!!!";
		print_r(sqlsrv_errors());
	}
	
	echo "<br />" . $query . "<br />";
	
	echo "done";

	unset($query, $tt);
	
	addLog('Added ' . $_POST['lesson'] . ' at ' . $_POST['date'] . ' in ' . $_POST['room'] . ':' . $_POST['time'] . ' for ' . $_POST['bookedfor'], 1);
	
	$emailcontent = file_get_contents('email/new.html');
	$emailcontent = str_replace('[NAME]', htmlspecialchars($_POST['bookedfor']), $emailcontent);
	$emailcontent = str_replace('[DATE]', htmlspecialchars($_POST['date']), $emailcontent);
	$emailcontent = str_replace('[ROOM]', htmlspecialchars($_POST['room']), $emailcontent);
	$emailcontent = str_replace('[EDITOR]', $AD->getUser(), $emailcontent);
	$emailcontent = str_replace('[REASON]', htmlspecialchars($_POST['reason']), $emailcontent);
	if($_POST['recur'] == 'weekly')
	{
		$emailcontent += PHP_EOL . 'This has been added as a recurring booking weekly';
	}
	if($_POST['recur'] == 'fortnight')
	{
		$emailcontent += PHP_EOL . 'This has been added as a recurring booking fortnightly';
	}
	
	sendNotice($LDAP->getEmailFromName(htmlspecialchars($_POST['bookedfor'])), htmlspecialchars($_POST['bookedfor']), null, $emailcontent);
	
	header('Location: ' . $SITE->path . '/index.php?' . base64_decode($_POST['ret']));
	exit();
}
if(isset($_POST['action']) && htmlspecialchars($_POST['action']) == 'doedit')
{
	$type = 2;
	if($_POST['recur'] == 'weekly' || $_POST['recur'] == 'fortnight')
	{
		if($_POST['recur'] == 'weekly')
		{
			$type = 6;
		}
		if($_POST['recur'] == 'fortnight')
		{
			$type = 7;
		}
	}

	$query = 'UPDATE dbo.BKLessons SET BK_ClassName=?, BK_Teacher=?, BK_NoOfStudents=?, BK_BookedBy=?, BK_BookedTime=?, BK_Type=? WHERE BK_ID=?;';
	
	$tt = $SQL->query($query, array($_POST['lesson'], $_POST['bookedfor'], $_POST['noofstudents'],  $AD->getUser(), date('d/m/Y H:i'), $type, $_POST['id']));
	
	if($tt == false)
	{
		echo "FAIL!!!!";
		print_r(sqlsrv_errors());
	}
	
	echo "<br />" . $query . "<br />";
	
	echo "done";

	unset($query, $tt);
	
	addLog('Edited ' . $_POST['lesson'] . ' at ' . $_POST['date'] . ' in ' . $_POST['room'] . ':' . $_POST['time'] . ' for ' . $_POST['bookedfor'], 2);
	
	$emailcontent = file_get_contents('email/edit.html');
	$emailcontent = str_replace('[NAME]', htmlspecialchars($_POST['bookedfor']), $emailcontent);
	$emailcontent = str_replace('[DATE]', htmlspecialchars($_POST['date']), $emailcontent);
	$emailcontent = str_replace('[ROOM]', htmlspecialchars($_POST['room']), $emailcontent);
	$emailcontent = str_replace('[EDITOR]', $AD->getUser(), $emailcontent);
	$emailcontent = str_replace('[REASON]', htmlspecialchars($_POST['reason']), $emailcontent);
	
	sendNotice($LDAP->getEmailFromName(htmlspecialchars($_POST['bookedfor'])), htmlspecialchars($_POST['bookedfor']), null, $emailcontent);
	
	header('Location: ' . $SITE->path . '/index.php?' . base64_decode($_POST['ret']));
	exit();
}
if(isset($_POST['action']) && htmlspecialchars($_POST['action']) == 'dodelete')
{
	$query = 'DELETE FROM dbo.BKLessons WHERE BK_ID = ?;';
	$tt = $SQL->query($query, array($_POST['id']));
	
	if($tt == false)
	{
		echo "FAIL!!!!";
		print_r(sqlsrv_errors());
	}
	
	echo "<br />" . $query . "<br />";
	
	echo "done";

	unset($query, $tt);
	
	addLog('Deleted ' . $_POST['lesson'] . ' at ' . $_POST['date'] . ' in ' . $_POST['room'] . ':' . $_POST['time'] . ' for ' . $_POST['bookedfor'], 3);
	
	$emailcontent = file_get_contents('email/delete.html');
	$emailcontent = str_replace('[NAME]', htmlspecialchars($_POST['oldbookedfor']), $emailcontent);
	$emailcontent = str_replace('[DATE]', htmlspecialchars($_POST['date']), $emailcontent);
	$emailcontent = str_replace('[ROOM]', htmlspecialchars($_POST['room']), $emailcontent);
	$emailcontent = str_replace('[EDITOR]', $AD->getUser(), $emailcontent);
	$emailcontent = str_replace('[REASON]', htmlspecialchars($_POST['reason']), $emailcontent);
	
	sendNotice($LDAP->getEmailFromName(htmlspecialchars($_POST['oldbookedfor'])), htmlspecialchars($_POST['oldbookedfor']), null, $emailcontent);
	
	header('Location: ' . $SITE->path . '/index.php?' . base64_decode($_POST['ret']));
	exit();
}
if(isset($_POST['action']) && htmlspecialchars($_POST['action']) == 'doclose')
{
	$query = 'SELECT * FROM dbo.BKLessons WHERE BK_Date=? AND BK_Room=? AND BK_Type<>?;';
	
	$booked = $SQL->query($query, array($_POST['date'], $_POST['room'], 4));
	
	while($booking = sqlsrv_fetch_array($booked, SQLSRV_FETCH_ASSOC))
	{
		$emailcontent = file_get_contents('email/close.html');
		$emailcontent = str_replace('[NAME]', $booking['BK_Teacher'], $emailcontent);
		$emailcontent = str_replace('[DATE]', htmlspecialchars($_POST['date']), $emailcontent);
		$emailcontent = str_replace('[ROOM]', htmlspecialchars($_POST['room']), $emailcontent);
		$emailcontent = str_replace('[EDITOR]', $AD->getUser(), $emailcontent);
		$emailcontent = str_replace('[REASON]', htmlspecialchars($_POST['reason']), $emailcontent);
		
		sendNotice($LDAP->getEmailFromName($booking['BK_Teacher']), $booking['BK_Teacher'], null, $emailcontent);
	}
	
	$query = 'INSERT INTO dbo.BKLessons (BK_Room, BK_Period, BK_ClassName, BK_Teacher, BK_NoOfStudents, BK_Date, BK_BookedBy, BK_BookedTime, BK_Type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);';
	
	for($p = $_POST['closedfrom']; $p <= $_POST['closedto']; $p++)
	{
		$tt = $SQL->query($query, array($_POST['room'], $day . $periods[$p], 'Closed<br />' . $_POST['reason'], 'Admin', 0, $_POST['date'], $AD->getUser(), date('d/m/Y H:i'), 4));
	}
	
	if($tt == false)
	{
		echo "FAIL!!!!";
		print_r(sqlsrv_errors());
	}
	
	echo "<br />" . $query . "<br />";
	
	echo "done";
	
	unset($query, $tt);
	
	addLog($AD->getUser() . ' closed ' . $_POST['room'] . ' at ' . $_POST['date'], 4);
	
	header('Location: ' . $SITE->path . '/index.php?page=home&date=' . htmlspecialchars($_POST['date']));
	exit();
}
if(isset($_GET['action']) && htmlspecialchars($_GET['action']) == 'open')
{
	$query = 'SELECT * FROM dbo.BKLessons WHERE BK_Date=? AND BK_Room=? AND BK_Type<>?;';
	
	$booked = $SQL->query($query, array($_GET['date'], $_GET['room'], 4));
	
	while($booking = sqlsrv_fetch_array($booked, SQLSRV_FETCH_ASSOC))
	{
		$emailcontent = file_get_contents('email/restore.html');
		$emailcontent = str_replace('[NAME]', $booking['BK_Teacher'], $emailcontent);
		$emailcontent = str_replace('[DATE]', $_GET['date'], $emailcontent);
		$emailcontent = str_replace('[ROOM]', $_GET['room'], $emailcontent);
		$emailcontent = str_replace('[EDITOR]', $AD->getUser(), $emailcontent);
		$emailcontent = str_replace('[REASON]', 'Closure has been removed', $emailcontent);
		
		sendNotice($LDAP->getEmailFromName($booking['BK_Teacher']), $booking['BK_Teacher'], null, $emailcontent);
	}
	
	$query = 'DELETE FROM dbo.BKLessons WHERE BK_Room=? AND BK_Date=? AND BK_Type=?;';
	
	$tt = $SQL->query($query, array($_GET['room'], $_GET['date'], 4));
	
	if($tt == false)
	{
		echo "FAIL!!!!";
		print_r(sqlsrv_errors());
	}
	
	echo "<br />" . $query . "<br />";
	
	echo "done";
	
	unset($query, $tt);
	
	addLog($AD->getUser() . ' opened ' . $_GET['room'] . ' at ' . $_GET['date'], 4);
	
	header('Location: ' . $SITE->path . '/index.php?page=home&date=' . htmlspecialchars($_GET['date']));
	exit();
}

?>
<script type="text/javascript">
$(document).ready(function(){
	$("#loadingspinner").hide();
	var teacherNames = [
	<?php 
	foreach(getDisplayNames() as $name)
	{
		echo '"' . $name . '",';
	}
	?>
	];
	$("#bookedfor").autocomplete({source: teacherNames});
	$("#noofstudents").on("input", function(){
		var $this = $(this);
		if($this.val() % 1 !== 0 || $this.val().indexOf('e') >= 0 || $this.val() > 32)
		{
			$(":submit").attr("disabled", true);
		}else{
			$(":submit").removeAttr("disabled");
		}
	});
	$("form").submit(function(){
		$("#loadingspinner").show();
	});
});
</script>

<div id="bookingContent">
Booking for - <?php echo $day . ':'; if(isset($_GET['time'])){echo htmlspecialchars($_GET['time']);} ?> in room <?php echo htmlspecialchars($_GET['room']); ?> on <?php echo htmlspecialchars($_GET['date']); ?>
<br /><br />
<table>
<form action="<?php echo htmlspecialchars($SITE->path); ?>/index.php?page=booking&debug=1" method="post">
<input type="hidden" name="ret" id="ret" value="<?php echo base64_encode('page=home&date=' . $_GET['date']) ?>" />
<input type="hidden" name="id" id="id" value="<?php echo htmlspecialchars($_GET['id']); ?>" />
<input type="hidden" name="room" id="room" value="<?php echo htmlspecialchars($_GET['room']); ?>" />
<input type="hidden" name="date" id="date" value="<?php echo htmlspecialchars($_GET['date']); ?>" />
<input type="hidden" name="day" id="day" value="<?php echo $day ?>" />
<input type="hidden" name="time" id="time" value="<?php echo htmlspecialchars($_GET['time']); ?>" />
<?php if(isset($_GET['action']) && $_GET['action'] == 'edit' && ($AD->isAdmin() || $AD->getUser() == $_GET['teacher'])){ ?>
	<input type="hidden" name="action" id="action" value="doedit" />
<?php }else if(isset($_GET['action']) && htmlspecialchars($_GET['action']) == 'close' && $AD->isAdmin()){ ?>
	<input type="hidden" name="action" id="action" value="doclose" />
<?php }else{ ?>
	<input type="hidden" name="action" id="action" value="book" />
<?php } ?>
<?php
$currentDescription = '';
$currentNoOfStudents = '';
$currentTeacher = $AD->getUser();
$currentType = '1';
if(isset($_GET['action']) && htmlspecialchars($_GET['action']) == 'edit' && ($AD->isAdmin() || $AD->getUser() == $_GET['teacher'])){
	$currentBookingQuery = "SELECT * FROM BKLessons WHERE BK_ID = ?;";
	$currentBookingResult = $SQL->query($currentBookingQuery, array($_GET['id']));
	$currentBooking = sqlsrv_fetch_array($currentBookingResult, SQLSRV_FETCH_ASSOC);
	$currentDescription = $currentBooking['BK_ClassName'];
	$currentNoOfStudents = $currentBooking['BK_NoOfStudents'];
	$currentTeacher = $currentBooking['BK_Teacher'];
	$currentType = $currentBooking['BK_Type'];
}
?>
<?php if(!$AD->isAdmin()){echo '<input type="hidden" name="bookedfor" id="bookedfor" value="'. $AD->getUser() .'"/>';} ?>
<tr><td>Lesson Description</td><td><input type="text" name="lesson" id="lesson" value="<?php echo $currentDescription; ?>"/></td></tr>
<tr><td>No. of Students (32 max)</td><td><input type="text" name="noofstudents" id="noofstudents" value="<?php echo $currentNoOfStudents; ?>"/></td></tr>
<tr><td>Booked for</td><td><input type="text" name="bookedfor" autocomplete="off" id="bookedfor" value="<?php echo $currentTeacher; ?>" <?php if(!$AD->isAdmin()){echo "disabled='disabled'";}?>/></td></tr>
<?php if(isset($_GET['action']) && htmlspecialchars($_GET['action']) == 'close' && $AD->isAdmin()){ ?>
<tr><td>Close from</td>
	<td><select name="closedfrom" id="closedfrom">
	<?php
	
	for($p = 0; $p < sizeof($periods); $p++)
	{
		$pname = $periods[$p];
		if(isset($LANG['period-' . strtolower($pname)]))
		{
			$pname = $LANG['period-' . strtolower($pname)];
		}
		echo '<option value="' . $p . '">' . $pname . '</option>';
	}
	
	?>
	</select></td>
	<td>Close till end of</td>
	<td><td><select name="closedto" id="closedto">
	<?php
	
	for($p = 0; $p < sizeof($periods); $p++)
	{
		$pname = $periods[$p];
		if(isset($LANG['period-' . strtolower($pname)]))
		{
			$pname = $LANG['period-' . strtolower($pname)];
		}
		if($p == 10)
		{
			echo '<option value="' . $p . '" selected="selected">' . $pname . '</option>';
		}else{
			echo '<option value="' . $p . '">' . $pname . '</option>';
		}
	}
	
	?>
	</select></td></td>
</tr>
<?php } ?>
<tr><td>Reason</td><td><input type="text" name="reason" id="reason" /></td></tr>
<?php if($AD->isAdmin()){ ?>
<tr><td>Recurrence</td><td>
<select name="recur" id="recur">
<option value="never" <?php if($currentType != '6' || $currentType != '7'){echo 'selected="selected"';} ?>>Never</option>
<option value="weekly" <?php if($currentType == '6'){echo 'selected="selected"';} ?>>Weekly</option>
<option value="fortnight" <?php if($currentType == '7'){echo 'selected="selected"';} ?>>Fortnightly</option>
</select>
</td></tr>
<?php }else{ ?>
<input type="hidden" name="recur" id="recur" value="never"/>
<?php } ?>
<?php if(isset($_GET['action']) && $_GET['action'] == 'edit' && ($AD->isAdmin() || $AD->getUser() == $_GET['teacher'])){ ?>	
	<tr><td colspan="2"><input type="submit" value="Update" /></td></tr>
<?php }else if(isset($_GET['action']) && htmlspecialchars($_GET['action']) == 'override' && $AD->isAdmin()){ ?>
	<tr><td colspan="2"><input type="submit" value="Override" /></td></tr>
<?php }else if(isset($_GET['action']) && htmlspecialchars($_GET['action']) == 'close' && $AD->isAdmin()){ ?>
	<tr><td colspan="2"><input type="submit" value="Close room" /></td></tr>
<?php }else{ ?>
	<tr><td colspan="2"><input type="submit" value="Book Now" /></td></tr>
<?php } ?>
</form>
<?php #If editing and users own lesson or is admin user then show Delete button with reason field ?>
<?php if(isset($_GET['action']) && $_GET['action'] == 'edit' && ($AD->isAdmin() || $AD->getUser() == $_GET['teacher'])){ ?>
<form id="delete" action="<?php echo htmlspecialchars($SITE->path); ?>/index.php?page=booking&debug=1" method="post">
<input type="hidden" name="action" id="action" value="dodelete" />
<input type="hidden" name="ret" id="ret" value="<?php echo base64_encode('page=home&date=' . $_GET['date']); ?>" />
<input type="hidden" name="id" id="id" value="<?php echo htmlspecialchars($_GET['id']); ?>" />
<input type="hidden" name="lesson" id="lesson" value="<?php echo htmlspecialchars($_GET['lessoncode']); ?>" />
<input type="hidden" name="room" id="room" value="<?php echo htmlspecialchars($_GET['room']); ?>" />
<input type="hidden" name="date" id="date" value="<?php echo htmlspecialchars($_GET['date']); ?>" />
<input type="hidden" name="day" id="day" value="<?php echo $day ?>" />
<input type="hidden" name="time" id="time" value="<?php echo htmlspecialchars($_GET['time']); ?>" />
<input type="hidden" name="oldbookedfor" id="oldbookedfor" value="<?php echo urldecode($_GET['teacher']); ?>" />
<?php if(isset($_GET['type'])){ ?><input type="hidden" name="type" id="type" value="<?php echo urldecode($_GET['type']); ?>" /><?php } ?>
<input type="hidden" name="reason" id="reason" />
<tr><td>Reason</td><td><input type="text" name="reason" id="reason" /></td></tr>
<tr><td colspan="2"><input type="submit" value="Delete" /></td></tr>
</form>
<?php } ?>
<form action="<?php echo htmlspecialchars($SITE->path); ?>/index.php?page=home" method="post">
<tr><td colspan="2"><input type="submit" value="Cancel" /></td></tr>
</form>
</table>
<div id="loadingspinner">
<img src="<?php echo htmlspecialchars($SITE->path); ?>/theme/gfx/loading.gif" alt="Please wait"/>
</div>
</div>
<?php
$body = ob_get_clean();
?>
