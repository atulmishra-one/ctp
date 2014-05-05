<?php 

include_once APPLICATION_PATH.'/models/Staff.php';
include_once APPLICATION_PATH.'/models/GroupTable.php';
include_once APPLICATION_PATH.'/models/GroupMember.php';
include_once APPLICATION_PATH.'/models/GetSubject.php';
include_once APPLICATION_PATH.'/models/Student.php';


class Studentgroup extends Zend_Controller_Request_Http
{	
	private $output = array(
	'status'	=> 0
	);
	
	protected static function getGroupTable()
	{
		return new Api_Model_GroupTable();
	}
	
	protected static function getGroupMemberTable()
	{
		return new Api_Model_GroupMember();
	}
	
	protected static function getStaffTable()
	{
		return new Api_Model_Staff();
	}
	
	protected static function getStudentTable()
	{
		return new Api_Model_Student();
	}
	protected static function getSubjectTable()
	{
		return new Api_Model_GetSubject();
	}
	
	public function indexAction()
	{
		try {
			$student_id = $this->getParam('student_id');
			
			if ( empty($student_id) )
			{
				throw new Exception('Please provide student_id');
			}
			
			$myGroupsId = self::getGroupMemberTable()->getActiveGroupByMemberById($student_id);
			
			$groups = self::getGroupTable()->getAllGroupArray($myGroupsId);
			
			foreach ( $groups as $group )
			{
				$members = self::getGroupMemberTable()->getMembersByGroupId($group['ctp_group_id']);
				
				$membersNames = array();
				
				if ( sizeof($members) )
				{
					foreach ( $members as $member )
					{
						if ( $student_id != $member )
						{
							$membersNames[] = self::getStudentTable()->getFullname($member);
						}
					}
				}
				
				$this->output['status'] = 1;
				$this->output['contents'][] = array(
				'group_name'	=> $group['ctp_group_name'],
				'group_id'		=> $group['ctp_group_id'],
				'teacher_name'	=> self::getStaffTable()->getFullname($group['ctp_group_owner_id'], true),
				'subject_name'	=> self::getSubjectTable()->getName($group['subject_id']),
				'joined_date'	=> self::getGroupMemberTable()->getDateJoined($group['ctp_group_id'], $student_id),
				'group_members'	=> implode(',', $membersNames)
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
}