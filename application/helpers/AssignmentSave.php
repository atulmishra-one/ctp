<?php

include_once APPLICATION_PATH.'/models/AssignmentMaster.php';
include_once APPLICATION_PATH.'/models/AssignmentSysn.php';
include_once APPLICATION_PATH.'/models/GroupMember.php';
include_once APPLICATION_PATH.'/models/GroupTable.php';
include_once APPLICATION_PATH.'/models/GroupHomework.php';
include_once APPLICATION_PATH.'/models/NotificationType.php';
include_once APPLICATION_PATH.'/models/Notification.php';
include_once APPLICATION_PATH.'/helpers/Socket.php';
include_once APPLICATION_PATH.'/models/Student.php';

class Assignment_Save_Helper
{
	public function getStudentTable()
	{
		return $students = new Api_Model_Student();
	}
	public function getNotificationTypeId()
	{
		$notificationTypeTable = new Api_Model_NotificationType();
		return  $notificationTypeTable->get('ASSINGMENT');
	}
	
	
	public function getNotificationTable()
	{
		$notificationTable = new Api_Model_Notification();
		return $notificationTable;
	}
	
	public function getAssignmentMasterTable()
	{
		$assignmentMasterTable = new Api_Model_AssignmentMaster();
		return $assignmentMasterTable;
	}
	
	public function getAssignmentSyncTable()
	{
		$assignmentSyncTable = new Api_Model_AssignmentSysn();
		return $assignmentSyncTable;
	}
	
	public function getGroupMembersTable()
	{
		$groupMembersTable = new Api_Model_GroupMember();
		return $groupMembersTable;
	}
	
	public function getGroupTable()
	{
		$groupTable = new Api_Model_GroupTable();
		return $groupTable;
	}
	
	public function getGroupHomeworkTable()
	{
		$groupHomeworkTable = new Api_Model_GroupHomework();
		return $groupHomeworkTable;
	}
	
	public function saveGroupAssignment($input)
	{
		if (! isset( $input ) )
		{
			throw new Exception('Data not provided');
		}
		
		$input['status'] = 'Inactive';
		
		if ( false === ( $lastId = $this->getAssignmentMasterTable()->saveAssignment( $input) ) )
		{
			throw new Exception('Error ');
		}
		$this->getAssignmentSyncTable()->saveForTeacher($input['teacher_id'], $lastId);
		
		$mapGroupShare = array();
		
		$mapGroupShare[] = array(
           'class_id'      => $input['class_id'], 
           'section_id'    => $input['section_id'],
           'subject_id'    => $input['subject_id'],
           'aid'           => $lastId
		);
		
		$temp_class   = $input['class_id'];
		$temp_section = $input['section_id'];
		$temp_subject = $input['subject_id'];
		
		foreach ( $input['group_id'] as $group )
		{
			if ( isset( $group) )
			{
				$members = $this->getGroupMembersTable()->getMembersByGroupId( $group );
				
				$gInfo   = $this->getGroupTable()->getById( $group );
				
				if ( isset( $gInfo->class_id, $gInfo->section_id, $gInfo->subject_id) )
				{
					if ( $gInfo->class_id == $temp_class && $gInfo->section_id == $temp_section && $gInfo->subject_id == $temp_subject )
					{
						$this->getAssignmentMasterTable()->updateTopicStatus( $lastId);
						$this->getGroupHomeworkTable()->save( $lastId, $group);
						// SEND NOTIFICATION TO MEMBERS
						$this->sendNotificationToMember($members, $group, 
						$input['school_id'], $input['teacher_id'], $lastId);
					}
					else 
					{
						$matchId = null;
						
						foreach ( $mapGroupShare as $maps )
						{
						       if( $gInfo->class_id == $maps['class_id'] &&
									$gInfo->section_id == $maps['section_id'] &&
									$gInfo->subject_id == $maps['subject_id'])
									{

										$matchId = $maps['aid'];
										break;

									}
						}
						
						if ( !is_null( $matchId) )
						{
							$this->getAssignmentMasterTable()->updateTopicStatus( $matchId );
							$this->getGroupHomeworkTable()->save( $matchId, $group);
							$matchId = null;
							// SEND NOTIFICATION TO MEMBERS
							$this->sendNotificationToMember($members, $group, 
							$input['school_id'], $input['teacher_id'], $matchId);
						}
						else 
						{
							$input['class_id'] = $gInfo->class_id;
							$input['section_id'] = $gInfo->section_id;
							$input['subject_id'] = $gInfo->subject_id;
							
							$input['status'] = 'Active';
							$hid = $this->getAssignmentMasterTable()->saveAssignment( $input);
							
							$this->getAssignmentSyncTable()->saveForTeacher($input['teacher_id'], $hid);
							
							$mapGroupShare[] = array(
           					'class_id'      => $input['class_id'], 
           					'section_id'    => $input['section_id'],
           					'subject_id'    => $input['subject_id'],
           					'aid'           => $hid
							);
							
							$this->getGroupHomeworkTable()->save( $hid, $group);
							$matchId = null;
							// SEND NOTIFICATION TO MEMBERS
							$this->sendNotificationToMember($members, $group, 
							$input['school_id'], $input['teacher_id'], $hid);
						}
					}
				}
				
			}
		}
	}
	
	public function saveAssignmentForCLass( $input )
	{
		if ( !isset( $input['assigned_to']) )
		{
			throw new Exception('Assigned info not given');
		}
		
		foreach ( $input['assigned_to'] as $assigned )
		{
			if ( isset( $assigned['class_id'] , $assigned['section_id']) )
			{
				$input['class_id'] = $assigned['class_id'] ;
				$input['section_id'] = $assigned['section_id'];
				
				$lastId = $this->getAssignmentMasterTable()->saveAssignment( $input);
				if ( $lastId )
				{
					$this->getAssignmentSyncTable()->saveForTeacher($input['teacher_id'], $lastId);
					// SEND NOTIFICATION TO CLASS
					$this->sendNotificationToClass($input['school_id'], $assigned['class_id'], 
					$assigned['section_id'], $input['teacher_id'], $lastId);
				}
			}
		}
		
	}
	
	public function saveAssignment( $input )
	{
		if (! isset( $input))
		{
			throw new Exception('Input not given');
		}
		
		$input['status'] = 'Inactive';
		
		if ( false === ( $lastId = $this->getAssignmentMasterTable()->saveAssignment( $input) ) )
		{
			throw new Exception('Error cannot save');
		}
		
		$this->getAssignmentSyncTable()->saveForTeacher($input['teacher_id'], $lastId);
		
	}
	
	public function updateGroupAssignment( $input)
	{
	 if (! isset( $input ) )
		{
			throw new Exception('Data not provided');
		}
		
		$input['status'] = 'Inactive';
		
		if ( false === ( $this->getAssignmentMasterTable()->updateAssignment( $input) ) )
		{
			throw new Exception('Error ');
		}
		
		$mapGroupShare = array();
		
		$mapGroupShare[] = array(
           'class_id'      => $input['class_id'], 
           'section_id'    => $input['section_id'],
           'subject_id'    => $input['subject_id'],
           'aid'           => $input['assignment_id']
		);
		
		$temp_class   = $input['class_id'];
		$temp_section = $input['section_id'];
		$temp_subject = $input['subject_id'];
		
		foreach ( $input['group_id'] as $group )
		{
			if ( isset( $group) )
			{
				$members = $this->getGroupMembersTable()->getMembersByGroupId( $group );
				
				$gInfo   = $this->getGroupTable()->getById( $group );
				
				if ( isset( $gInfo->class_id, $gInfo->section_id, $gInfo->subject_id) )
				{
					if ( $gInfo->class_id == $temp_class && $gInfo->section_id == $temp_section && $gInfo->subject_id == $temp_subject )
					{
						$this->getAssignmentMasterTable()->updateTopicStatus( $input['assignment_id'] );
						$this->getGroupHomeworkTable()->save( $input['assignment_id'] , $group);
						// SEND NOTIFICATION TO MEMBERS
						$this->sendNotificationToMember($members, $group, 
						$input['school_id'], $input['teacher_id'], $input['assignment_id']);
					}
					else 
					{
						$matchId = null;
						
						foreach ( $mapGroupShare as $maps )
						{
						       if( $gInfo->class_id == $maps['class_id'] &&
									$gInfo->section_id == $maps['section_id'] &&
									$gInfo->subject_id == $maps['subject_id'])
									{

										$matchId = $maps['aid'];
										break;

									}
						}
						
						if ( !is_null( $matchId) )
						{
							$this->getGroupHomeworkTable()->save( $matchId, $group);
							$matchId = null;
							// SEND NOTIFICATION TO MEMBERS
							$this->sendNotificationToMember($members, $group, 
							$input['school_id'], $input['teacher_id'], $matchId);
						}
						else 
						{
							$input['class_id'] = $gInfo->class_id;
							$input['section_id'] = $gInfo->section_id;
							$input['subject_id'] = $gInfo->subject_id;
							
							$input['status'] = 'Active';
							$hid = $this->getAssignmentMasterTable()->saveAssignment( $input);
							
							$this->getAssignmentSyncTable()->saveForTeacher($input['teacher_id'], $hid);
							
							$mapGroupShare[] = array(
           					'class_id'      => $input['class_id'], 
           					'section_id'    => $input['section_id'],
           					'subject_id'    => $input['subject_id'],
           					'aid'           => $hid
							);
							
							$this->getGroupHomeworkTable()->save( $hid, $group);
							$matchId = null;
							// SEND NOTIFICATION TO MEMBERS
							$this->sendNotificationToMember($members, $group, 
							$input['school_id'], $input['teacher_id'], $hid);
						}
					}
				}
				
			}
		}
	}
	
    public function updateAssignmentForCLass( $input )
	{
		if ( !isset( $input['assigned_to']) )
		{
			throw new Exception('Assigned info not given');
		}
		
		foreach ( $input['assigned_to'] as $assigned )
		{
			if ( isset( $assigned['class_id'] , $assigned['section_id']) )
			{
				$assignClassSection = $this->getAssignmentMasterTable()
				->getByIdClassSection($input['assignment_id'], $assigned['class_id'], $assigned['section_id']);
				
				$input['class_id'] = $assigned['class_id'];
				$input['section_id'] = $assigned['section_id'];
					
				$assignClassSectionCount = sizeof( $assignClassSection);
				if ( $assignClassSectionCount )
				{
					$this->getAssignmentMasterTable()->updateAssignment($input);
					$this->getAssignmentSyncTable()->saveForTeacher($input['teacher_id'], $input['assignment_id'] );
					// SEND NOTIFICATION TO CLASS
					$this->sendNotificationToClass($input['school_id'], $assigned['class_id'], 
					$assigned['section_id'], $input['teacher_id'], $input['assignment_id']);
				}
				else 
				{
					$lastId = $this->getAssignmentMasterTable()->saveAssignment($input);
					$this->getAssignmentSyncTable()->saveForTeacher($input['teacher_id'], $lastId);
					// SEND NOTIFICATION TO CLASS
					$this->sendNotificationToClass($input['school_id'], $assigned['class_id'], 
					$assigned['section_id'], $input['teacher_id'], $lastId);
					
				}
			}
		}
		
	}
	
	public function updateAssignment( $input )
	{
		if ( !isset($input)) 
		{
			throw new Exception('Input not found for assignment update');
		}
		
		if( false === ( $this->getAssignmentMasterTable()->updateAssignment($input) ) )
		{
			throw new Exception('Error cannot save');
		}
		
		$this->getAssignmentSyncTable()->saveForTeacher( $input['teacher_id'], $input['assignment_id'] );
		
	}
	
	public function sendNotificationToMember( $members , $group_id, $school_id, $teacher_id , $assignment_id)
	{
		if ( sizeof( $members) >= 1)
		{
			foreach ( $members as $member )
			{
				$class_id   = (int)$this->getGroupTable()->getInfoById( $group_id, 'class_id');
				$section_id = (int)$this->getGroupTable()->getInfoById( $group_id, 'section_id');
				
				$this->getNotificationTable()->saveForStudent( array(
					'type_id' 		   => $this->getNotificationTypeId(),
					'school_id'    => $school_id,
					'class_id'     => $class_id,
					'section_id'   => $section_id,
					'student_id'   => $member,
					'notify_by'    => 'Teacher',
					'notify_by_id' => $teacher_id
				));
				
				$this->getAssignmentSyncTable()->save( array(
				 'student_id'			 => $member,
				 'assignment_master_id' => $assignment_id
				));
			}
			
			Socket_Helper::write( array(
			 'class_id'   => $class_id,
			 'section_id' => $section_id,
			 'teacher_id' => 0
			));
		}
	}
	
	public function sendNotificationToClass( $school_id, $class_id, $section_id, $teacher_id, $assignment_id )
	{	
		$student_list = $this->getStudentTable()->getStudentList($school_id, $class_id, $section_id);
		
		if ( sizeof($student_list) >= 1) {
			
			foreach ( $student_list as $student )
			{
				$this->getNotificationTable()->saveForStudent( array(
				 'type_id'		=> $this->getNotificationTypeId(),
				 'school_id'	=> $school_id,
				 'class_id'		=> $class_id,
				 'section_id'	=> $section_id,
				 'student_id'	=> $student['sid'],
				 'notify_by'	=> 'Teacher',
				 'notify_by_id' => $teacher_id
				));
				
				$this->getAssignmentSyncTable()->save( array(
				 'student_id'			 => $student['sid'],
				 'assignment_master_id' => $assignment_id
				));
			}
			
			Socket_Helper::write( array(
			 'class_id'   => $class_id,
			 'section_id' => $section_id,
			 'teacher_id' => 0
			));
		}
	}
}




