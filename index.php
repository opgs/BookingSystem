<?php

error_reporting(-1);
ini_set('display_errors', 'On');

require('booking.php');

function getCell($lesson, $period, $type, $room, $periodcode)
{
	global $SITE, $AD, $LDAP, $dates, $day;
	$wDate = date('Y-m-d', strtotime(str_replace('/', '-', htmlspecialchars($_GET['date']))));
	if(!isset($dates[$wDate])){
		$contents = '<td class="' . $period . ' weekend">Weekend</td>';
	}else if($dates[$wDate] == false){
		$contents = '<td class="' . $period . ' holiday">Holiday</td>';
	}else if($type !== 'free'){
		$contents = '<td class="' . $period . ' ' . $type . '"><b>' . $LDAP->getStaffCodeFromId($lesson->getTeacher()) . '</b><br />' . $lesson->getClassName();
		if($type === 'booked' && ($AD->isAdmin() || $AD->getUser() == $LDAP->getStaffCodeFromId($lesson->getTeacher())) && ($SITE->bookinglock !== 'true'))
		{
			$contents .= '<br /><a href="' . $SITE->path . '/index.php?page=booking&id=' . urlencode($lesson->getId()) . '&room=' . urlencode($room) . '&time=' . urlencode($periodcode) . '&date=' . urlencode(htmlspecialchars($_GET['date'])) . '&teacher=' . urlencode($lesson->getTeacher()) . '&lessoncode=' . urlencode($lesson->getClassName()) . '&nos=' . urlencode($lesson->getNoOfStudents()) . '&type=' . urlencode($lesson->getType()) . '&action=edit">Edit</a>';
		}
		if($type === 'timetabled' && $AD->isAdmin())
		{
			$contents .= '<br /><a href="' . $SITE->path . '/index.php?page=booking&id=' . urlencode($lesson->getId()) . '&room=' . urlencode($room) . '&time=' . urlencode($periodcode) . '&date=' . urlencode(htmlspecialchars($_GET['date'])) . '&teacher=' . urlencode($lesson->getTeacher()) . '&lessoncode=' . urlencode($lesson->getClassName()) . '&nos=' . urlencode($lesson->getNoOfStudents()) . '&action=override">Override</a>';
		}
	}else{
		$contents = '<td class="' . $period . ' free">';
		if($AD->isAdmin() || $SITE->bookinglock !== 'true')
		{
			$contents .= '<a href="' . $SITE->path . '/index.php?page=booking&room=' . urlencode($room) . '&time=' . urlencode($periodcode) . '&date=' . urlencode(htmlspecialchars($_GET['date'])) . '">Free</a>';
		}
	}
	return $contents . '</td>';
}

if(!isset($body))
{
	ob_start();

	?>
	<span id="date">
	<a id="prevDay" href="<?php echo $SITE->path . '?page=home&date=' . date('d/m/Y', strtotime('-1 day', strtotime(str_replace('/', '-', htmlspecialchars($_GET['date']))))); ?>"><img src="theme/gfx/prev.jpg" alt="Previous" height="25" /></a>
	<input type="text" id="datepicker" size="12" value="<?php if(isset($_GET['date'])){echo htmlspecialchars($_GET['date']);}else{echo date('d/m/Y');} ?>">
	<a id="today" href="<?php echo $SITE->path . '?page=home&date=' . date('d/m/Y'); ?>"><img src="theme/gfx/today.jpg" alt="Today" height="25" /></a>
	<a id="nextDay" href="<?php echo $SITE->path . '?page=home&date=' . date('d/m/Y', strtotime('+1 day', strtotime(str_replace('/', '-', htmlspecialchars($_GET['date']))))); ?>"><img src="theme/gfx/next.jpg" alt="Next" height="25" /></a>
	</span>
	<script type="text/javascript">
	$(document).ready(function(){
		$("#datepicker").datepicker({dateFormat: 'dd/mm/yy', beforeShowDay: $.datepicker.noWeekends, minDate: '<?php echo $minDate; ?>', maxDate: '<?php echo $maxDate; ?>'}).change(function(){window.location.href = "<?php echo $SITE->path ?>" + "?page=home&date=" + encodeURIComponent(this.value);});
	});
	</script>
	<span id="timetableDay">Timetable day : <?php echo $day;?></span>
	<table id="timetable">
	<tr>
	<th></th>
	<?php
	
	foreach($periods as $period)
	{
		$pname = $period;
		if(isset($LANG['period-' . strtolower($pname)]))
		{
			$pname = $LANG['period-' . strtolower($pname)];
		}
		echo '<th>' . $pname . '</th>';
	}
	
	?>
	</tr>
	<?php
	$periodtypes = ['before','amreg','period','period','break','period','period','lunch','pmreg','period','after'];
	
	foreach($rooms as $room)
	{
		$dayp = new stdClass();
		for($p = 0; $p < sizeof($periods); $p++)
		{
			//$dayp->$periods[$p] = getCell(null, $periodtypes[$p], 'free', $room, substr($periods[$p], 1));
		}
		$before = getCell(null, 'before', 'free', $room, 'Bef');
		$am = getCell(null, 'amreg', 'free', $room, 'am');
		$p1 = getCell(null, 'period', 'free', $room, '1');
		$p2 = getCell(null, 'period', 'free', $room, '2');
		$break = getCell(null, 'break', 'free', $room, 'Bre');
		$p3 = getCell(null, 'period', 'free', $room, '3');
		$p4 = getCell(null, 'period', 'free', $room, '4');
		$lunch = getCell(null, 'lunch', 'free', $room, 'Lun');
		$pm = getCell(null, 'pmreg', 'free', $room, 'pm');
		$p5 = getCell(null, 'period', 'free', $room, '5');
		$after = getCell(null, 'after', 'free', $room, 'Aft');
		
		$hasClosure = false;
		
		foreach($lessons as $lesson)
		{
			$yearGroup = substr($lesson->getClassName(), 0, 2);
			$blockedOut = false;
			
			if(isset($SITE->yearblocks['y' . $yearGroup . 's']) && isset($SITE->yearblocks['y' . $yearGroup . 'e']))
			{
				$blockStart = strtotime(str_replace('/', '-', $SITE->yearblocks['y' . $yearGroup . 's']));
				$blockEnd = strtotime(str_replace('/', '-', $SITE->yearblocks['y' . $yearGroup . 'e']));
				
				$currentTime = strtotime(str_replace('/', '-', htmlspecialchars($_GET['date'])));
				
				if($currentTime >= $blockStart && $currentTime <= $blockEnd)
				{
					$blockedOut = true;
				}
			}
			
			if($lesson->getRoom() == $room && !$blockedOut)
			{
				if(strpos($lesson->getPeriod(), ':Bef'))
				{
					$before = getCell($lesson, 'before', 'timetabled', $room, 'Bef');
				}
				if(strpos($lesson->getPeriod(), ':am'))
				{
					$am = getCell($lesson, 'amreg', 'timetabled', $room, 'am');
				}
				if(strpos($lesson->getPeriod(), ':1'))
				{
					$p1 = getCell($lesson, 'period', 'timetabled', $room, '1');
				}
				if(strpos($lesson->getPeriod(), ':2'))
				{
					$p2 = getCell($lesson, 'period', 'timetabled', $room, '2');
				}
				if(strpos($lesson->getPeriod(), ':Bre'))
				{
					$break = getCell($lesson, 'break', 'timetabled', $room, 'Bre');
				}
				if(strpos($lesson->getPeriod(), ':3'))
				{
					$p3 = getCell($lesson, 'period', 'timetabled', $room, '3');
				}
				if(strpos($lesson->getPeriod(), ':4'))
				{
					$p4 = getCell($lesson, 'period', 'timetabled', $room, '4');
				}
				if(strpos($lesson->getPeriod(), ':Lun'))
				{
					$lunch = getCell($lesson, 'lunch', 'timetabled', $room, 'Lun');
				}
				if(strpos($lesson->getPeriod(), ':pm'))
				{
					$pm = getCell($lesson, 'pmreg', 'timetabled', $room, 'pm');
				}
				if(strpos($lesson->getPeriod(), ':5'))
				{
					$p5 = getCell($lesson, 'period', 'timetabled', $room, '5');
				}
				if(strpos($lesson->getPeriod(), ':Aft'))
				{
					$after = getCell($lesson, 'after', 'timetabled', $room, 'Aft');
				}
			}		
		}
		foreach($bookings as $booking)
		{
			if($booking->getRoom() == $room)
			{
				if(strpos($booking->getPeriod(), ':Bef'))
				{
					$before = getCell($booking, 'before', 'booked', $room, 'Bef');
				}
				if(strpos($booking->getPeriod(), ':am'))
				{
					$am = getCell($booking, 'amreg', 'booked', $room, 'am');
				}
				if(strpos($booking->getPeriod(), ':1'))
				{
					$p1 = getCell($booking, 'period', 'booked', $room, '1');
				}
				if(strpos($booking->getPeriod(), ':2'))
				{
					$p2 = getCell($booking, 'period', 'booked', $room, '2');
				}
				if(strpos($booking->getPeriod(), ':Bre'))
				{
					$break = getCell($booking, 'break', 'booked', $room, 'Bre');
				}
				if(strpos($booking->getPeriod(), ':3'))
				{
					$p3 = getCell($booking, 'period', 'booked', $room, '3');
				}
				if(strpos($booking->getPeriod(), ':4'))
				{
					$p4 = getCell($booking, 'period', 'booked', $room, '4');
				}
				if(strpos($booking->getPeriod(), ':Lun'))
				{
					$lunch = getCell($booking, 'lunch', 'booked', $room, 'Lun');
				}
				if(strpos($booking->getPeriod(), ':pm'))
				{
					$pm = getCell($booking, 'pmreg', 'booked', $room, 'pm');
				}
				if(strpos($booking->getPeriod(), ':5'))
				{
					$p5 = getCell($booking, 'period', 'booked', $room, '5');
				}
				if(strpos($booking->getPeriod(), ':Aft'))
				{
					$after = getCell($booking, 'after', 'booked', $room, 'Aft');
				}
				if($booking->getType() == 4)
				{
					$hasClosure = true;
				}
			}
		}
		
		if($AD->isAdmin())
		{
			$closure = '<br /><a href="' . $SITE->path . '/?page=booking&action=close&date=' . htmlspecialchars($_GET['date']) . '&room=' . $room . '">Close</a>';
			if($hasClosure)
			{
				$closure = '<br /><a href="' . $SITE->path . '/?page=booking&action=open&date=' . htmlspecialchars($_GET['date']) . '&room=' . $room . '">Open</a>';
			}
		}else{
			$closure = '';
		}
		
		$roomname = $room;
		if(isset($LANG['room-' . strtolower($roomname)]))
		{
			$roomname = $LANG['room-' . strtolower($roomname)];
		}
		
		echo '<tr><td class="room">' . $roomname. $closure . '</td>' . $before . $am . $p1 . $p2 . $break . $p3 . $p4 . $lunch . $pm . $p5 . $after . '</tr>';
	}
	unset($dayp, $roomname, $room, $yeargroup, $blockedout, $closure, $hasClosure);
	?>
	</table>	
	<?php

	if(isset($_GET['debug']))
	{
		echo '<pre>';
		var_dump($bookings);
		echo '</pre>';
	}

	$body = ob_get_clean();
}

if(!isset($noheader)){echo $header;}
if(!isset($nobody)){echo $body;}
if(isset($_GET['debug']) && htmlspecialchars($_GET['debug']) == '1'){echo $debug;}
if(!isset($nofooter)){echo $footer;}

?>
