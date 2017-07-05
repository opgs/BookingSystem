<?php
if(!$AD->isAdmin())
{
	header('Location: ' . $SITE->path . '/index.php?page=home');
	exit();
}

ob_start();

if(isset($_POST['action']) && htmlspecialchars($_POST['action']) == 'book')
{	
	$randomID = rand(100, 999);

	$date = str_replace('/', '-', $_POST['fromDate']);

	$end_date = str_replace('/', '-', $_POST['toDate']);
	 
	while(strtotime($date) <= strtotime($end_date))
	{
		if(!(date('N', strtotime($date)) >= 6)) //Weekends
		{
			$thisday = substr(date('D', strtotime(str_replace('/', '-', htmlspecialchars($date)))), 0, 2) . $dates[date('Y-m-d', strtotime(str_replace('/', '-', htmlspecialchars($date))))];
			$thisdate = date('d/m/Y', strtotime($date));
		
			$query = 'INSERT INTO dbo.BKLessons (BK_Room, BK_Period, BK_ClassName, BK_Teacher, BK_NoOfStudents, BK_Date, BK_BookedBy, BK_BookedTime, BK_Type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);';
	
			$tt = $SQL->query($query, array($_POST['room'], $thisday . ':' . $_POST['period'], $_POST['lesson'], $_POST['bookedfor'], 0, $thisdate, $AD->getUser(), date('d/m/Y H:i'), '5:' . $randomID));
			
			if($tt !== false)
			{
				echo sqlsrv_num_rows($tt);
			}else{
				echo "FAIL!!!!";
				print_r(sqlsrv_errors());
			}
			
			echo $query;
		}
		$date = date('Y-m-d', strtotime("+1 day", strtotime($date)));
	}
	
	unset($query, $tt);
	
	addLog('Added a block booking ' . $_POST['lesson'] . ' at ' . $_POST['date'] . ' in ' . $_POST['room'] . ':' . $_POST['time'], 1);
	
	header('Location: ' . $SITE->path . '/index.php?page=admin-blockbooking');
	exit();
}
if(isset($_GET['action']) && htmlspecialchars($_GET['action']) == 'delete')
{
	$query = 'DELETE FROM dbo.BKLessons WHERE BK_Type=? AND BK_BookedTime=?;';
	
	$tt = $SQL->query($query, array($_GET['id'], $_GET['date']));
	
	if($tt !== false)
	{
		echo sqlsrv_num_rows($tt);
	}else{
		echo "FAIL!!!!";
		print_r(sqlsrv_errors());
	}
	
	echo "<br />" . $query . "<br />";
	
	echo "done";

	unset($query, $tt);
	
	addLog('Deleted a block booking from ' . $_GET['sdate'] . ' to ' . $_GET['edate'], 3);
	
	header('Location: ' . $SITE->path . '/index.php?page=admin-blockbooking');
	exit();
}
?>

<?php include('adminmenu.php'); ?>

<script type="text/javascript">
$(document).ready(function(){
	$("#fromDate").datepicker({dateFormat: 'dd/mm/yy', beforeShowDay: $.datepicker.noWeekends, minDate: '<?php echo $minDate; ?>', maxDate: '<?php echo $maxDate; ?>'});
	$("#toDate").datepicker({dateFormat: 'dd/mm/yy', beforeShowDay: $.datepicker.noWeekends, minDate: '<?php echo $minDate; ?>', maxDate: '<?php echo $maxDate; ?>'});
});
</script>

<div id="bookingContent">
<form action="<?php echo htmlspecialchars($SITE->path); ?>/index.php?page=admin-blockbooking&debug=1&date=<?php echo date('d/m/Y'); ?>" method="post">
<input type="hidden" name="action" id="action" value="book" />
<table>
<tr><td>Room</td><td><select name="room" id="room">
<?php
foreach($rooms as $room)
{
	$roomname = $room;
	if(isset($lang['room-' . strtolower($roomname)]))
	{
		$roomname = $lang['room-' . strtolower($roomname)];
	}
	echo '<option value="' . $room . '">' . $roomname . '</option>';
}
?>
</select></td></tr>
<tr><td>Period</td><td><select name="period" id="period">
<?php
foreach($periods as $period)
{
	echo '<option value="' . $period . '">' . $period . '</option>';
}
?>
</select></td></tr>
<tr><td>From date</td><td><input type="text" name="fromDate" id="fromDate" /></td></tr>
<tr><td>To date</td><td><input type="text" name="toDate" id="toDate" /></td></tr>
<tr><td>Lesson Description</td><td><input type="text" name="lesson" id="lesson" /></td></tr>
<tr><td>Booked for</td><td><input type="text" name="bookedfor" autocomplete="off" id="bookedfor" value="<?php echo $AD->getUser(); ?>"/></td></tr>
<tr><td colspan="2"><input type="submit" value="Book Now" /></td></tr>
</table>
</form>
<form action="<?php echo htmlspecialchars($SITE->path); ?>/index.php" method="get">
<input type="hidden" name="page" id="page" value="admin-summary" />
<input type="submit" value="Cancel" />
</form>
</div>

<?php

$bk = $SQL->query("SELECT * FROM dbo.BKLessons WHERE BK_Type LIKE '5:%'");

$blockbookings = [];

while($period = sqlsrv_fetch_array($bk, SQLSRV_FETCH_ASSOC))
{
	$blockbookings[$period['BK_Room'] . $period['BK_Period']] = new BlockBooking($period['BK_ID'], $period['BK_ClassName'], $period['BK_Teacher'], $period['BK_Period'], $period['BK_Room'], $period['BK_NoOfStudents'], $period['BK_Date'], $period['BK_BookedBy'], $period['BK_BookedTime'], $period['BK_Type']);
}

$blocks = [];

foreach($blockbookings as $blockbooking)
{
	if(!isset($blocks[$blockbooking->getType()]))
	{
		$blocks[$blockbooking->getType()] = $blockbooking;
	}else{
		$blocks[$blockbooking->getType()]->setEndDate($blockbooking->getDate());
	}
}

?>

<table>
<thead><th>Classname</th><th>Booked for</th><th colspan="3">Date Range</th><th></th></thead>
<?php
foreach($blocks as $blockbooking)
{
	?><tr><td><?php echo $blockbooking->getClassname(); ?></td><td><?php echo $blockbooking->getTeacher(); ?></td><td><?php echo $blockbooking->getDate(); ?></td><td>-</td><td><?php echo $blockbooking->getEndDate(); ?></td><td><a href="<?php echo $SITE->path; ?>/index.php?page=admin-blockbooking&action=delete&id=<?php echo $blockbooking->getType(); ?>&date=<?php echo $blockbooking->getBookedTime(); ?>&sdate=<?php echo $blockbooking->getDate(); ?>&edate<?php echo $blockbooking->getEndDate(); ?>">Delete</a></td></tr><?php
}
?>
</table>

<?php
$body = ob_get_clean();
?>
