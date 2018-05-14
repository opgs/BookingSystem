<?php

error_reporting(-1);
ini_set('display_errors', 'On');

require('booking.php');

function getCell(&$config, ?Lesson &$lesson, string $period, string $type, string &$room, string $periodcode) : string
{
	$wDate = date('Y-m-d', strtotime(str_replace('/', '-', htmlspecialchars($_GET['date']))));
	$seperator = $config['mobile'] ? "<span class=\"mobile-seperator\">&nbsp</span>" : "<br />";
	$baseURL = '<a href="' . $config['site']->path . '/index.php?page=booking&room=' . urlencode($room) . '&time=' . urlencode($periodcode) . '&date=' . urlencode(htmlspecialchars($_GET['date']));
	$contents = '<td class="';
	if(!isset($config['dates'][$wDate])){
		$contents .= $period . ' weekend slot">Weekend</td>';
	}else if($config['dates'][$wDate] == false){
		$contents .= $period . ' holiday slot">Holiday</td>';
	}else if($type !== 'free'){
		$staffcode = $config['ldap']->getStaffCodeFromId($lesson->getTeacher());
		$fullURL = $baseURL . '&id=' . urlencode($lesson->getId()) . '&teacher=' . urlencode($lesson->getTeacher()) . '&lessoncode=' . urlencode($lesson->getClassName());
		$contents .= $period . ' ' . $type . ' slot"><b>' . $staffcode . '</b>' . $seperator . $lesson->getClassName();
		if($type === 'booked' && ($config['ad']->isAdmin() || $config['ad']->getUser() == $staffcode) && ($config['site']->bookinglock !== 'true'))
		{
			$fullURL .= '&type=' . urlencode($lesson->getType());
			$contents .= $seperator . $fullURL . '&action=edit">Edit</a>';
		}
		if($type === 'timetabled' && $config['ad']->isAdmin())
		{
			$contents .= $seperator . $fullURL . '&action=override">Override</a>';
		}
	}else{
		$contents .= $period . ' free slot">';
		if($config['ad']->isAdmin() || $config['site']->bookinglock !== 'true')
		{
			$contents .= $baseURL . '">Free</a>';
		}else{
			$contents .= '&nbsp;';
		}
	}
	return ($config['mobile'] ? "<tr class=\"mobile-period\"><td class=\"mobile-periodcode\">" . $periodcode . "</td>" : "") . $contents . '</td>' . ($config['mobile'] ? "</tr>" : "");
}

if(!isset($body))
{
	ob_start();

	?>
	<span id="date" <?php if($mobile){ ?> class="mobile" <?php } ?>>
	<a id="prevDay" href="<?php echo $SITE->path . '?page=home&date=' . date('d/m/Y', strtotime('-1 day', strtotime(str_replace('/', '-', htmlspecialchars($_GET['date']))))); ?>"><?php 
	if(!$mobile){
		echo '<img src="theme/gfx/prev.jpg" alt="Previous" height="25" />';
	}else{ 
		echo '<img class="mobile-button" src="theme/gfx/P.png" alt="Previous" height="25" />';
	} 
	?></a><span id="datepickerwrapper"><input type="text" id="datepicker" size="12" value="<?php if(isset($_GET['date'])){echo htmlspecialchars($_GET['date']);}else{echo date('d/m/Y');} ?>"></span><a id="today" href="<?php echo $SITE->path . '?page=home&date=' . date('d/m/Y'); ?>"><?php 
	if(!$mobile){
		echo '<img src="theme/gfx/today.jpg" alt="Today" height="25" />';
	}else{
		echo '<img class="mobile-button" src="theme/gfx/T.png" alt="Previous" height="25" />';
	} 
	?></a><a id="nextDay" href="<?php echo $SITE->path . '?page=home&date=' . date('d/m/Y', strtotime('+1 day', strtotime(str_replace('/', '-', htmlspecialchars($_GET['date']))))); ?>"><?php 
	if(!$mobile){
		echo '<img src="theme/gfx/next.jpg" alt="Next" height="25" />';
	}else{
		echo '<img class="mobile-button" src="theme/gfx/n.png" alt="Previous" height="25" />';
	}
	?></a>
	<span id="timetableDay"><?php if(!$mobile){echo 'Timetable day : ';} ?><?php echo $day;?></span>
	</span>
	<script type="text/javascript">
	$(document).ready(function(){
		$("#datepicker").datepicker({dateFormat: 'dd/mm/yy', beforeShowDay: $.datepicker.noWeekends, minDate: '<?php echo $minDate; ?>', maxDate: '<?php echo $maxDate; ?>'}).change(function(){window.location.href = "<?php echo $SITE->path ?>" + "?page=home&date=" + encodeURIComponent(this.value);});
	});
	</script>
	<?php if(!$mobile){
		echo '<table id="timetable"><tr><th></th>';

		foreach($periods as $period)
		{
			$pname = $period;
			if(isset($LANG['period-' . strtolower($pname)]))
			{
				$pname = $LANG['period-' . strtolower($pname)];
			}
			echo '<th>', $pname , '</th>';
		}
	}
	
	if(!$mobile)
	{
		echo '</tr>';
	}else{
		echo '<dl id="timetable" class="accordion mobile">';
	}
	
	$conf = ['site' => $SITE, 'ad' => $AD, 'ldap' => $LDAP, 'dates' => $dates, 'mobile' => $mobile];
	
	foreach($rooms as $room)
	{
		$null = NULL;
		$before = getCell($conf, $null, 'before', 'free', $room, 'Bef');
		$am = getCell($conf, $null, 'amreg', 'free', $room, 'am');
		$p1 = getCell($conf, $null, 'period', 'free', $room, '1');
		$p2 = getCell($conf, $null, 'period', 'free', $room, '2');
		$break = getCell($conf, $null, 'break', 'free', $room, 'Bre');
		$p3 = getCell($conf, $null, 'period', 'free', $room, '3');
		$p4 = getCell($conf, $null, 'period', 'free', $room, '4');
		$lunch = getCell($conf, $null, 'lunch', 'free', $room, 'Lun');
		$pm = getCell($conf, $null, 'pmreg', 'free', $room, 'pm');
		$p5 = getCell($conf, $null, 'period', 'free', $room, '5');
		$after = getCell($conf, $null, 'after', 'free', $room, 'Aft');
		
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
					$before = getCell($conf, $lesson, 'before', 'timetabled', $room, 'Bef');
				}
				if(strpos($lesson->getPeriod(), ':am'))
				{
					$am = getCell($conf, $lesson, 'amreg', 'timetabled', $room, 'am');
				}
				if(strpos($lesson->getPeriod(), ':1'))
				{
					$p1 = getCell($conf, $lesson, 'period', 'timetabled', $room, '1');
				}
				if(strpos($lesson->getPeriod(), ':2'))
				{
					$p2 = getCell($conf, $lesson, 'period', 'timetabled', $room, '2');
				}
				if(strpos($lesson->getPeriod(), ':Bre'))
				{
					$break = getCell($conf, $lesson, 'break', 'timetabled', $room, 'Bre');
				}
				if(strpos($lesson->getPeriod(), ':3'))
				{
					$p3 = getCell($conf, $lesson, 'period', 'timetabled', $room, '3');
				}
				if(strpos($lesson->getPeriod(), ':4'))
				{
					$p4 = getCell($conf, $lesson, 'period', 'timetabled', $room, '4');
				}
				if(strpos($lesson->getPeriod(), ':Lun'))
				{
					$lunch = getCell($conf, $lesson, 'lunch', 'timetabled', $room, 'Lun');
				}
				if(strpos($lesson->getPeriod(), ':pm'))
				{
					$pm = getCell($conf, $lesson, 'pmreg', 'timetabled', $room, 'pm');
				}
				if(strpos($lesson->getPeriod(), ':5'))
				{
					$p5 = getCell($conf, $lesson, 'period', 'timetabled', $room, '5');
				}
				if(strpos($lesson->getPeriod(), ':Aft'))
				{
					$after = getCell($conf, $lesson, 'after', 'timetabled', $room, 'Aft');
				}
			}		
		}
		foreach($bookings as $booking)
		{
			if($booking->getRoom() == $room)
			{
				if(strpos($booking->getPeriod(), ':Bef'))
				{
					$before = getCell($conf, $booking, 'before', 'booked', $room, 'Bef');
				}
				if(strpos($booking->getPeriod(), ':am'))
				{
					$am = getCell($conf, $booking, 'amreg', 'booked', $room, 'am');
				}
				if(strpos($booking->getPeriod(), ':1'))
				{
					$p1 = getCell($conf, $booking, 'period', 'booked', $room, '1');
				}
				if(strpos($booking->getPeriod(), ':2'))
				{
					$p2 = getCell($conf, $booking, 'period', 'booked', $room, '2');
				}
				if(strpos($booking->getPeriod(), ':Bre'))
				{
					$break = getCell($conf, $booking, 'break', 'booked', $room, 'Bre');
				}
				if(strpos($booking->getPeriod(), ':3'))
				{
					$p3 = getCell($conf, $booking, 'period', 'booked', $room, '3');
				}
				if(strpos($booking->getPeriod(), ':4'))
				{
					$p4 = getCell($conf, $booking, 'period', 'booked', $room, '4');
				}
				if(strpos($booking->getPeriod(), ':Lun'))
				{
					$lunch = getCell($conf, $booking, 'lunch', 'booked', $room, 'Lun');
				}
				if(strpos($booking->getPeriod(), ':pm'))
				{
					$pm = getCell($conf, $booking, 'pmreg', 'booked', $room, 'pm');
				}
				if(strpos($booking->getPeriod(), ':5'))
				{
					$p5 = getCell($conf, $booking, 'period', 'booked', $room, '5');
				}
				if(strpos($booking->getPeriod(), ':Aft'))
				{
					$after = getCell($conf, $booking, 'after', 'booked', $room, 'Aft');
				}
				if($booking->getType() == 4)
				{
					$hasClosure = true;
				}
			}
		}
		
		if($AD->isAdmin())
		{
			$closure = (!$mobile ? '<br />' : '<span class=\"mobile-seperator\">&nbsp</span>') . '<a href="' . $SITE->path . '/?page=booking&action=close&date=' . htmlspecialchars($_GET['date']) . '&room=' . $room . '">Close</a>';
			if($hasClosure)
			{
				$closure = (!$mobile ? '<br />' : '<span class=\"mobile-seperator\">&nbsp</span>') . '<a href="' . $SITE->path . '/?page=booking&action=open&date=' . htmlspecialchars($_GET['date']) . '&room=' . $room . '">Open</a>';
			}
		}else{
			$closure = '';
		}
		
		$roomname = $room;
		if(isset($LANG['room-' . strtolower($roomname)]))
		{
			$roomname = $LANG['room-' . strtolower($roomname)];
		}
		
		if(!$mobile)
		{
			echo '<tr><td class="room">' . $roomname . $closure . '</td>' . $before . $am . $p1 . $p2 . $break . $p3 . $p4 . $lunch . $pm . $p5 . $after . '</tr>';
		}else{
			echo '<dt class="accordion-trigger"><a href="#" title="show more" class="closed"><span class="iconText">' . $roomname . '</span><span class="hasIcon arrow">&#x25BE;</span></a></dt>';
			echo '<dd class="accordion-content"><table>';
			echo $before . $am . $p1 . $p2 . $break . $p3 . $p4 . $lunch . $pm . $p5 . $after;
			echo '</table></dd>';
		}
	}
	unset($dayp, $roomname, $room, $yeargroup, $blockedout, $closure, $hasClosure);
	
	if(!$mobile)
	{ 
		echo '</table>';
	}else{ 
		echo '</dl>';
	} 

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
