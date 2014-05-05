<?php

include_once APPLICATION_PATH.'/models/UserSection.php';
include_once APPLICATION_PATH.'/models/JoinStatus.php';
include_once APPLICATION_PATH.'/models/UserLogin.php';
include_once APPLICATION_PATH.'/models/Student.php';
include_once APPLICATION_PATH.'/models/GetClass.php';
include_once APPLICATION_PATH.'/models/GetSection.php';
include_once APPLICATION_PATH.'/models/MasterSection.php';
include_once APPLICATION_PATH.'/models/ClassSession.php';
include_once APPLICATION_PATH.'/models/NotificationType.php';
include_once APPLICATION_PATH.'/models/Notification.php';
include_once APPLICATION_PATH.'/helpers/Socket.php';

class Join extends Zend_Controller_Request_Http
{ 
	private $output = array(
	'status'	=> 0
	);
	
	protected static function getUserSectionTable()
	{
		return new Api_Model_UserSection();
	}
	
	protected static function getJoinTable()
	{
		return new Api_Model_JoinStatus();
	}
	
	protected static function getUserLoginTable()
	{
		return new Api_Model_UserLogin();
	}
	
	protected static function getStudentTable()
	{
		return new Api_Model_Student();
	}
	
	protected static function getClassTable()
	{
		return new Api_Model_GetClass();
	}
	
	protected static function getSectionTable()
	{
		return new Api_Model_GetSection();
	}
	
	protected static function getMasterSectionTable()
	{
		return new Api_Model_MasterSection();
	}
	
	protected static function getClassSessionTable()
	{
		return new Api_Model_ClassSession();
	}
	
	protected static function getNotifyTypeTable()
	{
		return new Api_Model_NotificationType();
	}
	
	protected static function getNotifyTable()
	{
		return new Api_Model_Notification();
	}
	
	public function postAction()
	{
		try {
			$user_id 	= (int)$this->getParam('user_id');
			$school_id 	= (int)$this->getParam('school_id');
			$status		= (string)$this->getParam('status');
			
			$assignedClassSection = self::getUserSectionTable()->getStudentAssignedClassSection($user_id, $school_id);
			
			if ( !sizeof($assignedClassSection) )
			{
				throw new Exception('User do not belong to any section');
			}
			
			$student_id = self::getStudentTable()->getInfo($user_id, 'id');
			
			self::getJoinTable()->save( array(
			'student_id'	=> $student_id,
			'status'		=> $status
			));
			
			$loginInfo = self::getUserLoginTable()->get($school_id, $user_id);
			$masterInfo = self::getMasterSectionTable()->getById($assignedClassSection['section_id']);
			
			$msGroupId = $masterInfo[0]->group_id? $masterInfo[0]->group_id: 0;
			
			$this->output['status'] = 1;
			$this->output['contents'][] = array(
			'master_section_id'	=> $assignedClassSection['section_id'],
			'class_id'			=> $assignedClassSection['class_id'],
			'class_name'		=> self::getClassTable()->getName($school_id, $assignedClassSection['class_id']),
			'section_id'		=> $assignedClassSection['section_id'],
			'section_name'		=> self::getSectionTable()->getName($school_id, $msGroupId),
			'student_id'		=> $student_id,
			'password'			=> $loginInfo->password,
			'current_date'		=> date('Y-m-d', time())
			);
		}
		catch (Exception $e)
		{
			$this->output['status']	 = 0;
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
			$student_id 		= $this->getParam('student_id');
         	$class_session_id 	= $this->getParam('class_session_id');
         	
         	if ( empty($student_id) )
         	{
         		throw new Exception('Please provide student_id');
         	}
         	
         	self::getJoinTable()->updateAttend($student_id);
         	
         	$classSession = self::getClassSessionTable()->getById( $class_session_id);
         	
         	if ( sizeof($classSession) )
         	{
         		self::getNotifyTable()->saveForTeacher( array(
         		'type_id'		=> self::getNotifyTypeTable()->get('EXITFROMCLASS'),
         		'school_id'		=> $classSession->school_id,
         		'class_id'		=> $classSession->class_id,
         		'section_id'	=> $classSession->section_id,
         		'teacher_id'	=> $classSession->teacher_id,
         		'notify_by'		=> 'Student',
         		'notify_by_id'	=> $student_id
         		));
         		
         		Socket_Helper::write( array(
         		'class_id'		=> $classSession->class_id,
         		'section_id'	=> $classSession->section_id,
         		'teacher_id'	=> 0,
                'type'          => Socket_Helper::EXITFROMCLASS
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
}