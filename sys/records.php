<?php

$classes = [];

class Lesson
{
	private $id = 0;
	private $classname = '';
	private $teacher = '';
	private $period = '';
	private $room = 0;
	private $noofstudents = 0;
	
	function getID(){return $this->id;}
	
	function setClassName($classnameIn){$this->classname = $classnameIn;}
	function getClassName(){return $this->classname;}
	
	function setTeacher($teacherIn){$this->teacher = $teacherIn;}
	function getTeacher(){return $this->teacher;}
	
	function setPeriod($periodIn){$this->period = $periodIn;}
	function getPeriod(){return $this->period;}
	
	function setRoom($roomIn){$this->room = $roomIn;}
	function getRoom(){return $this->room;}
	
	function setNoOfStudents($noofstudentsin){$this->noofstudents = $noofstudentsin;}
	function getNoOfStudents(){return $this->noofstudents;}
	
	function __construct($idIn, $classnameIn, $teacherIn, $periodIn, $roomIn, $noofstudentsIn)
	{
		$this->id = $idIn;
		$this->classname = $classnameIn;
		$this->teacher = $teacherIn;
		$this->period = $periodIn;
		$this->room = $roomIn;
		$this->noofstudents = $noofstudentsIn;
	}
}

class Booking extends Lesson
{
	private $date = '';
	private $bookedby = '';
	private $bookedtime = '';
	private $type = '';
	
	function getDate(){return $this->date;}
	
	function getBookedTime(){return $this->bookedtime;}
	
	function getType(){return $this->type;}
	
	function __construct($idIn, $classnameIn, $teacherIn, $periodIn, $roomIn, $noofstudentsIn, $dateIn, $bookedByIn, $bookedTimeIn, $typeIn)
	{
		parent::__construct($idIn, $classnameIn, $teacherIn, $periodIn, $roomIn, $noofstudentsIn);
		$this->date = $dateIn;
		$this->bookedby = $bookedByIn;
		$this->bookedtime = $bookedTimeIn;
		$this->type = $typeIn;
	}
}

class BlockBooking extends Booking
{
	private $endDate = '';
	
	function setDate($dateIn){$this->date = $dateIn;}
	
	function getEndDate(){return $this->endDate;}
	function setEndDate($dateIn){$this->endDate = $dateIn;}
	
	function __construct($idIn, $classnameIn, $teacherIn, $periodIn, $roomIn, $noofstudentsIn, $dateIn, $bookedByIn, $bookedTimeIn, $typeIn)
	{
		parent::__construct($idIn, $classnameIn, $teacherIn, $periodIn, $roomIn, $noofstudentsIn, $dateIn, $bookedByIn, $bookedTimeIn, $typeIn);
	}
}

?>
