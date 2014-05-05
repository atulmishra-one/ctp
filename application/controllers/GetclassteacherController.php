<?php

include_once APPLICATION_PATH.'/models/UserSection.php';
include_once APPLICATION_PATH.'/models/AssignSubject.php';
include_once APPLICATION_PATH.'/models/Staff.php';
include_once APPLICATION_PATH.'/models/UserLogin.php';

class Getclassteacher extends Zend_Controller_Request_Http
{ 
	private $output = array(
		'status'	=> 0
	);
	
	protected static function getUserSectionTable()
	{
		return new Api_Model_UserSection();
	}
	
	protected static function getAssignSubjectTable()
	{
		return new Api_Model_AssignSubject();
	}
	
	protected static function getStaffTable()
	{
		return new Api_Model_Staff();
	}
	
	protected static function getUserLoginTable()
	{
		return new Api_Model_UserLogin();
	}
	
	public function getAction()
	{
		try {
			$user_id 	= $this->getParam('user_id');
			$school_id 	= $this->getParam('school_id');
			
			$assignedSections = self::getUserSectionTable()->getByUserId($user_id, $school_id);
       		
       		if ( !sizeof($assignedSections) )
       		{
       			throw new Exception('No section assigned to this teacher');
       		}
       		
       		foreach ( $assignedSections as $section)
       		{
       			$sections[] = $section['section_id'];
       		}
       		
			$assignedSubjects = self::getAssignSubjectTable()->getAssignedSubjectArray($sections, $user_id, $school_id);
       		
       		if ( !sizeof($assignedSubjects) )
       		{
       			throw new Exception('No subject assigned to this teacher');
       		}
       		
       		$teacher_id = self::getStaffTable()->getId($user_id);
       		$password   = self::getUserLoginTable()->get($school_id, $user_id);
       		$password   = $password->password;
       		
       		$curDate    = self::getUserLoginTable()->getCurDate();
       		
       		$this->output['status'] = 1;
       		foreach ( $assignedSubjects as $as)
       		{
       			$this->output['contents'][] = array(
       			'class_id'		=> $as['class_id'],
       			'class_name'	=> $as['class_name'],
       			'section_id'	=> $as['section_id'],
       			'section_name'	=> $as['group_name'],
       			'subject_id'	=> $as['subject_id'],
       			'subject_name'	=> $as['subject_name'],
       			'teacher_id'	=> $teacher_id,
       			'password'		=> $password,
       			'current_date'	=> $curDate
       			);
       		}
       		
		}
		catch (Exception $e)
		{
			$this->output['status']	= 0;
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
			
			$user_id 	= $this->getParam('user_id');
       		$school_id  = $this->getParam('school_id');
       		
       		$assignedSections = self::getUserSectionTable()->getByUserId($user_id, $school_id);
       		
       		if ( !sizeof($assignedSections) )
       		{
       			throw new Exception('No section assigned to this teacher');
       		}
       		
       		foreach ( $assignedSections as $section)
       		{
       			$sections[] = $section['section_id'];
       		}
       		
       		$assignedSubjects = self::getAssignSubjectTable()->getAssignedSubjectArray($sections, $user_id, $school_id);
       		
       		if ( !sizeof($assignedSubjects) )
       		{
       			throw new Exception('No subject assigned to this teacher');
       		}
       		
       		$this->output['status'] = 1;
       		foreach ( $assignedSubjects as $as)
       		{
       			$this->output['contents'][] = array(
       			'class_id'		=> $as['class_id'],
       			'class_name'	=> $as['class_name'],
       			'section_id'	=> $as['section_id'],
       			'section_name'	=> $as['group_name']
       			);
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
}