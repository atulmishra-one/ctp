<?php 

include_once APPLICATION_PATH.'/models/WhiteboardConfig.php';
include_once APPLICATION_PATH.'/models/WhiteboardSession.php';
include_once APPLICATION_PATH.'/models/ClassSession.php';
include_once APPLICATION_PATH.'/models/Staff.php';
include_once APPLICATION_PATH.'/models/UserLogin.php';
include_once APPLICATION_PATH.'/models/GetClass.php';
include_once APPLICATION_PATH.'/models/BtnSync.php';
include_once APPLICATION_PATH.'/models/NotificationType.php';
include_once APPLICATION_PATH.'/models/Notification.php';
include_once APPLICATION_PATH.'/helpers/Socket.php';
include_once APPLICATION_PATH.'/models/Attendence.php';
include_once APPLICATION_PATH.'/models/AttendenceMaster.php';


class Whiteboard extends Zend_Controller_Request_Http
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
	
	protected static function getNotifyTypeTable()
	{
		return new Api_Model_NotificationType();
	}
	
	protected static function getNotifyTable()
	{
		return new Api_Model_Notification();
	}
	
	protected static function getWhiteBoardConfigTable()
	{
		return new Api_Model_WhiteboardConfig();
	}
	
	protected static function getWhiteBoardSessionTable()
	{
		return new Api_Model_WhiteboardSession();
	}
	
	protected static function getClassSessionTable()
	{
		return new Api_Model_ClassSession();
	}
	
	protected static function getStaffTable()
	{
		return new Api_Model_Staff();
	}
	
	protected static function getUserLoginTable()
	{
		return new Api_Model_UserLogin();
	}
	
	protected static function getClassTable()
	{
		return new Api_Model_GetClass();
	}
	
	protected static function getBtnSyncTable()
	{
		return new Api_Model_BtnSync();
	}
	
	public function getAction()
	{
		try {
		 $school_id 			= (int)$this->getParam('school_id');
		 $whiteboard_config_ip 	= $this->getParam('whiteboard_config_ip');
		 
		 if ( empty( $school_id ) )
		 {
		 	throw new Exception('Please provide school_id');
		 }
		 
		 $wInfo = self::getWhiteBoardConfigTable()->getByIp($whiteboard_config_ip, $school_id);
		 
		 if ( !sizeof($wInfo) )
		 {
		 	throw new Exception('Invalid school_id and whiteboard_config_ip');
		 }

		 $class_session = self::getClassSessionTable()->getCurrentSession( array(
		 'school_id'	=> $school_id,
		 'class_id'		=> $wInfo->class_id,
		 'section_id'	=> $wInfo->section_id
		 ));

		 if ( !sizeof($class_session) )
		 {
		 	throw new Exception('Invalid class_session');
		 }
		 
		 $teacher_id = self::getWhiteBoardSessionTable()->getTeacherId($wInfo->whiteboard_config_id, $school_id);
		 
		 if ( empty($teacher_id) )
		 {
		 	throw new Exception('Invalid teacher');
		 }
		 
		 $user_id 	 = self::getStaffTable()->getById($teacher_id, $school_id);
		 
		 $loginInfo  = self::getUserLoginTable()->get($school_id, $user_id);
		 
		 $this->output['status'] = 1;
		 $this->output['contents'][] = array(
		 'class_session_id'		=> $class_session->id,
		 'teacher_id'			=> $teacher_id,
		 'user_id'				=> $user_id,
		 'class_id'				=> $wInfo->class_id,
		 'class_name'			=> self::getClassTable()->getName($school_id, $wInfo->class_id),
		 'username'				=> $loginInfo->username,
		 'password'				=> $loginInfo->password,
		 'section_id'			=> $wInfo->section_id,
		 'subject_id'			=> $class_session->subject_id,
		 'mute'					=> self::getBtnSyncTable()->getStatus($school_id, $wInfo->class_id, $wInfo->section_id, 'MUTE'),
		 'blackout'				=> self::getBtnSyncTable()->getStatus($school_id, $wInfo->class_id, $wInfo->section_id, 'BLACKOUT'),
		 'lock'					=> self::getBtnSyncTable()->getStatus($school_id, $wInfo->class_id, $wInfo->section_id, 'LOCK')
		 );
		 
		 
		 
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
	
	public function putAction()
	{
		try {
			$teacher_id 	= $this->getParam('teacher_id');
			$school_id 		= $this->getParam('school_id');
			$class_id 		= $this->getParam('class_id');
			$section_id 	= $this->getParam('section_id');
			
			$whiteboard_config_id = self::getWhiteBoardConfigTable()->getId($school_id, $class_id, $section_id);
			
			if ( empty($whiteboard_config_id) )
			{
				throw new Exception('Invalid request parameters');
			}
			
			$class_session = self::getClassSessionTable()->getCurrentClassSectionTeacher( array(
			'school_id'		=> $school_id,
			'class_id'		=> $class_id,
			'section_id'	=> $section_id,
			'teacher_id'	=> $teacher_id
			));
			
			if ( ! sizeof($class_session) )
			{
				throw new Exception('Invalid class_session_id ');
			}
			
			$class_session_teacher_id = self::getClassSessionTable()->getById($class_session->id)->teacher_id;
			$subject_id 			  = self::getClassSessionTable()->getById($class_session->id)->subject_id;
			
			$attendance_master_id = 0;
			
			$attendance_master_id = self::getAttendenceMasterTable()
            	->getLatestAttendence($teacher_id, $school_id, $class_id, $section_id, $subject_id);
			
			self::getClassSessionTable()->updateEndTime($class_session->id);
			
			self::getWhiteBoardSessionTable()->stopWhiteBoard($whiteboard_config_id, $teacher_id);
			
			$student_list = self::getAttendenceTable()->getByAttendanceMasterIdAndAttendArray($attendance_master_id);
			
			if ( sizeof($student_list) )
            {
            		$notifyData = json_encode( array(
					 'type_id'	 => self::getNotifyTypeTable()->get('STOP'),
					 'students'	 => $student_list,
					 'school_id' => $school_id,
					 'class_id'	 => $class_id,
					 'section_id'=> $section_id,
					 'teacher_id'=> $teacher_id
					));
					
		exec("/opt/lampp/bin/php /opt/lampp/htdocs/schoolerp/soap/ctp/application/helpers/Notification_Student.php '$notifyData' > /dev/null &");
            		Socket_Helper::write( array(
            		'class_id'		=> $class_id,
            		'section_id'	=> $section_id,
            		'teacher_id'	=> 0,
                    'type'          => Socket_Helper::CLASS_STOP
            		));
            }
			
			
			if ( $class_session_teacher_id != $teacher_id )
			{
				self::getNotifyTable()->saveForTeacher( array(
				'type_id'		=> self::getNotifyTypeTable()->get('STOP'),
				'school_id'		=> $school_id,
				'class_id'		=> $class_id,
				'section_id'	=> $section_id,
				'teacher_id'	=> $class_session_teacher_id,
				'notify_by'		=> 'Teacher',
				'notify_by_id'	=> $teacher_id
				));
			
				Socket_Helper::write( array(
				'class_id'		=> 0,
				'section_id'	=> 0,
				'teacher_id'	=> $class_session_teacher_id,
                'type'          => self::getNotifyTypeTable()->get('STOP')
				));
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
	
	public function indexAction()
	{
		try {
			
			$school_id 				= $this->getParam('school_id');
			$whiteboard_config_ip 	= $this->getParam('whiteboard_config_ip');
			$whiteboard_config_id 	= self::getWhiteBoardConfigTable()->getByIpSingle($whiteboard_config_ip, $school_id);
			
			if( self::getWhiteBoardSessionTable()->getLastestIndex($whiteboard_config_id) )
	                { 
			 echo 1;
			}
			else{
			 echo 0;
			}
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
		}
		
	}
}










