<?php

include_once APPLICATION_PATH.'/models/AttendenceMaster.php';
include_once APPLICATION_PATH.'/models/Attendence.php';
include_once APPLICATION_PATH.'/models/Student.php';
include_once APPLICATION_PATH.'/models/JoinStatus.php';
include_once APPLICATION_PATH.'/models/MasterSection.php';

class Attendence extends Zend_Controller_Request_Http
{
	private $output = array(
		'status' => 0
	);
	
	protected static function getMasterSectionTable()
	{
		return new Api_Model_MasterSection();
	}
	protected static function getStudentTable()
	{
		return new Api_Model_Student();
	}
	protected static function getAttendenceMasterTable()
	{
		return new Api_Model_AttendenceMaster();
	}
	
	protected static function getAttendenceTable()
	{
		return new Api_Model_Attendence();
	}
	
	protected static function getJoinStatusTable()
	{
		return new Api_Model_JoinStatus();
	}
	
	public function postAction()
	{
		try {
			$student_ids = $this->getParam('student_ids');
			$student_ids = json_decode($student_ids, true);
			
			$class_id	 = $this->getParam('class_id');
			$section_id	 = $this->getParam('section_id');
			$teacher_id	 = $this->getParam('teacher_id');
			$subject_id	 = $this->getParam('subject_id');
			$school_id	 = $this->getParam('school_id');
			$attendence_master_id = $this->getParam('attendence_master_id');
			$x_time = $this->getParam('x_time');
			
			if ( !empty( $attendence_master_id) )
			{
				if ( ( self::getAttendenceTable()->isToday($attendence_master_id) ) === true )
				{
					if ( (self::getAttendenceMasterTable()->XtimeDiFF($attendence_master_id, $x_time) ) === true)
					{
						foreach ( $student_ids['student_info'] as $student_id )
						{
							if ( $student_id['changed'] == 1 )
							{
								self::getAttendenceTable()->updateRowsAttend( array(
									'attendence_master_id'	=> $attendence_master_id,
									'attend'				=> $student_id['attend'],
									'student_id'			=> $student_id['student_id'],
									'join_status'			=> $student_id['join_status']
								));
							}
							else 
							{
								self::getAttendenceTable()->updateRows( array(
									'attendence_master_id'	=> $attendence_master_id,
									'student_id'			=> $student_id['student_id'],
									'join_status'			=> $student_id['join_status']
								));
							}
						}
						$this->output['status'] = 1;
						$this->output['attendence_master_id'] = $attendence_master_id;
					}
					else 
					{
						$lastId = self::getAttendenceMasterTable()->save( array(
							'class_id'		=> $class_id,
							'section_id'	=> $section_id,
							'teacher_id'	=> $teacher_id,
							'school_id'		=> $school_id,
							'subject_id'	=> $subject_id
						));
						
						foreach ( $student_ids['student_info'] as $student_id )
						{
							self::getAttendenceTable()->save( array(
								'student_id'			=> $student_id['student_id'],
								'attendence_master_id'	=> $lastId,
								'attend'				=> $student_id['attend'],
								'join_status'			=> $student_id['join_status']
							));
						}
						
						$this->output['attendence_master_id'] = $lastId;
					}
					
					$this->output['status'] = 1;
				}
				else 
				{
					foreach ( $student_ids['student_info'] as $student_id )
					{
						if ( $student_id['changed'] == 1)
						{
							self::getAttendenceTable()->updateRowsAttend( array(
								'attendence_master_id'		=> $attendence_master_id,
								'attend'					=> $student_id['attend'],
								'student_id'				=> $student_id['student_id'],
								'join_status'				=> $student_id['join_status']
							));
						}
						else 
						{
							self::getAttendenceTable()->updateRows( array(
								'attendence_master_id'	=> $attendence_master_id,
								'student_id'			=> $student_id['student_id'],
								'join_status'			=> $student_id['join_status']
							));
						}
					}
					
					$this->output['status'] = 1;
					$this->output['attendence_master_id'] = $attendence_master_id;
				}
			} // CLOSE UPDATE
			else 
			{
				$oldId = self::getAttendenceMasterTable()
				->getOldId($teacher_id, $school_id, $class_id, $section_id, $subject_id);
				
				$updateEntry = false;
				
				if ( $oldId )
				{
					if ( ( self::getAttendenceTable()->isToday($oldId) ) === true )
					{
					
//						if ( ( self::getAttendenceMasterTable()->XtimeDiFF($oldId, $x_time) ) === true)
                      //Implement my amar kumar bhanu for Attendance issue Start
						if ( ( self::getAttendenceMasterTable()->XtimeDiFFExtra($oldId, $x_time) ) != "")

						{
							$updateEntry = true;
						}
						/////End
						
					}
				}
				
				if ( $updateEntry === true )
				{
					foreach ( $student_ids['student_info'] as $student_id )
					{
						if ( $student_id['changed'] == 1)
						{
							self::getAttendenceTable()->updateRowsAttend( array(
								'attendence_master_id'	=> $oldId,
								'attend'				=> $student_id['attend'],
								'student_id'			=> $student_id['student_id'],
								'join_status'			=> $student_id['join_status']
							));
						}
						else 
						{
							self::getAttendenceTable()->updateRows( array(
								'attendence_master_id'	=> $oldId,
								'student_id'			=> $student_id['student_id'],
								'join_status'			=> $student_id['join_status']
							));
						}
					}
					$this->output['status'] = 1;
					$this->output['attendence_master_id'] = $oldId;
				}
				else 
				{
					$lastId = self::getAttendenceMasterTable()->save( array(
						'class_id'		=> $class_id,
						'section_id'	=> $section_id,
						'teacher_id'	=> $teacher_id,
						'school_id'		=> $school_id,
						'subject_id'	=> $subject_id
					));
					
					foreach ( $student_ids['student_info'] as $student_id )
					{
						self::getAttendenceTable()->save( array(
							'student_id'			=> $student_id['student_id'],
							'attendence_master_id'	=> $lastId,
							'attend'				=> $student_id['attend'],
							'join_status'			=> $student_id['join_status']
						));
					}
					
					$this->output['status'] = 1;
					$this->output['attendence_master_id'] = $lastId;
				}
			}
		}
		catch (Exception $e)
		{
			$this->output['status'] = 0;
			$this->output['message'] = $e->getMessage();
		}
		
		$response = new Response();
			$response->getResponse()
			->setHttpResponseCode(200)
			->setHeader( 'Content-Type', 'application/json' )
			->appendBody( json_encode( $this->output ) );
	}
	
	public function getAction()
	{
		try {
			
			$school_id 	= $this->getParam('school_id');
			$teacher_id = $this->getParam('teacher_id');
			$class_id 	= $this->getParam('class_id');
			$section_id = $this->getParam('section_id');
			$subject_id = $this->getParam('subject_id');
			$x_time 	= $this->getParam('x_time');
			
			if ( empty($school_id) || empty($class_id) || empty($section_id) || empty($subject_id) || empty($x_time) || empty($teacher_id))
			{
				throw new Exception('Please provide all parameters');
			}
			
			$student_list = self::getStudentTable()->getStudentList($school_id, $class_id, $section_id);
			
			if ( sizeof( $student_list) )
			{
				$attendence_master_id = (int)self::getAttendenceMasterTable()
				->XtimeDiFFbyDate($class_id, $section_id, $teacher_id, $subject_id, $x_time);
			
				$mid = self::getMasterSectionTable()->get($class_id, $section_id, $school_id);
				
				foreach ( $student_list as $student )
				{	
					$sids[] = $student['sid'];
				}
				
				$names = self::getStudentTable()->getFnameLname($sids);
				$joined  = self::getJoinStatusTable()->getStatusArray($sids);
				$attends = array();
				
				if ( $attendence_master_id)
				{
					$attends = self::getAttendenceTable()->getByAttendenceMasterIdAndStudentIdArray($attendence_master_id, $sids);
				}
				//echo $attendence_master_id;
				//print_r($attends);
				$this->output['status'] = 1;
				foreach ( $names as $name )
				{	
					$this->output['contents'][] = array(
					 'student_id' 			=> $name['id'],
					 'student_fname'		=> $name['fname'],
					 'student_lname'		=> $name['lname'],
					 'joined'				=> (int)self::getJoinStatusStudent($joined, $name['id']),
					 'attend'				=> (int)self::getAttendStudent($attends, $name['id']),
					 'attendence_master_id'	=> $attendence_master_id,
					 'master_section_id'	=> $mid,
					 'roll_no'				=> (int)$name['roll_no']
					);
				}
				
				
				
			}
			
		}
		catch (Exception $e)
		{
			$this->output['status'] = 0;
			$this->output['message'] = $e->getMessage();
		}
		
		$response = new Response();
			$response->getResponse()
			->setHttpResponseCode(200)
			->setHeader( 'Content-Type', 'application/json' )
			->appendBody( json_encode( $this->output ) );
	}
	
	public function indexAction()
	{
		try {
			$x_time    = $this->getParam('x_time');
			$class_id  = $this->getParam('class_id');
			$section_id= $this->getParam('section_id');
			$subject_id= $this->getParam('subject_id');
			$teacher_id= $this->getParam('teacher_id');
			
			if ( !self::getAttendenceMasterTable()->XtimeDiFFbyDate($class_id, $section_id, $teacher_id, $subject_id, $x_time) )
			{
				$this->output['status'] = 1;
			}
			else {
				throw new Exception('You cannot take attendance');
			}
		}
		catch (Exception $e)
		{
			$this->output['status'] = 0;
			$this->output['message'] = $e->getMessage();
		}
		
		$response = new Response();
			$response->getResponse()
			->setHttpResponseCode(200)
			->setHeader( 'Content-Type', 'application/json' )
			->appendBody( json_encode( $this->output ) );
	}
	
	protected static function getAttendStudent($a, $s)
	{
		foreach ( $a as $l )
		{
			if ( isset($l['student_id']) && $l['student_id'] == $s  )
			{
				return $l['attend'];
				break;
			}
		}
	}
	protected static function getJoinStatusStudent( $j, $s)
	{
		foreach ( $j as $k )
		{
			if ( isset($k['student_id']) && $k['student_id'] == $s)
			{
				return $k['joined'];
				break;
			}
		}
	}
	public function putAction()
	{
		try {
			$student_ids = $this->_getParam('student_ids', 0);
			$student_ids = json_encode($student_ids, true);
			$class_id 	 = $this->getParam('class_id');
			$section_id  = $this->getParam('section_id');
			$teacher_id  = $this->getParam('teacher_id');
			$school_id   = $this->getParam('school_id');
			$subject_id  = $this->getParam('subject_id');
			$attendence_master_id = $this->getParam('attendence_master_id');
			$x_time = $this->getParam('x_time');
			
			
			if ( !empty( $attendence_master_id) )
			{
				if ( sizeof( $student_ids) >= 1)
				{
					foreach ( $student_ids['student_info'] as $student_id )
					{
						self::getAttendenceTable()->updateRowsPut( array(
							'attendence_master_id'	=> $attendence_master_id,
							'attend'				=> $student_id['attend'],
							'student_id'			=> $student_id['student_id']
						));
					}
					$this->output['status'] = 1;
					$this->output['message'] = $attendence_master_id;
				}
			}
			else 
			{
				$this->output['status'] = 0;
				$this->output['message'] = 'Please provide attendence_master_id';
			}
			
		}
		catch (Exception $e)
		{
			$this->output['status'] = 0;
			$this->output['message'] = $e->getMessage();
		}
		$response = new Response();
			$response->getResponse()
			->setHttpResponseCode(200)
			->setHeader( 'Content-Type', 'application/json' )
			->appendBody( json_encode( $this->output ) );
	}
}