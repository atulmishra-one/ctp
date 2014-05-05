<?php

include_once APPLICATION_PATH.'/models/GroupTable.php';
include_once APPLICATION_PATH.'/models/GroupMember.php';
include_once APPLICATION_PATH.'/models/Student.php';
include_once APPLICATION_PATH.'/models/UserSection.php';
include_once APPLICATION_PATH.'/models/GetClass.php';
include_once APPLICATION_PATH.'/models/GetSection.php';
include_once APPLICATION_PATH.'/models/GetSubject.php';
include_once APPLICATION_PATH.'/models/GroupHomework.php';
include_once APPLICATION_PATH.'/models/GroupNotes.php';


class Group extends Zend_Controller_Request_Http
{ 
	private $output = array(
		'status'	=> 0
	);
	
	protected static function getGroupHomeWorkTable()
	{
		return new Api_Model_GroupHomework();
	}
	
	protected static function getGroupNotesTable()
	{
		return new Api_Model_GroupNotes();
	}
	
	protected static function getClassTable()
	{
		return new Api_Model_GetClass();
	}
	
	protected static function getSectionTable()
	{
		return new Api_Model_GetSection();
	}
	
	protected static function getSubjectTable()
	{
		return new Api_Model_GetSubject();
	}
	
	protected static function getGroupTable()
	{
		return new Api_Model_GroupTable();
	}
	
	protected static function getGroupMemberTable()
	{
		return new Api_Model_GroupMember();
	}
	
	protected static function getStudentTable()
	{
		return new Api_Model_Student();
	}
	
	protected static function getUserSectionTable()
	{
		return new Api_Model_UserSection();
	}
	
	public function postAction()
	{
		try {
			$member_ids 		= $this->getParam('member_ids');
	   		$member_ids 		= json_decode( $member_ids, true); // [1,2,3,4] json data
	   		$group_name 		= (string)$this->getParam('group_name');
	   		$group_owner_id 	= (int)$this->getParam('group_owner_id');
	   		$group_owner_type 	= (string)$this->getParam('group_owner_type');
	   		$group_status 		= (string)$this->getParam('group_status');
	   		$school_id 			= (int)$this->getParam('school_id');
	   		$class_id 			= (int)$this->getParam('class_id');
	   		$section_id 		= (int)$this->getParam('section_id');
	   		$subject_id 		= (int)$this->getParam('subject_id');
	   		
	   		$isGroupExists = self::getGroupTable()
	   		->isGroupExitsForMember($school_id, $class_id, $section_id, $group_owner_id, $subject_id, $group_name);
	   		
	   		if ( $isGroupExists > 0)
	   		{
	   			throw new Exception('Group name already exist!');
	   		}
	   		
	   		$lastId = self::getGroupTable()->save( array(
	   		'group_name'		=> $group_name,
	   		'group_owner_id'	=> $group_owner_id,
	   		'group_owner_type'	=> $group_owner_type,
	   		'group_status'		=> $group_status,
	   		'class_id'			=> $class_id,
	   		'section_id'		=> $section_id,
	   		'subject_id'		=> $subject_id,
	   		'school_id'			=> $school_id
	   		));
	   		
	   		self::getGroupMemberTable()->saveBatch( array(
	   		'member_id' => $member_ids,
	   		'id'		=> $lastId
	   		));
	   		
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
	
	public function getAction()
	{
		try {
			$group_id = $this->getParam('group_id');
			
			$members = self::getGroupMemberTable()->getMembersAndDateJoinedByGroupId($group_id);
			
			if ( !sizeof($members) )
			{
				throw new Exception('Invalid group_id');
			}
			
			$this->output['status'] = 1;
			foreach ( $members as $member )
			{
				$this->output['contents'][] = array(
				'student_id'	=> $member['member_id'],
				'fname'			=> self::getStudentTable()->getInfoById($member['member_id'], 'fname'),
				'lname'			=> self::getStudentTable()->getInfoById($member['member_id'], 'lname'),
				'roll_no'		=> self::getUserSectionTable()->getRollNo(self::getStudentTable()->getInfoById($member['member_id'], 'user_id')),
				'date_joined'	=> $member['date_joined']
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
	
	public function indexAction()
	{
		try {
			$school_id  = $this->getParam('school_id');
	   		$class_id   = $this->getParam('class_id');
	   		$section_id = $this->getParam('section_id');
	   		$subject_id = $this->getParam('subject_id');
	   
	   		$owner_id   = (int)$this->getParam('owner_id');
	   		$owner_type = $this->getParam('owner_type');
	   		
	   		if ( empty( $school_id) or empty($owner_id))
	   		{
	   			throw new Exception('Please provide school_id and owner_id');
	   		}
	   		
	   		// BREADCRUMB
	   		if( empty( $class_id) and empty( $section_id) and empty( $subject_id) )
	   		{
	   			$groups = self::getGroupTable()->getResult( array(
	   			'school_id'	=> $school_id,
	   			'owner_id'	=> $owner_id,
	   			'owner_type'=> $owner_type
	   			));
	   			
	   			if ( !sizeof($groups) )
	   			{
	   				throw new Exception('No records');
	   			}
	   			
	   			$this->output['status'] = 1;
	   			foreach ( $groups as $group )
	   			{
	   				$group['studentcount'] = self::getGroupMemberTable()->getCountByGroupIdAndExitDate($group['ctp_group_id']);
	   				$this->output['contents'][] = self::output( $group);
	   			}
	   			
	   		}
	   		else if( empty( $class_id) and empty( $section_id) and !empty( $subject_id) )
	   		{
	   			$groups = self::getGroupTable()->getResult( array(
	   			'school_id'	=> $school_id,
	   			'owner_id'	=> $owner_id,
	   			'owner_type'=> $owner_type,
	   			'subject_id'=> $subject_id
	   			));
	   			
	   			if ( !sizeof($groups) )
	   			{
	   				throw new Exception('No records');
	   			}
	   			
	   			$this->output['status'] = 1;
	   			foreach ( $groups as $group )
	   			{
	   				$group['subject_id'] = '';
	   				$group['studentcount'] = self::getGroupMemberTable()->getCountByGroupIdAndExitDate($group['ctp_group_id']);
	   				$this->output['contents'][] = self::output( $group);
	   			}
	   		}
	   		else if( empty( $class_id) and !empty( $section_id) and empty( $subject_id) )
	   		{
	   			$groups = self::getGroupTable()->getResult( array(
	   			'school_id'	=> $school_id,
	   			'owner_id'	=> $owner_id,
	   			'owner_type'=> $owner_type,
	   			'section_id'=> $section_id
	   			));
	   			
	   			if ( !sizeof($groups) )
	   			{
	   				throw new Exception('No records');
	   			}
	   			
	   			$this->output['status'] = 1;
	   			foreach ( $groups as $group )
	   			{
	   				$group['section_id'] = '';
	   				$group['studentcount'] = self::getGroupMemberTable()->getCountByGroupIdAndExitDate($group['ctp_group_id']);
	   				$this->output['contents'][] = self::output( $group);
	   			}
	   		}
	   		else if( empty( $class_id) and !empty( $section_id) and !empty( $subject_id) )
	   		{
	   			$groups = self::getGroupTable()->getResult( array(
	   			'school_id'	=> $school_id,
	   			'owner_id'	=> $owner_id,
	   			'owner_type'=> $owner_type,
	   			'section_id'=> $section_id,
	   			'subject_id'=> $subject_id
	   			));
	   			
	   			if ( !sizeof($groups) )
	   			{
	   				throw new Exception('No records');
	   			}
	   			
	   			$this->output['status'] = 1;
	   			foreach ( $groups as $group )
	   			{
	   				$group['section_id'] = '';
	   				$group['subject_id'] = '';
	   				$group['studentcount'] = self::getGroupMemberTable()->getCountByGroupIdAndExitDate($group['ctp_group_id']);
	   				$this->output['contents'][] = self::output( $group);
	   			}
	   		}
	   		else if( !empty( $class_id) and empty( $section_id) and empty( $subject_id) )
	   		{
	   			$groups = self::getGroupTable()->getResult( array(
	   			'school_id'	=> $school_id,
	   			'owner_id'	=> $owner_id,
	   			'owner_type'=> $owner_type,
	   			'class_id'  => $class_id
	   			));
	   			
	   			if ( !sizeof($groups) )
	   			{
	   				throw new Exception('No records');
	   			}
	   			
	   			$this->output['status'] = 1;
	   			foreach ( $groups as $group )
	   			{
	   				$group['class_id'] = '';
	   				$group['studentcount'] = self::getGroupMemberTable()->getCountByGroupIdAndExitDate($group['ctp_group_id']);
	   				$this->output['contents'][] = self::output( $group);
	   			}
	   		}
	   		else if( !empty( $class_id) and empty( $section_id) and !empty( $subject_id) )
	   		{
	   			$groups = self::getGroupTable()->getResult( array(
	   			'school_id'	=> $school_id,
	   			'owner_id'	=> $owner_id,
	   			'owner_type'=> $owner_type,
	   			'class_id'	=> $class_id,
	   			'subject_id'=> $subject_id
	   			));
	   			
	   			if ( !sizeof($groups) )
	   			{
	   				throw new Exception('No records');
	   			}
	   			
	   			$this->output['status'] = 1;
	   			foreach ( $groups as $group )
	   			{
	   				$group['class_id'] = '';
	   				$group['subject_id'] = '';
	   				$group['studentcount'] = self::getGroupMemberTable()->getCountByGroupIdAndExitDate($group['ctp_group_id']);
	   				$this->output['contents'][] = self::output( $group);
	   			}
	   		}
	   		else if( !empty( $class_id) and !empty( $section_id) and empty( $subject_id) )
	   		{
	   			$groups = self::getGroupTable()->getResult( array(
	   			'school_id'	=> $school_id,
	   			'owner_id'	=> $owner_id,
	   			'owner_type'=> $owner_type,
	   			'class_id'	=> $class_id,
	   			'section_id'=> $section_id
	   			));
	   			
	   			if ( !sizeof($groups) )
	   			{
	   				throw new Exception('No records');
	   			}
	   			
	   			$this->output['status'] = 1;
	   			foreach ( $groups as $group )
	   			{
	   				$group['section_id'] = '';
	   				$group['class_id']	 = '';
	   				$group['studentcount'] = self::getGroupMemberTable()->getCountByGroupIdAndExitDate($group['ctp_group_id']);
	   				$this->output['contents'][] = self::output( $group);
	   			}
	   		}
	   		else if( !empty( $class_id) and !empty( $section_id) and !empty( $subject_id) )
	   		{
	   			$groups = self::getGroupTable()->getResult( array(
	   			'school_id'	=> $school_id,
	   			'owner_id'	=> $owner_id,
	   			'owner_type'=> $owner_type,
	   			'class_id'	=> $class_id,
	   			'section_id'=> $section_id,
	   			'subject_id'=> $subject_id
	   			));
	   			
	   			if ( !sizeof($groups) )
	   			{
	   				throw new Exception('No records');
	   			}
	   			
	   			$this->output['status'] = 1;
	   			foreach ( $groups as $group )
	   			{
	   				$group['class_id']	= '';
	   				$group['section_id'] = '';
	   				$group['subject_id'] = '';
	   				$group['studentcount'] = self::getGroupMemberTable()->getCountByGroupIdAndExitDate($group['ctp_group_id']);
	   				$this->output['contents'][] = self::output( $group);
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
	
	public function putAction()
	{
		try {
		 $group_id = (int)$this->getParam('group_id');
		 $member_ids = $this->getParam('member_ids');
	     $member_ids = json_decode($member_ids, true); // [1,2,3,4] json data
		 $group_name = (string)$this->getParam('group_name');
		 
		 if ( empty($group_id) or empty($group_name))
		 {
		 	throw new Exception('Please provide group_id and group_name');
		 }
		 
		 $groupInfo = self::getGroupTable()->getById($group_id);
		 
		 self::getGroupTable()->updateGroupName($group_name, $group_id);
		 
		 if ( sizeof($member_ids) )
		 {
		 	foreach ( $member_ids as $member_id )
		 	{
		 		if ( ! self::getGroupMemberTable()->isInGroupMid($member_id, $group_id) )
		 		{
		 			self::getGroupMemberTable()->save( array(
		 			'member_id'	=> $member_id,
		 			'id'		=> $group_id
		 			));
		 		}
		 	}
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
	
	public function deleteAction()
	{
		try {
		 $group_id = $this->getParam('group_id');
		
		 if ( self::getGroupHomeWorkTable()->hasAssignment($group_id) || self::getGroupNotesTable()->hasNotes($group_id) )
		 {
		 	self::getGroupTable()->setInactive($group_id);
		 	$this->output['status'] = 1;
			$this->output['message'] = 'success';
			$this->output['toast_message'] = 'This group has records against it,so marked as inactive';
		 }
		 else 
		 {
		 	self::getGroupTable()->removeGroup($group_id);
		 	self::getGroupMemberTable()->removeForEver($group_id);
		 	$this->output['status'] = 1;
			$this->output['message'] = 'success';
			$this->output['toast_message'] = 'Group removed successfully';
		 }
			
			
		}
		catch (Exception $e)
		{
			$this->output['status']	   	= 0;
			$this->output['message']  	= $e->getMessage();
		}
		
		$response = new Response();
		$response->getResponse()
		->setHttpResponseCode(200)
		->setHeader( 'Content-Type', 'application/json' )
		->appendBody( json_encode( $this->output ) );
	}
	
	protected static function output( $data)
	{
		$members = self::getGroupMemberTable()->getMembersByGroupId($data['ctp_group_id']);
		
		return array(
		'group_id'		=> $data['ctp_group_id'],
		'group_name'	=> $data['ctp_group_name'],
		'date_created'	=> $data['ctp_group_date_created'],
		'status'		=> $data['ctp_group_status'],
		'class_id'		=> (int)$data['class_id'],
		'class_name'	=> self::getClassTable()->getName($data['school_id'], (int)$data['class_id']),
		'section_id'	=> $data['section_id'],
		'section_name'	=> (string)self::getSectionTable()->getName($data['school_id'], (int)$data['section_id']),
		'subject_id'	=> (int)$data['subject_id'],
		'subject_name'	=> (string)self::getSubjectTable()->getName((int)$data['subject_id']),
		'studentcount'	=> $data['studentcount'],
		'members'		=> implode(',',$members)
		);
	}
}
