<?php

include_once APPLICATION_PATH.'/models/ClassSession.php';
include_once APPLICATION_PATH.'/models/NotificationType.php';
include_once APPLICATION_PATH.'/models/Notification.php';
include_once APPLICATION_PATH.'/helpers/Socket.php';
include_once APPLICATION_PATH.'/models/AttendenceMaster.php';
include_once APPLICATION_PATH.'/models/Attendence.php';
include_once APPLICATION_PATH.'/models/JoinStatus.php';
include_once APPLICATION_PATH.'/models/BtnSync.php';
include_once APPLICATION_PATH.'/models/Student.php';

class Checkclasssession extends Zend_Controller_Request_Http
{ 
	private $output = array(
		'status'	=> 0
	);
	
	protected static function getClassSessionTable()
	{
		$classSessionTable = new Api_Model_ClassSession();
		return $classSessionTable;
	}
	
	protected static function getStudentTable()
	{
		return new Api_Model_Student();
	}
	protected static function saveJoinStatus( $sid)
	{
		$joinTable = new Api_Model_JoinStatus();
		$joinTable->save( array(
			'student_id' => $sid,
			'status'	 => 1
		));
	}
	protected static function getAttendenceMasterTable()
	{
		return new Api_Model_AttendenceMaster();
	}
	
	protected static function getAttendenceTable()
	{
		return new Api_Model_Attendence();
	}
	
	protected static function getBtnSyncTable()
	{
		return new Api_Model_BtnSync();
	}
	public function indexAction()
	{
		/**
		 * @description
		 * returns class status STOP||START if request made by student
		 */
		try {
			$school_id  = $this->getParam('school_id');
            $class_id   = $this->getParam('class_id');
        	$section_id = $this->getParam('section_id');
        	$student_id = $this->getParam('student_id');
        	$logged_in  = $this->getParam('logged_in');
        	
        	if ( empty($school_id) || empty($class_id) || empty($section_id) || empty($student_id) )
        	{
        		throw new Exception('Missing parameters');
        	}
        	
        	$current_session = self::getClassSessionTable()->getCurrentClassSession( array(
        		'school_id'		=> $school_id,
        		'class_id'		=> $class_id,
        		'section_id'	=> $section_id
        	));
        	
        	if ( sizeof( $current_session) > 1 )
        	{
        		$attendance_master_id = self::getAttendenceMasterTable()
        		->getCurrentId( $current_session['teacher_id'], $school_id, $class_id, $section_id, $current_session['subject_id']);
        		
        		if ( $attendance_master_id )
        		{
        			if ( (self::getAttendenceTable()->isStudentThere($student_id, $attendance_master_id) ) === true )
        			{
        				self::getAttendenceTable()->updateRowsAttend( array(
        					'student_id'			=> $student_id,
        					'attendence_master_id'	=> $attendance_master_id,
        					'attend'				=> 1,
        					'join_status'			=> 1
        				));
        			}
        			else 
        			{
        				self::getAttendenceTable()->save( array(
        					'student_id'			=> $student_id,
        					'attendence_master_id'	=> $attendance_master_id,
        					'attend'				=> 1
        				));
        			}
        			
        			self::saveJoinStatus($student_id);
        			
        			if ( $logged_in )
        			{
        				self::sendNotificationToTeacher($school_id, $class_id, $section_id, $current_session['teacher_id'], $student_id);
        			}
        			
        			$this->output['status'] = 1;
        			$this->output['class_session_id'] = $current_session['id'];
        			$this->output['mute'] = self::getBtnSyncTable()->getStatus($school_id, $class_id, $section_id, 'MUTE');
        			$this->output['blackout'] = self::getBtnSyncTable()->getStatus($school_id, $class_id, $section_id, 'BLACKOUT');
        			$this->output['lock'] = self::getBtnSyncTable()->getStatus($school_id, $class_id, $section_id, 'LOCK');
        		}
        		else 
        		{
        			throw new Exception('Invalid');
        		}
        	}
        	else 
        	{
        		throw new Exception('Invalid');
        	}
        	
		}
		catch (Exception $e)
		{
			$this->output['status']  = 0;
			$this->output['message'] = $e->getMessage();
		}
		$response = new Response();
			$response->getResponse()
			->setHttpResponseCode(200)
			->setHeader( 'Content-Type', 'application/json' )
			->appendBody( json_encode( $this->output ) );
	}
	
	protected static function hasStarted()
	{
		global $teacher_id, $school_id, $class_id, $section_id, $subject_id;
		
		return self::getClassSessionTable()->hasStarted( array(
			'teacher_id' => $teacher_id,
			'school_id'	 => $school_id,
			'class_id'   => $class_id,
			'section_id' => $section_id,
			'subject_id' => $subject_id
		));
	}
	
	protected static function isCurrentTeacher()
	{
		global $school_id, $teacher_id;
		
		return self::getClassSessionTable()->isCurrentTeacher( array(
			'school_id'		=> $school_id,
			'teacher_id'	=> $teacher_id
		));
	}
	
	protected static function isCurrentClassSectionSubject()
	{
		global $school_id, $class_id, $section_id, $subject_id;
		
		return self::getClassSessionTable()->isCurrentClassSectionSubject( array(
			'school_id' 	=> $school_id,
			'class_id'		=> $class_id,
			'section_id'	=> $section_id,
			'subject_id'	=> $subject_id
		));
	}
	
	protected static function isCurrentClassSection()
	{
		global $school_id, $class_id, $section_id;
		
		return self::getClassSessionTable()->isCurrentClassSection( array(
			'school_id'	 => $school_id,
			'class_id'	 => $class_id,
			'section_id' => $section_id
		));
	}
	
	public function getAction()
	{
		try {
			
			global $teacher_id, $school_id, $class_id, $section_id, $subject_id;
			
			$teacher_id = $this->getParam('teacher_id');
            $school_id  = $this->getParam('school_id');
            $class_id   = $this->getParam('class_id');
            $section_id = $this->getParam('section_id');
            $subject_id = $this->getParam('subject_id');
            $x_time     = $this->getParam('x_time');
            
            if ( empty($school_id) || empty($teacher_id) )
            {
            	throw new Exception('Please provide school_id and teacher_id');
            }
			
            if ( !self::hasStarted()  )
            {
            	if ( !self::isCurrentTeacher() )
            	{
            		if ( !self::isCurrentClassSectionSubject() )
            		{
            			if ( !self::isCurrentClassSection() )
            			{
            				
            				if ( !self::getAttendenceMasterTable()->XtimeDiFFbyDate($class_id, $section_id, $teacher_id, $subject_id, $x_time) )
            				{
            					
            					$student_list = self::getStudentTable()->getStudentListIds($school_id, $class_id, $section_id);
            				
            					$notificationTypeTable = new Api_Model_NotificationType();
							 
            					$notifydata = json_encode ( array(
            					'type_id'   => $notificationTypeTable->get('START'),
            					'students'  => $student_list,
            					'school_id' => $school_id,
            					'class_id'	=> $class_id,
            					'section_id'=> $section_id,
            					'teacher_id'=> $teacher_id
            					) );
            					
            					exec("/opt/lampp/bin/php /opt/lampp/htdocs/schoolerp/soap/ctp/application/helpers/Notification_Student.php '$notifydata' > /dev/null &");
								
								Socket_Helper::write(array(
            						'class_id'		=> $class_id,
            						'section_id'	=> $section_id,
            						'teacher_id'	=> 0
							    ));
            				
            				}
            				
            				
            				$this->output['status'] = 5;
            				$this->output['message'] = 'Ok';
            			}
            			else 
            			{
            				$this->output['status'] = 4;
            				$this->output['class_session_id'] = self::isCurrentClassSection();
            				$this->output['message'] = 'Class for different subject already running';
            			}
            		}
            		else 
            		{
            			$this->output['status'] = 3;
            			$this->output['message'] = 'Class for this subject is already running';
            		}
            	}
            	else 
            	{
            		$this->output['status'] = 2;
            		$this->output['class_session_id'] = self::isCurrentTeacher();
            		$this->output['message'] = 'You are currently running the class';
            	}
            }
            else {
            	$this->output['status'] = 1;
            	$this->output['class_session_id'] = self::hasStarted();
            	$this->output['message'] = 'You have already started the class for section and subject';
            }
            
            
            
		}catch (Exception $e)
		{
			$this->output['status']  = 0;
			$this->output['message'] = $e->getMessage();
		}
		$response = new Response();
			$response->getResponse()
			->setHttpResponseCode(200)
			->setHeader( 'Content-Type', 'application/json' )
			->appendBody( json_encode( $this->output ) );
	}
	protected static function sendNotificationToStudents( $school_id, $class_id, $section_id, $teacher_id, $student_id)
	{
		$notificationTypeTable = new Api_Model_NotificationType();
		$notify_type_id = $notificationTypeTable->get('START');
		
		$notifyTable = new Api_Model_Notification();
		$notifyTable->saveForStudent( array(
			'type_id'		=> $notify_type_id,
			'school_id'		=> $school_id,
			'class_id'		=> $class_id,
			'section_id'	=> $section_id,
			'student_id'	=> $student_id,
			'notify_by' 	=> 'Teacher',
			'notify_by_id'	=> $teacher_id
		));
		
		Socket_Helper::write( array(
			'class_id' 		=> $class_id,
			'section_id'	=> $section_id,
			'teacher_id'	=> 0
		));
		
	}
	protected static function sendNotificationToTeacher( $school_id, $class_id, $section_id, $teacher_id, $student_id)
	{
		$notificationTypeTable = new Api_Model_NotificationType();
		$notify_type_id = $notificationTypeTable->get('STUDENTJOINED');
		
		$notifyTable = new Api_Model_Notification();
		$notifyTable->saveForTeacher( array(
			'type_id'		=> $notify_type_id,
			'school_id'		=> $school_id,
			'class_id'		=> $class_id,
			'section_id'	=> $section_id,
			'teacher_id'	=> $teacher_id,
			'notify_by' 	=> 'Student',
			'notify_by_id'	=> $student_id
		));
		
		Socket_Helper::write( array(
			'class_id' 		=> 0,
			'section_id'	=> 0,
			'teacher_id'	=> $teacher_id
		));
		
	}
}