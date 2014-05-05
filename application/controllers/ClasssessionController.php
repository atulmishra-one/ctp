<?php

include_once APPLICATION_PATH.'/models/ClassSession.php';
include_once APPLICATION_PATH.'/models/WhiteboardConfig.php';
include_once APPLICATION_PATH.'/models/WhiteboardSession.php';
include_once APPLICATION_PATH.'/models/NotificationType.php';
include_once APPLICATION_PATH.'/models/Notification.php';
include_once APPLICATION_PATH.'/models/BtnSync.php';
include_once APPLICATION_PATH.'/models/Attendence.php';
include_once APPLICATION_PATH.'/models/AttendenceMaster.php';
include_once APPLICATION_PATH.'/helpers/Socket.php';

class Classsession extends Zend_Controller_Request_Http
{
	private $output = array(
		'status'	=> 0
	);

	protected static function getAttendenceMasterTable()
	{
		return new Api_Model_AttendenceMaster();
	}
	
	protected static function getAttendenceTable()
	{
		return new Api_Model_Attendence();
	}

	protected static function getClassSessionTable()
	{
		return new Api_Model_ClassSession();
	}

	protected static function getNotifyTypeTable()
	{
		return new Api_Model_NotificationType();
	}

	protected static function getNotifyTypeId( $type)
	{
		return self::getNotifyTypeTable()->get($type);
	}

	protected static function getNotifyTable()
	{
		return new Api_Model_Notification();
	}

	protected static function getBtnSyncTable()
	{
		return new Api_Model_BtnSync();
	}

	protected static function getWhiteBoardConfigIpTable()
	{
		return new Api_Model_WhiteboardConfig();
	}

	protected static function getWhiteBoardSessionTable()
	{
		return new Api_Model_WhiteboardSession();
	}

	protected static function saveBtnStatus($btnname, $status)
	{
		global $school_id, $class_id, $section_id;

		return self::getBtnSyncTable()->save( array(
		'school_id'		=> $school_id,
		'class_id'		=> $class_id,
		'section_id'	=> $section_id,
		'button_name'	=> $btnname,
		'status'		=> $status
		));
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

	protected static function isCurrentClassSection()
	{
		global $school_id, $class_id, $section_id;

		return self::getClassSessionTable()->isCurrentClassSection( array(
			'school_id'	 => $school_id,
			'class_id'	 => $class_id,
			'section_id' => $section_id
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

	protected static function getWhiteBoardConfigID()
	{
		global $school_id, $class_id, $section_id;

		return self::getWhiteBoardConfigIpTable()->getId($school_id, $class_id, $section_id);
	}

	protected static function startWhiteBoard()
	{
		global $teacher_id, $school_id, $class_id, $section_id;

		$id = self::getWhiteBoardConfigID();

		if ( !empty($id)) {
				
			if ( self::getWhiteBoardSessionTable()->checkByWcAndTeacher($id, $teacher_id) == 1)
			{
				self::getWhiteBoardSessionTable()->updateStartTimeAndStatus($id, $teacher_id, 'ON');
			}
			else
			{
				self::getWhiteBoardSessionTable()->save( array(
					'whiteboard_config_id'	=> $id,
					'teacher_id'			=> $teacher_id
				));
			}
		}
	}
	/**
	 * @description
	 * Use to START CLASS
	 */
	public function postAction()
	{
		try {
				
			global $teacher_id, $school_id, $class_id, $section_id, $subject_id;
				
			$teacher_id = $this->getParam('teacher_id');
			$school_id = $this->getParam('school_id');
			$class_id = $this->getParam('class_id');
			$section_id = $this->getParam('section_id');
			$subject_id = $this->getParam('subject_id');
			$attendance_master_id =& $this->getParam('attendance_master_id');

			if ( empty($teacher_id) || empty($school_id) || empty($class_id) || empty($section_id) || empty($subject_id) )
			{
				throw new Exception('Missing Parameters');
			}

			if ( self::hasStarted() )
			{
				$this->output['class_session_id'] = self::hasStarted();
				throw new Exception('Class has been already started');
			}

			if ( self::isCurrentTeacher() )
			{
				$this->output['class_session_id'] = self::isCurrentTeacher();
				throw new Exception('You are already taking class');
			}

			if ( self::isCurrentClassSection() )
			{
				$this->output['class_session_id'] = self::isCurrentClassSection();
				throw new Exception('There is already a class running for this class section');
			}

			if ( self::isCurrentClassSectionSubject() )
			{
				$this->output['class_session_id'] = self::isCurrentClassSectionSubject();
				throw new Exception('There is already a class running for this class section subject');
			}

			$lastId = self::getClassSessionTable()->save( array(
            			'teacher_id'	=> $teacher_id,
            			'school_id'		=> $school_id,
            			'class_id'		=> $class_id,
            			'section_id'	=> $section_id,
            			'subject_id'	=> $subject_id
			));

			
			self::startWhiteBoard();

			self::saveBtnStatus('MUTE', 'FALSE');
			self::saveBtnStatus('BLACKOUT', 'FALSE');
			self::saveBtnStatus('LOCK', 'TRUE');

			$this->output['status'] = 1;
			$this->output['class_session_id'] = $lastId;

			if ( !empty($attendance_master_id) )
			{
				$student_list = self::getAttendenceTable()->getByAttendanceMasterIdAndAttendArray($attendance_master_id);
				 
				if ( sizeof($student_list) )
				{
					
					$notifyData = json_encode( array(
					 'type_id'	 => self::getNotifyTypeId('STARTED'),
					 'students'	 => $student_list,
					 'school_id' => $school_id,
					 'class_id'	 => $class_id,
					 'section_id'=> $section_id,
					 'teacher_id'=> $teacher_id
					));
					
		exec("/opt/lampp/bin/php /opt/lampp/htdocs/schoolerp/soap/ctp/application/helpers/Notification_Student.php '$notifyData' > /dev/null &");
					Socket_Helper::write(array(
            						'class_id'		=> $class_id,
            						'section_id'	=> $section_id,
            						'teacher_id'	=> 0
					));
				}
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
	
	protected static function getLatestSession( $teacher_id )
	{
		global $school_id, $class_id, $section_id;
		
		return self::getClassSessionTable()->getCurrentClassSectionTeacher( array(
		'school_id'		=> $school_id,
		'class_id'		=> $class_id,
		'section_id'	=> $section_id,
		'teacher_id'	=> $teacher_id
		));
	}
	public function putAction()
	{
		try {
			global $school_id, $class_id, $section_id;
			
			$class_session_id 	  = $this->getParam('class_session_id');
            $school_id 			  = $this->getParam('school_id');
            $class_id 			  = $this->getParam('class_id');
            $section_id 		  = $this->getParam('section_id');
            $request_teacher_id   = $this->getParam('teacher_id');
            $attendance_master_id = $this->getParam('attendance_master_id');
            
            if ( empty($school_id) || empty($class_id) || empty($section_id) || empty($request_teacher_id) )
            {
            	throw new Exception('Missing parameters');
            }
            
            if ( empty($class_session_id) )
            {
            	$lastestSession = self::getLatestSession($request_teacher_id);
            	if ( sizeof($lastestSession) )
            	{
            		$class_session_id = $lastestSession->id;
            		$subject_id       = $lastestSession->subject_id;
            	}
            }
            else {
            	$class_session_id = $class_session_id;
            }
            
            if ( empty( $class_session_id) )
            {
            	throw new Exception('Invalid class_session_id');
            }
            
            if ( empty($attendance_master_id) )
            {
            	$subject_id = self::getClassSessionTable()->getById( $class_session_id)->subject_id;
            	$attendance_master_id = self::getAttendenceMasterTable()
            	->getLatestAttendence($request_teacher_id, $school_id, $class_id, $section_id, $subject_id);
            }
            else {
            	$attendance_master_id = $attendance_master_id;
            }
            
            $teacher_id = self::getClassSessionTable()->getById($class_session_id)->teacher_id;
            
            if ( self::getClassSessionTable()->updateEndTime($class_session_id) )
            {
            	self::saveBtnStatus('MUTE', 'TRUE');
            	self::saveBtnStatus('LOCK', 'TRUE');
            	self::saveBtnStatus('BLACKOUT', 'TRUE');
            	
            	self::getWhiteBoardSessionTable()->stopWhiteBoard( self::getWhiteBoardConfigID() , $teacher_id);
            	
            	if ( $request_teacher_id != $teacher_id )
            	{
            		self::getNotifyTable()->saveForTeacher( array(
            		'type_id'		=> self::getNotifyTypeId('STOP'),
            		'school_id'		=> $school_id,
            		'class_id'		=> $class_id,
            		'section_id'	=> $section_id,
            		'teacher_id'	=> $teacher_id,
            		'notify_by'		=> 'Teacher',
            		'notify_by_id'	=> $request_teacher_id
            		));
            		Socket_Helper::write( array(
            		'class_id'		=> 0,
            		'section_id'	=> 0,
            		'teacher_id'	=> $teacher_id
            		));
            	}
            	
            	$student_list = self::getAttendenceTable()->getByAttendanceMasterIdAndAttendArray($attendance_master_id);
            	
            	if ( sizeof($student_list) )
            	{
            		$notifyData = json_encode( array(
					 'type_id'	 => self::getNotifyTypeId('STOP'),
					 'students'	 => $student_list,
					 'school_id' => $school_id,
					 'class_id'	 => $class_id,
					 'section_id'=> $section_id,
					 'teacher_id'=> $request_teacher_id
					));
					
		exec("/opt/lampp/bin/php /opt/lampp/htdocs/schoolerp/soap/ctp/application/helpers/Notification_Student.php '$notifyData' > /dev/null &");
            		Socket_Helper::write( array(
            		'class_id'		=> $class_id,
            		'section_id'	=> $section_id,
            		'teacher_id'	=> 0
            		));
            	}
            	
            	$this->output['status']  = 1;
            	$this->output['message'] = 'success';
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

	protected static function sendNotificationStudent( $type, $sid, $teacher_id )
	{
		global $school_id, $class_id, $section_id;

		self::getNotifyTable()->saveForStudent( array(
			'type_id'		=> self::getNotifyTypeId($type),
			'school_id' 	=> $school_id,
			'class_id'		=> $class_id,
			'section_id'	=> $section_id,
			'student_id'	=> $sid,
			'notify_by'		=> 'Teacher',
			'notify_by_id'  => $teacher_id
		));
	}
}