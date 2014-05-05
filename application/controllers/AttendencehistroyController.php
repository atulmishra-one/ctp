<?php
include_once APPLICATION_PATH.'/models/Student.php';
include_once APPLICATION_PATH.'/models/Staff.php';
include_once APPLICATION_PATH.'/models/AttendenceMaster.php';
include_once APPLICATION_PATH.'/models/Attendence.php';
include_once APPLICATION_PATH.'/models/GetClass.php';
include_once APPLICATION_PATH.'/models/GetSection.php';
include_once APPLICATION_PATH.'/models/GetSubject.php';

class Attendencehistroy extends Zend_Controller_Request_Http
{
	private $output = array(
		'status' => 0
	);
	
	protected static function getAttendenceMasterTable()
	{
		$attendenceMasterTable = new Api_Model_AttendenceMaster();
		return $attendenceMasterTable;
	}
	
	protected static function getAttendenceTable()
	{
		$attendenceTable = new Api_Model_Attendence();
		return $attendenceTable;
	}
	protected static function getClassName( $school_id, $class_id)
	{
		$classTable = new Api_Model_GetClass();
		return $classTable->getName($school_id, $class_id);
	}
	protected static function getSectionName( $school_id, $section_id)
	{
		$sectionTable = new Api_Model_GetSection();
		return $sectionTable->getName($school_id, $section_id);
	}
	protected static function getSubjectName( $subject_id)
	{
		$subjectTable = new Api_Model_GetSubject();
		return $subjectTable->getName($subject_id);
	}
	protected static function getStaffTable()
	{
		$staffTable = new Api_Model_Staff();
		return $staffTable;
	}
	protected static function getStudentTable()
	{
		$studentTable = new Api_Model_Student();
		return $studentTable;
	}
	public function getAction()
	{
		$school_id  = $this->getParam('school_id');
		$user_type  = $this->getParam('user_type');
		$user_id    = $this->getParam('user_id');
		$class_id   = $this->getParam('class_id');
		$section_id = $this->getParam('section_id');
		$subject_id = $this->getParam('subject_id');
		$date 		= $this->getParam('date');
		
		try {
			
		if ( empty( $school_id) or  empty($user_id ) or empty($user_type))
		{
			throw new Exception('Please provide school_id and user_id and user_type');
		}
		
		switch ( $user_type )
		{
			case 'TEACHER':
				if ( self::getStaffTable()->isTeacherValid($user_id) )
				{
					$teacherId = self::getStaffTable()->getId($user_id);
					
					if ( $teacherId )
					{
						if( empty($class_id) && empty($section_id) && empty($subject_id) && empty($date) )
						{
							$aids = self::getAttendenceMasterTable()->getAttendenceRecords( array(
								'teacher_id'	=> $teacherId,
								'school_id'		=> $school_id
							));
							
							if ( sizeof( $aids) >= 1)
							{
								foreach ( $aids as $aid )
								{
									$this->output['status'] = 1;
									$this->output['contents'][] = self::_output( $aid);
								}
							}
						}
						else if( empty($class_id) && empty($section_id) && empty($subject_id) && !empty($date) )
						{
							$aids = self::getAttendenceMasterTable()->getAttendenceRecords( array(
								'teacher_id'	=> $teacherId,
								'school_id'		=> $school_id,
								'date'			=> $date
							));
							
							if ( sizeof( $aids) >= 1)
							{
								foreach ( $aids as $aid )
								{
									$this->output['status'] = 1;
									$this->output['contents'][] = self::_output( $aid);
								}
							}
						}
						else if( empty($class_id) && empty($section_id) && !empty($subject_id) && empty($date) )
						{
							$aids = self::getAttendenceMasterTable()->getAttendenceRecords( array(
								'teacher_id'	=> $teacherId,
								'school_id'		=> $school_id,
								'subject_id'	=> $subject_id
							));
							
							if ( sizeof( $aids) >= 1)
							{
								foreach ( $aids as $aid )
								{
									$this->output['status'] = 1;
									$aid['subject_id']	= '';
									$this->output['contents'][] = self::_output( $aid);
								}
							}
						}
					    else if( empty($class_id) && empty($section_id) && !empty($subject_id) && !empty($date) )
						{	
						}
						else if( empty($class_id) && !empty($section_id) && empty($subject_id) && empty($date) )
						{
						}
						else if( empty($class_id) && !empty($section_id) && empty($subject_id) && !empty($date) )
						{
						}
						else if( empty($class_id) && !empty($section_id) && !empty($subject_id) && empty($date) )
						{
							$aids = self::getAttendenceMasterTable()->getAttendenceRecords( array(
								'teacher_id'	=> $teacherId,
								'school_id'		=> $school_id,
								'section_id'	=> $section_id,
								'subject_id'	=> $subject_id
							));
							
							if ( sizeof( $aids) >= 1)
							{
								foreach ( $aids as $aid )
								{
									$this->output['status'] = 1;
									$aid['section_id'] = '';
									$aid['subject_id'] = '';
									$this->output['contents'][] = self::_output( $aid);
								}
							}
						}
						else if( empty($class_id) && !empty($section_id) && !empty($subject_id) && !empty($date) )
						{
							
						}
						else if( !empty($class_id) and empty($section_id) and empty($subject_id) and empty($date) )
						{
							$aids = self::getAttendenceMasterTable()->getAttendenceRecords( array(
								'teacher_id'	=> $teacherId,
								'school_id'		=> $school_id,
								'class_id'		=> $class_id
							));
							
							if ( sizeof( $aids) >= 1)
							{
								foreach ( $aids as $aid )
								{
									$this->output['status'] = 1;
									$aid['class_id'] = '';
									$this->output['contents'][] = self::_output( $aid);
								}
							}
						}
						else if( !empty($class_id) && empty($section_id) && empty($subject_id) && !empty($date) )
						{
							$aids = self::getAttendenceMasterTable()->getAttendenceRecords( array(
								'teacher_id'	=> $teacherId,
								'school_id'		=> $school_id,
								'class_id'		=> $class_id,
								'date'			=> $date
							));
							
							if ( sizeof( $aids) >= 1)
							{
								foreach ( $aids as $aid )
								{
									$this->output['status'] = 1;
									$aid['class_id'] = '';
									$this->output['contents'][] = self::_output( $aid);
								}
							}
						}
						else if( !empty($class_id) && empty($section_id) && !empty($subject_id) && empty($date) )
						{
							
						}
						else if( !empty($class_id) && empty($section_id) && !empty($subject_id) && !empty($date) )
						{
							
						}
						else if( !empty($class_id) && !empty($section_id) && empty($subject_id) && empty($date) )
						{
							$aids = self::getAttendenceMasterTable()->getAttendenceRecords( array(
								'teacher_id'	=> $teacherId,
								'school_id'		=> $school_id,
								'class_id'		=> $class_id,
								'section_id'	=> $section_id
							));
							
							if ( sizeof( $aids) >= 1)
							{
								foreach ( $aids as $aid )
								{
									$this->output['status'] = 1;
									$aid['class_id'] = '';
									$aid['section_id'] = '';
									$this->output['contents'][] = self::_output( $aid);
								}
							}
						}
						else if( !empty($class_id) && !empty($section_id) && empty($subject_id) && !empty($date) )
						{
							$aids = self::getAttendenceMasterTable()->getAttendenceRecords( array(
								'teacher_id'	=> $teacherId,
								'school_id'		=> $school_id,
								'class_id'		=> $class_id,
								'section_id'	=> $section_id,
								'date'			=> $date
							));
							
							if ( sizeof( $aids) >= 1)
							{
								foreach ( $aids as $aid )
								{
									$this->output['status'] = 1;
									$aid['class_id'] = '';
									$aid['section_id'] = '';
									$this->output['contents'][] = self::_output( $aid);
								}
							}
						}
						else if( !empty($class_id) && !empty($section_id) && !empty($subject_id) && empty($date) )
						{
							$aids = self::getAttendenceMasterTable()->getAttendenceRecords( array(
								'teacher_id'	=> $teacherId,
								'school_id'		=> $school_id,
								'class_id'		=> $class_id,
								'section_id'	=> $section_id,
								'subject_id'	=> $subject_id
							));
							
							if ( sizeof( $aids) >= 1)
							{
								foreach ( $aids as $aid )
								{
									$this->output['status'] = 1;
									$aid['class_id'] = '';
									$aid['section_id'] = '';
									$aid['subject_id'] = '';
									$this->output['contents'][] = self::_output( $aid);
								}
							}
						}
						else if( !empty($class_id) && !empty($section_id) && !empty($subject_id) && !empty($date) )
						{
							$aids = self::getAttendenceMasterTable()->getAttendenceRecords( array(
								'teacher_id'	=> $teacherId,
								'school_id'		=> $school_id,
								'class_id'		=> $class_id,
								'section_id'	=> $section_id,
								'subject_id'	=> $subject_id,
								'date'			=> $date
							));
							
							if ( sizeof( $aids) >= 1)
							{
								foreach ( $aids as $aid )
								{
									$this->output['status'] = 1;
									$aid['class_id'] = '';
									$aid['section_id'] = '';
									$aid['subject_id'] = '';
									$this->output['contents'][] = self::_output( $aid);
								}
							}
						}
					}
					else {
						$this->output['message'] = 'Invalid Teacher';
					}
				}
				else {
					$this->output['message'] = 'Invalid Teacher';
				}
			break;
			
			case 'STUDENT':
				if ( ( self::getStudentTable()->isStudentValid($user_id) ) === true )
				{
					$student_id = self::getStudentTable()->getInfo($user_id, 'id');
					
					if ( $student_id) 
					{
						if( !empty( $class_id) && !empty( $section_id) && empty( $subject_id) && empty( $date) )
						{
							$aids = self::getAttendenceMasterTable()->getAttendenceRecords( array(
								'school_id'		=> $school_id,
								'class_id'		=> $class_id,
								'section_id'	=> $section_id
							));
							
							if ( sizeof( $aids) >= 1)
							{
								foreach ( $aids as $aid )
								{
									$attendence = self::getAttendenceTable()->getAttendStudent($aid['id'], $student_id);
									foreach ( $attendence as $attend )
									{
										$this->output['status'] = 1;
										$this->output['contents'][] = self::_output($aid, $student_id);
									}
									
								}
							}
						}
						else if( !empty( $class_id) && !empty( $section_id) && empty( $subject_id) && !empty( $date) )
						{
							$aids = self::getAttendenceMasterTable()->getAttendenceRecords( array(
								'school_id'		=> $school_id,
								'class_id'		=> $class_id,
								'section_id'	=> $section_id,
								'date'			=> $date
							));
							
							if ( sizeof( $aids) >= 1)
							{
								foreach ( $aids as $aid )
								{
									$attendence = self::getAttendenceTable()->getAttendStudent($aid['id'], $student_id);
									foreach ( $attendence as $attend )
									{
										$this->output['status'] = 1;
										$this->output['contents'][] = self::_output($aid, $student_id);
									}
									
								}
							}
						}
						else if( !empty( $class_id) && !empty( $section_id) && !empty( $subject_id) && empty( $date) )
						{
							$aids = self::getAttendenceMasterTable()->getAttendenceRecords( array(
								'school_id'		=> $school_id,
								'class_id'		=> $class_id,
								'section_id'	=> $section_id,
								'subject_id'	=> $subject_id
							));
							
							if ( sizeof( $aids) >= 1)
							{
								foreach ( $aids as $aid )
								{
									$attendence = self::getAttendenceTable()->getAttendStudent($aid['id'], $student_id);
									foreach ( $attendence as $attend )
									{
										$this->output['status'] = 1;
										$this->output['contents'][] = self::_output($aid, $student_id);
									}
									
								}
							}
						}
					}
					else 
					{
						$this->output['status'] = 0;
						$this->output['message'] = 'Invalid student';
					}
				}
				else 
				{
					$this->output['status'] = 0;
					$this->output['messsage'] = 'Invalid student';
				}
			break;
			
		}// CLOSE SWITCH
		
		}catch (Exception $e)
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
	
	protected static function _output($aid , $student_id = false)
	{
		$attend = 0;
		if ( $student_id )
		{
			$attend = self::getAttendenceTable()
			->getByAttendenceMasterIdAndStudentId( $aid['id'], $student_id);
		}
		return array(
			'attendence_master_id'	=> $aid['id'],
			'class_id'				=> (int)$aid['class_id'],
			'class_name'			=> self::getClassName($aid['school_id'], (int)$aid['class_id']),
			'section_id'			=> (int)$aid['section_id'],
			'section_name'			=> (string)self::getSectionName((int)$aid['school_id'], (int)$aid['section_id']),
			'subject_id'			=> (int)$aid['subject_id'],
			'subject_name'			=> (string)self::getSubjectName((int)$aid['subject_id']),
			'datetime'				=> $aid['date_created'],
			'attend'				=> $attend
		);
	}
}