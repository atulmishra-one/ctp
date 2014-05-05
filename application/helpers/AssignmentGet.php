<?php

include_once APPLICATION_PATH.'/models/GroupHomework.php';
include_once APPLICATION_PATH.'/models/GroupTable.php';
include_once APPLICATION_PATH.'/models/GroupMember.php';
include_once APPLICATION_PATH.'/models/Student.php';
include_once APPLICATION_PATH.'/helpers/Attachment.php';
include_once APPLICATION_PATH.'/models/AssignmentMaster.php';
include_once APPLICATION_PATH.'/models/AssignmentStudent.php';
include_once APPLICATION_PATH.'/models/UserSection.php';
include_once APPLICATION_PATH.'/models/AssignmentRemark.php';

class Assignment_Get_Helper
{
	public function getGroupAssignmentsId($id)
	{
		$groupAssignments = new Api_Model_GroupHomework();
		return $groupAssignments->getGroupId($id);
	}
	
	public function getRemark($aid, $sid)
	{
		$remarkTable = new Api_Model_AssignmentRemark();
		return $remarkTable->getByAssignmentAndStudent($aid, 'remark', $sid);
	}
	
	public function getRemarkTable()
	{
		$remarkTable = new Api_Model_AssignmentRemark();
		return $remarkTable;
	}
	public function getRollNo( $user_id )
	{
		$userSectionTable = new Api_Model_UserSection();
		return (int)$userSectionTable->getRollNo($user_id);
	}
	
	public function getAssignmentMasterTable()
	{
		$assignmentMasterTable = new Api_Model_AssignmentMaster();
		return $assignmentMasterTable;
	}
	
	public function getAssignStudentTable()
	{
		$assignStudentTable = new Api_Model_AssignmentStudent();
		return $assignStudentTable;
	}
	
	public function getGroupTable()
	{
		$groupTable = new Api_Model_GroupTable();
		return $groupTable; 
	}
	
	public function getGroupMemberTable()
	{
		$groupMemberTable = new Api_Model_GroupMember();
		return $groupMemberTable;
	}
	
	public function getStudentTable()
	{
		$studentTable = new Api_Model_Student();
		return $studentTable;
	}
	
	public function assignStatus($aid, $sid, $date)
	{
		if ( !$this->getAssignStudentTable()->isPending($aid, $sid) )
		{
			$status = 'Pending';
		}
		elseif ( $this->getAssignStudentTable()->isSumitted($aid, $sid, $date) )
		{
			$status = 'Submitted';
		}
		elseif ( $this->getAssignStudentTable()->isLate($aid, $sid, $date) )
		{
			$status = 'Late';
		}
		
		return $status;
	}
	
	public function getGroupAssignments( $data )
	{
		$groupAssignments = self::getGroupAssignmentsId( $data['assignment_master_id'] );
		
		$output = array();
		
		if ( sizeof($groupAssignments) >= 1) {
			
			foreach ( $groupAssignments as $ga )
			{
				if ( isset( $ga) )
				{
					$group_name[] = $this->getGroupTable()->getInfoById($ga['group_id'], 'ctp_group_name');
					$output['contents']['group'] = array( implode(',', $group_name));
					
					$group_members = $this->getGroupMemberTable()->getMembersByGroupId( $ga['group_id'] );
					
					if ( sizeof( $group_members) >= 1)
					{
						foreach ( $group_members as $member )
						{
							$user_id = $this->getStudentTable()->getInfoById($member, 'user_id');
							$name    = $this->getStudentTable()->getInfoById($member, 'fname').' '.
									   $this->getStudentTable()->getInfoById($member, 'lname');
							$target_date = $this->getAssignmentMasterTable()->getSubmissionDate(  $data['assignment_master_id'] );
							
							$status = $this->assignStatus($data['assignment_master_id'], $member, $target_date);
							
							$attachments = $this->getAssignStudentTable()
							->getInfoById($data['assignment_master_id'], $member, 'upload_file');
							
							$attachmentsFiles = Attachment_Helper::getAttachment($attachments);
							
							$content = $this->getAssignStudentTable()
							->getInfoById($data['assignment_master_id'], $member, 'content');
							
							$subject_id = $this->getAssignmentMasterTable()->getInfoById($data['assignment_master_id'], 'subject_id');
							
							$submittedDate = $this->getAssignStudentTable()
							->getInfoById($data['assignment_master_id'], $member, 'submission_date');
							
							$output['status'] = 1;
							$output['contents']['student_info'][] = array(
							 'submitted_date'	=> $submittedDate,
							 'roll_no'			=> self::getRollNo($user_id),
							 'student_name'		=> $name,
							 'status'			=> $status,
							 'remark'			=> self::getRemark($data['assignment_master_id'], $member),
							 'attachments'		=> $attachmentsFiles,
							 'content'			=> $content,
							 'student_id'		=> $member,
							 'subject_id'		=> $subject_id,
							 'student_group'	=> $group_name
							);
							
						}
					}else {
						break;
					}
				}
			}
		}
		else
		{
			$assigns = $this->getAssignmentMasterTable()->getById( $data['assignment_master_id'] , $data['school_id']);
			
			if ( sizeof($assigns) >= 1)
			{
				$class_id   = $assigns->class_auto_id;
				$section_id = $assigns->section_id;
				
				$student_list = $this->getStudentTable()->getStudentList($data['school_id'], $class_id, $section_id);
				
				if ( sizeof($student_list) >= 1)
				{
					foreach ( $student_list as $student) 
					{
							$user_id = $this->getStudentTable()->getInfoById($student['sid'], 'user_id');
							$name    = $this->getStudentTable()->getInfoById($student['sid'], 'fname').' '.
									   $this->getStudentTable()->getInfoById($student['sid'], 'lname');
							$target_date = $this->getAssignmentMasterTable()->getSubmissionDate(  $data['assignment_master_id'] );
							
							$status = $this->assignStatus($data['assignment_master_id'], $student['sid'], $target_date);
							
							$attachments = $this->getAssignStudentTable()
							->getInfoById($data['assignment_master_id'], $student['sid'], 'upload_file');
							
							$attachmentsFiles = Attachment_Helper::getAttachment($attachments);
							
							$content = $this->getAssignStudentTable()
							->getInfoById($data['assignment_master_id'], $student['sid'], 'content');
							
							$subject_id = $this->getAssignmentMasterTable()->getInfoById($data['assignment_master_id'], 'subject_id');
							
							$submittedDate = $this->getAssignStudentTable()
							->getInfoById($data['assignment_master_id'], $student['sid'], 'submission_date');
							
							$output['status'] = 1;
							$output['contents']['group'] = array();
							$output['contents']['student_info'][] = array(
								'submitted_date' => $submittedDate,
							    'roll_no'		 => self::getRollNo($user_id),
								'student_name'	 => $name,
								'status'		 => $status,
								'remark'		 => self::getRemark($data['assignment_master_id'], $student['sid']),
								'attachments'	 => $attachmentsFiles,
								'content'		 => $content,
								'student_id'	 => $student['sid'],
								'subject_id'	 => $subject_id,
								'marks'			 => $this->getRemarkTable()
													->getByAssignmentAndStudent($data['assignment_master_id'], 'marks', $student['sid']),
								'show_remarks'	 => $this->getRemarkTable()
													->getByAssignmentAndStudent($data['assignment_master_id'], 'show_remarks', $student['sid']),
								'show_marks'	 => $this->getRemarkTable()
													->getByAssignmentAndStudent($data['assignment_master_id'], 'show_marks', $student['sid'])
							);
							
					}
				}
			}
		}
		
		return $output;
	}
	
	
}