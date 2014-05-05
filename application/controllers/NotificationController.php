<?php

include_once APPLICATION_PATH.'/models/NotificationType.php';
include_once APPLICATION_PATH.'/models/Notification.php';
include_once APPLICATION_PATH.'/models/Staff.php';
include_once APPLICATION_PATH.'/models/Student.php';
include_once APPLICATION_PATH.'/models/GetClass.php';
include_once APPLICATION_PATH.'/models/GetSection.php';
include_once APPLICATION_PATH.'/models/ClassSession.php';
include_once APPLICATION_PATH.'/models/BtnSync.php';
include_once APPLICATION_PATH.'/models/JoinStatus.php';
include_once APPLICATION_PATH.'/helpers/Socket.php';
include_once APPLICATION_PATH.'/models/AssessmentNotification.php';


class Notification extends Zend_Controller_Request_Http
{ 
	private $output = array(
	'status'	=> 0
	);
	
	const CLASS_START          = 1;
    const CLASS_STOP           = 15;
    const CLASS_STARTED        = 2;
    const HOMEWORK             = 9;
    const DIARY                = 19;
    const NOTES                = 10;
    const ASSIGNMENT_SUBMITTED = 12;
    const STUDENT_LOGOUT       = 16;
    const EXIT_FROM_CLASS      = 17;
    const STUDENT_JOINED_CLASS = 18;
    const REMARK 	       		= 11;
	
    protected static function getAssessmentNotificationTable()
    {
    	return new Api_Model_AssessmentNotification();
    }
	protected static function getNotifyTypeTable()
	{
		return new Api_Model_NotificationType();
	}
	
	protected static function getNotifyTable()
	{
		return new Api_Model_Notification();
	}
	
	protected static function getStaffTable()
	{
		return new Api_Model_Staff();
	}
	
	protected static function getStudentTable()
	{
		return new Api_Model_Student();
	}
	
	protected static function getClassTable()
	{
		return new Api_Model_GetClass();
	}
	
	protected static function getClassSessionTable()
	{
		return new Api_Model_ClassSession();
	}
	
	protected static function getBtnSyncTable()
	{
		return new Api_Model_BtnSync();
	}
	
	protected static function getJoinStatusTable()
	{
		return new Api_Model_JoinStatus();
	}
	
	protected static function getSectionTable()
	{
		return new Api_Model_GetSection();
	}
	
	public function indexAction()
	{
		try {
			$school_id = (int)$this->getParam('school_id');
            $teacher_id = (int)$this->getParam('teacher_id');
            $student_id = (int)$this->getParam('student_id');
            
            if ( empty( $school_id) )
            {
            	throw new Exception('Please provide school_id');
            }
            
            $notifications = array();
            
            if ( !empty( $teacher_id))
            {
            	$notifications = self::getNotifyTable()->getByTeacherId($school_id, $teacher_id);
            	 $who = 'teacher_by_id';
                 $value = 'teacher_id';
            }
            elseif ( !empty($student_id) )
            {
            	/*exec("/opt/lampp/bin/mysql -h 172.16.0.197 -uroot -pExDb20Tra schoolerp -e ' UPDATE  `ctp_notification` ctp, ctp_notification_type ctpt SET  ctp.`read` =1 WHERE ctp.student_id = $student_id AND ctp.school_id=$school_id AND ctp.date_created < date_sub(NOW(), interval 1 minute ) AND ctp.notification_type_id = ctpt.notification_type_id and ctpt.priority =  \"'HIGH'\" and ctp.`read` != 1  ' ");
				*/
            	$notificationsS = self::getNotifyTable()->getByStudentId($school_id, $student_id);
				
				if( sizeof( $notificationsS ) ) {
					$notifications = $notificationsS;
				}
				else {
					$notifications = self::getNotifyTable()->getByStudentIdLow($school_id, $student_id);
				}
            	$who = 'student_id';
                $value = 'student_id';
            }
            
            if ( !sizeof($notifications) )
            {
            	throw new Exception('No notifications');
            }
            
            $this->output['status'] = 1;
            foreach ( $notifications as $notification )
            {
            	$this->output['contents'][] = array(
            	'notification_id'		=> $notification['notification_id'],
            	'notification_type'		=> $notification['notification_type_type'],
            	'notification_msg'		=> self::getNotifyMessages(
            								$notification['notification_type_msg'],
            								$notification['notification_type_id'],
            								$notification['notify_by'], 
            								$notification['notify_by_id'],
            								$notification['school_id'],
            								$notification['class_id'],
            								$notification['section_id']
            								),
            	'date_recieved'			=> $notification['date_recieved'],
            	'priority'				=> $notification['priority'],
            	$who					=> $notification[$value],
            	'paper_id'				=> self::getPaperId($notification['notification_type_id'], $notification['notification_id']),
            	'teacher_id'			=> self::getAssessmentTeacherId($notification['notification_type_id'], $notification['notification_id']),
            	'subject_id'			=> self::getAssessmentSubjectId($notification['notification_type_id'],  $notification['notification_id'])
            	);
            }
            $uid = ( $value == 'student_id') ? $student_id : $teacher_id;
           exec("/opt/lampp/bin/mysql -uroot -pExDb20Tra schoolerp -e ' UPDATE  `ctp_notification` ctp, ctp_notification_type ctpt SET  ctp.`read` =1 
 WHERE ctp.$value = $uid AND ctp.notification_type_id = ctpt.notification_type_id and ctpt.priority =  \"'HIGH'\" and ctp.`read` != 1; ' ");
           
           
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
	
	protected static function getPaperId( $type_id, $notify_id ){
        return ($type_id == 0xD)? (int)self::getAssessmentNotificationTable()->getById($notify_id)->paper_set_id : 0;
    }
    
    protected static function getAssessmentTeacherId($type_id, $notify_id ){
        return ( $type_id == 0xD )? (int)self::getAssessmentNotificationTable()->getById($notify_id)->teacher_id : 0;
    }
    
    protected static  function getAssessmentSubjectId($type_id, $notify_id ){
        return ( $type_id == 0xD ) ? (int)self::getAssessmentNotificationTable()->getById($notify_id)->subject_id : 0;
    }
	
	public function getAction()
	{
		try {
			$school_id  = $this->getParam('school_id');
            $student_id = $this->getParam('student_id');
            $teacher_id = $this->getParam('teacher_id');
            
			if ( empty( $school_id) )
            {
            	throw new Exception('Please provide school_id');
            }
            
            $notifications = array();
            
            if ( !empty( $teacher_id))
            {
            	 $notifications = self::getNotifyTable()->getByTeacherIdDate($school_id, $teacher_id);
            	 $who = 'teacher_by_id';
                 $value = 'teacher_id';
            }
            elseif ( !empty($student_id) )
            {
            	$notifications = self::getNotifyTable()->getByStudentIdDate($school_id, $student_id);
            	$who = 'student_id';
                $value = 'student_id';
            }
            
            if ( !sizeof($notifications) )
            {
            	throw new Exception('No notifications');
            }
            
            $this->output['status'] = 1;
            foreach ( $notifications as $notification )
            {
            	$this->output['contents'][] = array(
            	'notification_id'		=> $notification['notification_id'],
            	'notification_type'		=> $notification['notification_type_type'],
            	'notification_msg'		=> self::getNotifyMessages(
            								$notification['notification_type_msg'],
            								$notification['notification_type_id'],
            								$notification['notify_by'], 
            								$notification['notify_by_id'],
            								$notification['school_id'],
            								$notification['class_id'],
            								$notification['section_id']
            								),
            	'date_recieved'			=> date('H:i:s', strtotime($notification['date_recieved'])),
            	'priority'				=> $notification['priority'],
            	$who					=> $notification[$value]
            	);
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
	protected static function getType($type)
	{
		$type = str_replace(' ', '', $type);
		if ( $type == 'BLACKOUT')
		{
			$type = 'UNBLOCK';
		}
		elseif ( $type == 'BLACKIN')
		{
			$type = 'BLOCK';
		}
		
		return $type;
	}
	public function postAction()
	{
		try {
			$request_teacher_id = $this->getParam('teacher_id');
            $request_student_id = $this->getParam('student_id');
            $school_id 			= $this->getParam('school_id');
            $class_id 			= $this->getParam('class_id');
            $section_id 		= $this->getParam('section_id');
            $type 				= $this->getParam('type');
            $class_session_id 	= $this->getParam('class_session_id');
			$isexit             = $this->getParam('isexit');
                
                //
            $type = self::getType($type);
            
            if ( empty( $school_id) or empty($class_id) or empty($section_id) or empty($type) )
            {
            	throw new Exception('Missing parameters');
            }
            
            if ( empty( $request_teacher_id) && empty( $request_student_id) )
            {
            	throw new Exception('Please provide teacher_id or student_id');
            }
            
            $isBtn = false;
            
            $isExitfromClass = false;
            
            switch ( $type )
            {
            	case 'START':
            		//Ignore
            	break;
            	
            	case 'EXITFROMCLASS':
					
					if( $isexit == 1 ) {
						$isExitfromClass = true;
						$teacher_id = (int)self::getClassSessionTable()->getById($class_session_id)->teacher_id;
            			self::getNotifyTable()->saveForTeacher( array(
            			'type_id'		=> self::getNotifyTypeTable()->get('EXITFROMCLASS'),
            			'school_id' 	=> $school_id,
            			'class_id'		=> $class_id,
            			'section_id'	=> $section_id,
            			'teacher_id'	=> $teacher_id,
            			'notify_by'		=> 'Student',
            			'notify_by_id'	=> $request_student_id
            			));
            			Socket_Helper::write( array(
            			'class_id'		=> 0,
            			'section_id'	=> 0,
            			'teacher_id'	=> $teacher_id
            			));
            		
            			$this->output['status'] =  1;
            			$this->output['message'] = 'success';
					}
            	break;
            	
            	case 'LOCK':
            		self::getBtnSyncTable()->save( array(
            		'school_id'		=> $school_id,
            		'class_id'		=> $class_id,
            		'section_id'	=> $section_id,
            		'button_name'	=> 'LOCK',
            		'status'		=> 'FALSE'
            		));
            		$isBtn = true;
            	break;
            	
            	case 'BLOCK':
            		self::getBtnSyncTable()->save( array(
            		'school_id'		=> $school_id,
            		'class_id'		=> $class_id,
            		'section_id'	=> $section_id,
            		'button_name'	=> 'BLACKOUT',
            		'status'		=> 'FALSE'
            		));
            		$isBtn = true;
            	break;
            	
            	case 'MUTE':
            		self::getBtnSyncTable()->save( array(
            		'school_id'		=> $school_id,
            		'class_id'		=> $class_id,
            		'section_id'	=> $section_id,
            		'button_name'	=> 'MUTE',
            		'status'		=> 'FALSE'
            		));
            		$isBtn = true;
            	break;
            	
            	case 'UNLOCK':
            		self::getBtnSyncTable()->save( array(
            		'school_id'		=> $school_id,
            		'class_id'		=> $class_id,
            		'section_id'	=> $section_id,
            		'button_name'	=> 'LOCK',
            		'status'		=> 'TRUE'
            		));
            		$isBtn = true;
            	break;
            	
            	case 'UNBLOCK':
            		self::getBtnSyncTable()->save( array(
            		'school_id'		=> $school_id,
            		'class_id'		=> $class_id,
            		'section_id'	=> $section_id,
            		'button_name'	=> 'BLACKOUT',
            		'status'		=> 'TRUE'
            		));
            		$isBtn = true;
            	break;
            	
            	case 'UNMUTE':
            		self::getBtnSyncTable()->save( array(
            		'school_id'		=> $school_id,
            		'class_id'		=> $class_id,
            		'section_id'	=> $section_id,
            		'button_name'	=> 'MUTE',
            		'status'		=> 'TRUE'
            		));
            		$isBtn = true;
            	break;
            }
            
            if ( ( $isExitfromClass) === false )
            {
            	if ( $request_teacher_id )
            	{
            		$student_list = self::getStudentTable()->getStudentListIds($school_id, $class_id, $section_id);
            		foreach ($student_list as $st)
            		{
            			$student_lists[] = $st['student_id'];
            		}
            		
				
            		if ( $isBtn) 
            		{
            			$student_joined_list = self::getJoinStatusTable()->getStatusArray($student_lists);
            			
            			$notifyData = json_encode( array(
						'type_id' 		=> self::getNotifyTypeTable()->get($type),
						'students'  	=> $student_joined_list,
						'school_id' 	=> $school_id,
						'class_id'		=> $class_id,
						'section_id'	=> $section_id,
						'teacher_id'	=> $request_teacher_id
						));
						
						
						
exec("/opt/lampp/bin/php /opt/lampp/htdocs/schoolerp/soap/ctp/application/helpers/Notification_Student.php '$notifyData' > /dev/null &");
					
						Socket_Helper::write( array(
						'class_id' 	 => $class_id,
						'section_id' => $section_id,
						'teacher_id' => 0
						));
            		}
            		else 
            		{
            			$notifyData = json_encode( array(
						'type_id' 		=> self::getNotifyTypeTable()->get($type),
						'students'  	=> $student_list,
						'school_id' 	=> $school_id,
						'class_id'		=> $class_id,
						'section_id'	=> $section_id,
						'teacher_id'	=> $request_teacher_id
						));
						exec("/opt/lampp/bin/php /opt/lampp/htdocs/schoolerp/soap/ctp/application/helpers/Notification_Student.php '$notifyData' > /dev/null &");
					
						Socket_Helper::write( array(
						'class_id' 	 => $class_id,
						'section_id' => $section_id,
						'teacher_id' => 0
						));
            		}
            	}
            }
           $this->output['status'] = 1;
           $this->output['message'] = 'success';
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
	
	public function putAction()
	{
		try {
			
			$notification_id = $this->getParam('notification_id');
            $student_id 	 = $this->getParam('student_id');
            $teacher_id 	 = $this->getParam('teacher_id');
            
			if( $student_id ) {
                self::getNotifyTable()->markAsReadByStudentId($student_id);
            }
            elseif( $teacher_id ) {
                self::getNotifyTable()->markAsReadByTeacherId($teacher_id);
            }
            elseif( $notification_id ){
                self::getNotifyTable()->markAsReadById($notification_id);
            }
            else {
                throw new exception('Please provide any id');
            }
            
            $this->output['status'] = 1;
            $this->output['message'] = 'Success';
		}
		catch (Exception $e)
		{
			$this->output['status'] = 0;
		}
		
		$response = new Response();
		$response->getResponse()
		->setHttpResponseCode(200)
		->setHeader( 'Content-Type', 'application/json' )
		->appendBody( json_encode( $this->output ) );
	}
	
	protected static function getNotifyMessages($msgs, $type_id, $notify_by, $notify_by_id, $school_id, $class_id, $section_id)
	{
		switch ( $type_id)
		{
			case self::CLASS_START:
				return self::getStaffTable()->getFullname($notify_by_id).' is about to start the class';
			break;
			
			case self::ASSIGNMENT_SUBMITTED:
				return self::getStudentTable()->getFullname($notify_by_id).' of '.self::getClassSection($school_id, $class_id, $section_id).' has submitted homework';
			break;
			
			case self::CLASS_STARTED:
				return self::getStaffTable()->getFullname($notify_by_id).' has started the class';
			break;
			
			case self::CLASS_STOP:
				return self::getStaffTable()->getFullname($notify_by_id). ' has stopped the class';
			break;
			
			case self::HOMEWORK:
				return self::getStaffTable()->getFullname($notify_by_id).' has given you homework';
			break;
			
			case self::NOTES:
				if ( $notify_by == 'Teacher')
				{
					return self::getStaffTable()->getFullname($notify_by_id).' has shared a note with you';
				}
				elseif ($notify_by == 'Student')
				{
					return self::getStudentTable()->getFullname($notify_by_id).' of '.self::getClassSection($school_id, $class_id, $section_id).' has shared a note with you';
				}
			break;
			
			case self::DIARY:
				if ( $notify_by == 'Teacher')
				{
					return self::getStaffTable()->getFullname($notify_by_id).' has sent you a message';
				}
				elseif ($notify_by == 'Student')
				{
					return self::getStudentTable()->getFullname($notify_by_id).' of '.self::getClassSection($school_id, $class_id, $section_id).' has sent you a message';
				}
			break;
			
			case self::STUDENT_LOGOUT:
				return self::getStudentTable()->getFullname($notify_by_id).' of '.self::getClassSection($school_id, $class_id, $section_id).' has logged out';
			break;
			
			case self::STUDENT_JOINED_CLASS:
				return self::getStudentTable()->getFullname($notify_by_id).' of '.self::getClassSection($school_id, $class_id, $section_id).' has joined the class';
			break;
			
			case self::EXIT_FROM_CLASS:
				return self::getStudentTable()->getFullname($notify_by_id).' of '.self::getClassSection($school_id, $class_id, $section_id).' has left the class';
			break;
			
			default:
				return $msgs;
			break;
		}
	}
	
	protected static function getClassSection($school_id, $class_id, $section_id)
	{
		return self::getClassTable()->getName($school_id, $class_id).'-'.self::getSectionTable()->getName($school_id, $section_id);
	}
}