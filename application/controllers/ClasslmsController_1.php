<?php

include_once APPLICATION_PATH.'/models/ClassLms.php';
include_once APPLICATION_PATH.'/models/ClassSession.php';
include_once APPLICATION_PATH.'/models/BtnSync.php';
include_once APPLICATION_PATH.'/models/WhiteboardConfig.php';
include_once APPLICATION_PATH.'/models/WhiteboardSession.php';
include_once APPLICATION_PATH.'/models/Attendence.php';
include_once APPLICATION_PATH.'/models/NotificationType.php';
include_once APPLICATION_PATH.'/models/Notification.php';
include_once APPLICATION_PATH.'/models/JoinStatus.php';
include_once APPLICATION_PATH.'/helpers/Socket.php';
//include_once APPLICATION_PATH.'/models/BtnSync.php';

class Classlms extends Zend_Controller_Request_Http
{ 
	private $output = array(
		'status'	=> 0
	);
	
	protected static function getClassLmsTable()
	{
		return new Api_Model_ClassLms();
	}
	
	protected static function getClassSessionTable()
	{
		return new Api_Model_ClassSession();
	}
	
	protected static function getBtnSyncTable()
	{
		return new Api_Model_BtnSync();
	}
	
	protected static function getWhiteBoardConfigIp()
	{
		global $school_id, $class_id, $section_id;
		
		$whiteBoardConfigTable = new Api_Model_WhiteboardConfig();
		return $whiteBoardConfigTable->getId($school_id, $class_id, $section_id);
	}
	
	protected static function getWhiteBoardSessionTable()
	{
		return new Api_Model_WhiteboardSession();
	}
	
	protected static function getAttendenceTable()
	{
		return new Api_Model_Attendence();
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
	
	protected static function getJoinTable()
	{
		return new Api_Model_JoinStatus();
	}
	
	public function getAction()
	{
		try {
			$class_session_id = (int) $this->getParam('class_session_id');
			
			if ( empty($class_session_id) )
			{
				throw new Exception('Please provide class_session_id');
			}
			
			$current_url = self::getClassLmsTable()->getCurrentUrl($class_session_id);
			
			if ( $current_url === null)
			{
				throw new Exception('Invalid');
			}
			
			$classData = self::getClassSessionTable()->getById($class_session_id);
			
			if ( !sizeof($classData) )
			{
				throw new Exception('Invalid');
			}
			
			$school_id  = $classData->school_id;
			$class_id   = $classData->class_id;
			$section_id = $classData->section_id;
			
			$this->output['class_status'] = false;
			
			if ( (  self::getClassSessionTable()->isRunning($class_session_id) ) === true )
			{
				$this->output['class_status'] = true;
			}
			
			$this->output['status'] = 1;
			$this->output['current_url'] = $current_url;
			$this->output['mute'] = self::getBtnSyncTable()->getStatus($school_id, $class_id, $section_id, 'MUTE');
			$this->output['blackout'] = self::getBtnSyncTable()->getStatus($school_id, $class_id, $section_id, 'BLACKOUT');
			$this->output['lock'] = self::getBtnSyncTable()->getStatus($school_id, $class_id, $section_id, 'LOCK');
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
	
	public function postAction()
	{
		try {
			$class_session_id 	  = $this->getParam('class_session_id');
        	$current_url 	  	  = $this->getParam('current_url');
        	$mode 			  	  = $this->getParam('mode');
        	$attendance_master_id = $this->getParam('attendance_master_id');
        	
        	if ( empty( $class_session_id) )
        	{
        		throw new Exception('Please provide class_session_id');
        	}
        	
        	global $school_id, $class_id, $section_id;
        	
        	$classSessionValues = self::getClassSessionTable()->getClassSessionValuesById($class_session_id);
        	
        	$school_id 	= $classSessionValues['school_id'];
        	$class_id 	= $classSessionValues['class_id'];
        	$section_id = $classSessionValues['section_id'];
        	$teacher_id = $classSessionValues['teacher_id'];
        	
        	switch ( $mode )
        	{
        		case 'TABLET':
        			// STOP WHITEBOARD
        			self::getWhiteBoardSessionTable()->stopWhiteBoard( self::getWhiteBoardConfigIp() , $teacher_id);
        			
        			$student_list = self::getAttendenceTable()->getByAttendanceMasterIdAndAttend($attendance_master_id);
        			
        			if ( sizeof( $student_list) )
        			{
        				$notifyTypeIdUnblock = self::getNotifyTypeId('UNBLOCK');
        				$notifyTypeIdUnmute  = self::getNotifyTypeId('UNMUTE');
        				$notifyTypeIdlock 	 = self::getNotifyTypeId('LOCK');
         				
         				$joineds = self::getJoinTable()->getStatusArray($student_list);
         				
         				
         				$notifydata1 = json_encode( array(
         				'students' => $joineds,
         				'type_id'  => $notifyTypeIdUnblock,
         				'school_id'=> $school_id,
         				'class_id' => $class_id,
         				'section_id'=> $section_id,
         				'teacher_id'=> $teacher_id
         				));
         				
         				$notifydata2 = json_encode( array(
         				'students' => $joineds,
         				'type_id'  => $notifyTypeIdUnmute,
         				'school_id'=> $school_id,
         				'class_id' => $class_id,
         				'section_id'=> $section_id,
         				'teacher_id'=> $teacher_id
         				));
         				$notifydata3 = json_encode( array(
         				'students' => $joineds,
         				'type_id'  => $notifyTypeIdlock,
         				'school_id'=> $school_id,
         				'class_id' => $class_id,
         				'section_id'=> $section_id,
         				'teacher_id'=> $teacher_id
         				));
         				
         				
        exec("/opt/lampp/bin/php /opt/lampp/htdocs/schoolerp/soap/ctp/application/helpers/Notification_Student.php '$notifydata1' > /dev/null &");
        exec("/opt/lampp/bin/php /opt/lampp/htdocs/schoolerp/soap/ctp/application/helpers/Notification_Student.php '$notifydata2' > /dev/null &");
        exec("/opt/lampp/bin/php /opt/lampp/htdocs/schoolerp/soap/ctp/application/helpers/Notification_Student.php '$notifydata3' > /dev/null &");
        				
        				
        				Socket_Helper::write( array(
        				'class_id'		=> $class_id,
        				'section_id'	=> $section_id,
        				'teacher_id'	=> 0
        				));
        			}
        			
        			// SAVE BUTTONS STATUS
        			self::saveBtnStatus('MUTE', 'TRUE');
        			self::saveBtnStatus('LOCK', 'FALSE');
        			self::saveBtnStatus('BLACKOUT', 'TRUE');
        		break;
        		
        		case 'WHITEBOARD':
        			self::getWhiteBoardSessionTable()->updateByWcAndTeacher(self::getWhiteBoardConfigIp() , $teacher_id);
        			
        			$student_list = self::getAttendenceTable()->getByAttendanceMasterIdAndAttend($attendance_master_id);
        			
        			if ( sizeof( $student_list) )
        			{
        				$notifyTypeIdblock = self::getNotifyTypeId('BLOCK');
        				$notifyTypeIdmute  = self::getNotifyTypeId('MUTE');
        				
        				$joineds = self::getJoinTable()->getStatusArray($student_list);
        				
        				$notifydata1 = json_encode( array(
         				'students'  => $joineds,
         				'type_id'   => $notifyTypeIdblock,
         				'school_id' => $school_id,
         				'class_id'  => $class_id,
         				'section_id'=> $section_id,
         				'teacher_id'=> $teacher_id
         				));
         				
         				$notifydata2 = json_encode( array(
         				'students'  => $joineds,
         				'type_id'   => $notifyTypeIdmute,
         				'school_id' => $school_id,
         				'class_id'  => $class_id,
         				'section_id'=> $section_id,
         				'teacher_id'=> $teacher_id
         				));
        				
        exec("/opt/lampp/bin/php /opt/lampp/htdocs/schoolerp/soap/ctp/application/helpers/Notification_Student.php '$notifydata1' > /dev/null &");
        exec("/opt/lampp/bin/php /opt/lampp/htdocs/schoolerp/soap/ctp/application/helpers/Notification_Student.php '$notifydata2' > /dev/null &");
        
        				Socket_Helper::write( array(
        				'class_id'		=> $class_id,
        				'section_id'	=> $section_id,
        				'teacher_id'	=> 0
        				));
        			}
        			
        			// SAVE BUTTONS STATUS
        			self::saveBtnStatus('MUTE', 'FALSE');
        			self::saveBtnStatus('LOCK', 'TRUE');
        			self::saveBtnStatus('BLACKOUT', 'FALSE');
        			
        		break;
        		
        		default:
        		break;
        	}
        	
        	$this->output['status'] = 1;
        	$this->output['message'] = 'success';
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
	
	protected static function sendNotificationToStudent( $type_id, $sid, $tid)
	{
		global $school_id, $class_id, $section_id;
		
		self::getNotifyTable()->saveForStudent(array(
			'type_id'		=> $type_id,
			'school_id'		=> $school_id,
			'class_id'		=> $class_id,
			'section_id'	=> $section_id,
			'student_id'	=> $sid,
			'notify_by'		=> 'Teacher',
			'notify_by_id'	=> $tid
		));
	}
}