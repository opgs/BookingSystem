<?php

wincache_ucache_delete('bookingdatesdat');

$dates = [];
$entries = [];
$startWeek = $SITE->startingweek;

if(!$AD->isAdmin())
{
	header('Location: ' . $SITE->path . '/index.php?page=home');
	exit();
}

ob_start();
?>

<?php include('adminmenu.php'); ?>

<?php
$query = "SELECT * FROM dbo.TermDates ORDER BY StartDate;";

$tt = $SQL->query($query);

if($tt !== false)
{
	echo sqlsrv_num_rows($tt);
}else{
	echo "FAIL!!!!";
	print_r(sqlsrv_errors());
}

while($entry = sqlsrv_fetch_array($tt, SQLSRV_FETCH_ASSOC))
{
	$entries[] = $entry;
}

$date = $entries[0]['StartDate'];
$date = $date->format('Y-m-d');

$end_date = $entries[sizeof($entries) - 1]['EndDate'];
$end_date = $end_date->format('Y-m-d');
 
while(strtotime($date) <= strtotime($end_date))
{
	$date = date('Y-m-d', strtotime("+1 day", strtotime($date)));
	if(!(date('N', strtotime($date)) >= 6)) //Weekends
	{
		$dates[$date] = false;
	}
}

foreach($entries as $range)
{
	if($range['EventType'] == 'Term') //Holidays = false
	{
		$date = $range['StartDate'];
		$date = $date->format('Y-m-d');
		
		$end_date = $range['EndDate'];
		$end_date = $end_date->format('Y-m-d');
		
		while(strtotime($date) <= strtotime($end_date))
		{
			if(isset($dates[$date]))
			{
				$dates[$date] = true; //True = termtime
			}
			$date = date('Y-m-d', strtotime("+1 day", strtotime($date)));
		}
	}			
}

$currentWeek = $startWeek;
$termflip = false; //Alternates week either side of holidays

foreach($dates as $date => $value)
{
	if($value)
	{
		$termflip = false;
		if(date('N', strtotime($date)) < 5) //Mon, Tue, Wed, Thu
		{
			$dates[$date] = $currentWeek;
		}else if(date('N', strtotime($date)) >= 5){ //Fri
			$dates[$date] = $currentWeek;
			$termflip = true; //Prevents a double flip
			if($currentWeek == 1)
			{
				$currentWeek = 2;
			}else{
				$currentWeek = 1;
			}
		}
	}else if(!$termflip){ //If havent already flipped during holidays
		if($currentWeek == 1)
		{
			$currentWeek = 2;
		}else{
			$currentWeek = 1;
		}
		$termflip = true; //Flip done
	}
}

foreach($dates as $date => $value)
{
	if($value)
	{
		echo '<div class="week' . $value . '">' . $date . '</div>';
	}else{
		echo '<div class="holiday">' . $date . '</div>';
	}
}

unset($query, $tt, $entries, $startdate);

addLog('Ran termdates', 5);

unlink('dates.dat');
file_put_contents('dates.dat', serialize($dates));
?>

<?php
$body = ob_get_clean();
?>

