<?php

include_once APPLICATION_PATH.'/models/Student.php';
include_once APPLICATION_PATH.'/models/GroupMember.php';
include_once APPLICATION_PATH.'/models/GroupTable.php';

class Groupmember extends Zend_Controller_Request_Http
{ 
	private $output = array(
	'status'	=> 0
	);
	
	
	protected static function getStudentTable()
	{
		return new Api_Model_Student();
	}
	
	protected static function getGroupMemberTable()
	{
		return new Api_Model_GroupMember();
	}
	
	protected static function getGroupTable()
	{
		return new Api_Model_GroupTable();
	}
	
	public function getAction()
	{
		try {
			$group_owner_id 	= (int)$this->getParam('group_owner_id');
	   		$group_owner_type 	= (string)$this->getParam('group_owner_type');
	  		$school_id 			= (int)$this->getParam('school_id');
	   		$class_id 			= (int)$this->getParam('class_id');
	   		$section_id 		= (int)$this->getParam('section_id');
	   		$subject_id 		= (int)$this->getParam('subject_id');
	   		
	   		if ( empty($school_id) or empty($class_id) or empty($section_id) )
	   		{
	   			throw new Exception('Please school_id class_id and section_id');
	   		}
	   		
	   		$student_list = self::getStudentTable()->getStudentListIds($school_id, $class_id, $section_id);
	   		
	   		foreach ( $student_list as $st )
	   		{
	   			$studentIDs[] = $st['student_id'];
	   		}
	   		
	   		$studentInfo = self::getStudentTable()->getFnameLname($studentIDs);
	   		
	   		
	   		$this->output['status'] = 1;
	   		foreach ( $studentInfo as $student )
	   		{
	   			$isMembers = self::getGroupMemberTable()
	   			->getNonMembers($student['id'], $group_owner_type, $group_owner_id, $school_id, $class_id, $section_id, $subject_id);
	   			
	   			if ( !$isMembers)
	   			{
	   				$this->output['contents'][] = array(
	   				'student_id'	=> $student['id'],
	   				'fname'			=> $student['fname'],
	   				'lname'			=> $student['lname'],
	   				'roll_no'		=> (int)$student['roll_no']
	   				);
	   			}
	   		}
	   		
	   		
	   		
	   		
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
	
	public function indexAction()
	{
		try {
			$student_id = $this->getParam('student_id');
        	$user_id 	= $this->getParam('user_id');
        	
        	if ( empty( $student_id) )
        	{
        		throw new Exception('Please provide student_id');
        	}
        	
        	$members = self::getGroupMemberTable()->getMembersByMemberId($student_id, $user_id);
        	
        	if ( !sizeof($members) )
        	{
        		throw new Exception('No members');
        	}
        	
        	$this->output['status'] = 1;
        	foreach ( $members as $member )
        	{
        		$this->output['contents'][] = array(
        		'group_name'	=> self::getGroupTable()->getById($member['ctp_group_id'])->ctp_group_name,
        		'date_joined'	=> $member['date_joined'],
        		'date_exit'		=> $member['date_exit']
        		);
        	}
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
	
	public function deleteAction()
	{
		try {
			$group_id 	= $this->getParam('group_id');
			$student_id = $this->getParam('student_id');
			
			if ( self::getGroupMemberTable()->getCountByGroupIdAndExitDate($group_id) == 1 )
			{
				self::getGroupTable()->updateGroupStatus('INACTIVE', $group_id);
			}
			
			self::getGroupTable()->updateDateCreated($group_id);
			
			self::getGroupMemberTable()->removeMember($group_id, $student_id);
			
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