<?php

$classes = [];
$lessons = [];
$bookings = [];
$cover = [];
$rooms = explode(',', $SITE->rooms);
$periods = explode(',', $SITE->periods);

$dates = '';
if(wincache_ucache_exists('bookingdatesdat'))
{
	$dates = wincache_ucache_get('bookingdatesdat');
}else{
	$dates = unserialize(file_get_contents('dates.dat'));
	wincache_ucache_set('bookingdatesdat', $dates);
}

$dateRange = array_keys($dates);

$minDate = date('d/m/Y', strtotime($dateRange[0]));
$maxDate = date('d/m/Y', strtotime($dateRange[sizeof($dateRange) - 1]));

unset($dateRange);

$date = isset($_POST['date']) ? $date = $_POST['date'] : (isset($_GET['date']) ? $_GET['date'] : date('d/m/Y'));
$dayindex = date('Y-m-d', strtotime(str_replace('/', '-', htmlspecialchars($date))));
$day = substr(date('D', strtotime(str_replace('/', '-', htmlspecialchars($date)))), 0, 2) . (isset($dates[$dayindex]) ? $dates[$dayindex] : "");
unset($date, $dateindex);

if(isset($_GET['page']) && (htmlspecialchars($_GET['page']) == 'home'))
{
	if(!isset($_GET['date']))
	{
		header('Location: ' . $SITE->path . '/index.php?page=home&date=' . date('d/m/Y') . '');
		exit();
	}

	$ttquery = "SELECT * FROM dbo.TTLessons WHERE TT_Period LIKE '" . $day . "%';";
	$bkquery = "SELECT * FROM dbo.BKLessons WHERE BK_Period LIKE '" . $day . "%' AND BK_Date = '" . htmlspecialchars($_GET['date']) . "' AND BK_Type != '6' AND BK_Type != '7';";
	$recbkquery = "SELECT * FROM dbo.BKLessons WHERE BK_Type = '6' OR BK_Type = '7';";

	if(wincache_ucache_exists('bookingtimetable' . htmlspecialchars($_GET['date'])))
	{
		$lessons = unserialize(wincache_ucache_get('bookingtimetable' . htmlspecialchars($_GET['date'])));
	}else{
		$tt = $SQL->query($ttquery);
		
		while($period = sqlsrv_fetch_array($tt, SQLSRV_FETCH_ASSOC))
		{
			if(!isset($lessons[$period['TT_Room'] . $period['TT_Period']]))
			{
				$lessons[$period['TT_Room'] . $period['TT_Period']] = new Lesson($period['TT_ID'], $period['TT_ClassName'], $period['TT_Teacher'], $period['TT_Period'], $period['TT_Room']);
			}else{
				$existingLesson = $lessons[$period['TT_Room'] . $period['TT_Period']];
				/*if($existingLesson !== new Lesson($period['TT_ID'], $period['TT_ClassName'], $period['TT_Teacher'], $period['TT_Period'], $period['TT_Room']))*/
				{
					if($existingLesson->getClassName() !== $period['TT_ClassName'])/* && $existingLesson->getRoom() === $period['TT_Room'] && $existingLesson->getPeriod() === $period['TT_Period'])*/
					{
						$existingLesson->setClassName($existingLesson->getClassName() . " " . $period['TT_ClassName']);
						if($existingLesson->getTeacher() !== $period['TT_Teacher'])
						{
							$existingLesson->setTeacher($existingLesson->getTeacher() . " " . $period['TT_Teacher']);
						}
					}
				}
			}
		}
		
		wincache_ucache_set('bookingtimetable' . htmlspecialchars($_GET['date']), serialize($lessons));
		
		sqlsrv_free_stmt($tt);
	}
	
	$bk = $SQL->query($bkquery);
	
	while($period = sqlsrv_fetch_array($bk, SQLSRV_FETCH_ASSOC))
	{
		$bookings[$period['BK_Room'] . $period['BK_Period']] = new Booking($period['BK_ID'], $period['BK_ClassName'], $period['BK_Teacher'], $period['BK_Period'], $period['BK_Room'], $period['BK_Date'], $period['BK_BookedBy'], $period['BK_BookedTime'], $period['BK_Type']);
	}
	
	sqlsrv_free_stmt($bk);
	
	$recbk = $SQL->query($recbkquery);
	
	while($period = sqlsrv_fetch_array($recbk, SQLSRV_FETCH_ASSOC))
	{
		#Weekly
		if($period['BK_Type'] == '6' && (strpos($period['BK_Period'], substr($day, 0, 2)) !== false))
		{
			$bookings[$period['BK_Room'] . $period['BK_Period']] = new Booking($period['BK_ID'], $period['BK_ClassName'], $period['BK_Teacher'], $period['BK_Period'], $period['BK_Room'], $period['BK_Date'], $period['BK_BookedBy'], $period['BK_BookedTime'], $period['BK_Type']);
		}
		#Fornightly
		if($period['BK_Type'] == '7' && (strpos($period['BK_Period'], $day) !== false))
		{
			$bookings[$period['BK_Room'] . $period['BK_Period']] = new Booking($period['BK_ID'], $period['BK_ClassName'], $period['BK_Teacher'], $period['BK_Period'], $period['BK_Room'], $period['BK_Date'], $period['BK_BookedBy'], $period['BK_BookedTime'], $period['BK_Type']);
		}
	}
	
	sqlsrv_free_stmt($recbk);

	unset($ttquery, $bkquery, $recbkquery, $bk, $recbk, $period, $existingLesson);
}else{
	
}

?>
