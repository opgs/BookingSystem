<?php

class Lesson
{
	private $id = 0;
	private $classname = '';
	private $teacher = '';
	private $period = '';
	private $room = '';
	
	function getID() : int {return $this->id;}
	
	function setClassName(string $classnameIn){$this->classname = $classnameIn;}
	function getClassName() : string {return $this->classname;}
	
	function setTeacher(string $teacherIn){$this->teacher = $teacherIn;}
	function getTeacher() : string {return $this->teacher;}
	
	function setPeriod(string $periodIn){$this->period = $periodIn;}
	function getPeriod() : string {return $this->period;}
	
	function setRoom(string $roomIn){$this->room = $roomIn;}
	function getRoom() : string {return $this->room;}
	
	function __construct(int $idIn, string $classnameIn, string $teacherIn, string $periodIn, string $roomIn)
	{
		$this->id = $idIn;
		$this->classname = $classnameIn;
		$this->teacher = $teacherIn;
		$this->period = $periodIn;
		$this->room = $roomIn;
	}
}

class Booking extends Lesson
{
	private $date = '';
	private $bookedby = '';
	private $bookedtime = '';
	private $type = '';
	
	function getDate() : string {return $this->date;}
	
	function getBookedTime() : string {return $this->bookedtime;}
	
	function getType() : string {return $this->type;}
	
	function __construct(int $idIn, string $classnameIn, string $teacherIn, string $periodIn, string $roomIn, string $dateIn, string $bookedByIn, string $bookedTimeIn, string $typeIn)
	{
		parent::__construct($idIn, $classnameIn, $teacherIn, $periodIn, $roomIn);
		$this->date = $dateIn;
		$this->bookedby = $bookedByIn;
		$this->bookedtime = $bookedTimeIn;
		$this->type = $typeIn;
	}
}

class BlockBooking extends Booking
{
	private $endDate = '';
	
	function setDate(string $dateIn){$this->date = $dateIn;}
	
	function getEndDate() : string {return $this->endDate;}
	function setEndDate(string $dateIn){$this->endDate = $dateIn;}
	
	function __construct(int $idIn, string $classnameIn, string $teacherIn, string $periodIn, string $roomIn, string $dateIn, string $bookedByIn, string $bookedTimeIn, string $typeIn)
	{
		parent::__construct($idIn, $classnameIn, $teacherIn, $periodIn, $roomIn, $dateIn, $bookedByIn, $bookedTimeIn, $typeIn);
	}
}

?>
