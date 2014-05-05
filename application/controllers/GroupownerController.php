<?php

include_once APPLICATION_PATH.'/models/UserSection.php';
include_once APPLICATION_PATH.'/models/AssignSubject.php';
include_once APPLICATION_PATH.'/models/GroupTable.php';
include_once APPLICATION_PATH.'/models/GetClass.php';
include_once APPLICATION_PATH.'/models/GetSection.php';

class Groupowner extends Zend_Controller_Request_Http
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
	
	protected static function getGroupTable()
	{
		return new Api_Model_GroupTable();
	}
	
	protected static function getClassTable()
	{
		return new Api_Model_GetClass();
	}
	
	protected static function getSectionTable()
	{
		return new Api_Model_GetSection();
	}
	
	public function indexAction()
	{
		try {
		 	$user_id 	= (int)$this->getParam('user_id');
		 	$school_id 	= (int)$this->getParam('school_id');
		 	
		 	if ( empty($user_id) or empty($school_id) )
		 	{
		 		throw new Exception('Please provide user_id and school_id');
		 	}
		 	
		 	$assignedClassSections = self::getUserSectionTable()->getByUserId($user_id, $school_id);
		 	
			if ( !sizeof($assignedClassSections) )
       		{
       			throw new Exception('Not valid teacher');
       		}
       		
       		$this->output['status'] = 1;
       		foreach ( $assignedClassSections as $ClassSection)
       		{
       			$groups = self::getGroupTable()
       			->getBySchoolOwnerIdTypeClassSection($school_id, $user_id, 'TEACHER', $ClassSection['class_id'], $ClassSection['section_id']);
       			
       			if ( sizeof($groups) )
       			{
       				foreach ( $groups as $group )
       				{
       					$this->output['contents'][] = array(
       					'class_id'		=> $ClassSection['class_id'],
       					'class_name'	=> self::getClassTable()->getName($school_id, $ClassSection['class_id']),
       					'section_id'	=> $ClassSection['section_id'],
       					'section_name'	=> self::getSectionTable()->getName($school_id, $ClassSection['section_id']),
       					'group_id'		=> $group['ctp_group_id'],
       					'group_name'	=> $group['ctp_group_name']
       					);
       				}
       			}
       			
       			$this->output['contents'][] = array(
       			'class_id'		=> $ClassSection['class_id'],
       			'class_name'	=> self::getClassTable()->getName($school_id, $ClassSection['class_id']),
       			'section_id'	=> $ClassSection['section_id'],
       			'section_name'	=> self::getSectionTable()->getName($school_id, $ClassSection['section_id']),
       			'group_id'		=> '',
       			'group_name'	=> ''
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
	
	
}