<?php 
include_once APPLICATION_PATH.'/helpers/Socket.php';
include_once APPLICATION_PATH.'/helpers/Attachment.php';
include_once APPLICATION_PATH.'/models/AssignmentMaster.php';
include_once APPLICATION_PATH.'/models/AssignmentStudent.php';
include_once APPLICATION_PATH.'/models/NotificationType.php';
include_once APPLICATION_PATH.'/models/Notification.php';
include_once APPLICATION_PATH.'/models/GetSubject.php';
include_once APPLICATION_PATH.'/models/Staff.php';
include_once APPLICATION_PATH.'/models/AssignmentRemark.php';
include_once APPLICATION_PATH.'/models/AssignmentSysn.php';


class Studentassignment extends Zend_Controller_Request_Http
{ 
	private $output = array(
	'status'	=> 0
	);
	
	protected static function getSubjectTable()
	{
		return new Api_Model_GetSubject();
	}
	
	protected static function getStaffTable()
	{
		return new Api_Model_Staff();
	}
	
	protected static function getAssignmentRemarkTable()
	{
		return new Api_Model_AssignmentRemark();
	}
	
	protected static function getAssignmentMasterTable()
	{
		return new Api_Model_AssignmentMaster();
	}
	
	protected static function getStudentAssignmentTable()
	{
		return new Api_Model_AssignmentStudent();
	}
	
	protected static function getAssignmentSyncTable()
	{
		return new Api_Model_AssignmentSysn();
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
			$data = $this->getParam('data');
			$data = json_decode($data, true);
			
			$path = (string)$this->getParam('path');
			
			if( is_null($path ) or empty($path) )
			$path = '';
			else
			$path = (string)$path;
			
			if ( empty($data['assignment_master_id']) )
			{
				throw new Exception('Please provide assignment_master_id');
			}
			
			$data['teacher_id'] = self::getAssignmentMasterTable()->getInfoById($data['assignment_master_id'], 'staff_id');
			
			$data['attachments'] = Attachment_Helper::makeAttachment($data['attachments'], $path);
			
			$assignment_student_id = self::getStudentAssignmentTable()->save($data);
			
			self::getAssignmentSyncTable()->setOFFTeacher($data['assignment_master_id'], $data['teacher_id']);
			
			self::getNotifyTable()->saveForTeacher( array(
			'type_id'		=> self::getNotifyTypeTable()->get('ASSIGNMENTSUBMITTED'),
			'school_id'		=> $data['school_id'],
			'class_id'		=> $data['class_id'],
			'section_id'	=> $data['section_id'],
			'teacher_id'	=> $data['teacher_id'],
			'notify_by_id'	=> $data['student_id'],
			'notify_by'		=> 'Student'
			));
			
			Socket_Helper::write( array(
			'class_id'	 => 0,
			'section_id' => 0,
			'teacher_id' => $data['teacher_id']
			));
			
			$this->output['status'] = 1;
			$this->output['message'] = 'success';
			$this->output['contents'][] = array(
			'assignment_student_id'	=> $assignment_student_id
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
	
	public function getAction()
	{
		try {
			$student_id 			= $this->getParam('student_id');
       		$assignment_master_id 	= $this->getParam('assignment_master_id');
       		
       		if ( empty($student_id) or empty($assignment_master_id) )
       		{
       			throw new Exception('Please provide student_id and assignment_master_id');
       		}
       		
       		$contents = self::getStudentAssignmentTable()->getByStudentAndAssignmentId($student_id, $assignment_master_id);
       		
       		if ( ! sizeof($contents) )
       		{
       			throw new Exception('No records');
       		}
       		
       		
       		$this->output['status'] = 1;
       		foreach ( $contents as $content )
       		{
       			$target_date = date('Y-m-d', strtotime($content['target_submission_date']));
       			
       			if ( self::getStudentAssignmentTable()->isLate($assignment_master_id, $student_id, $target_date) )
       			{
       				$status = 'Late';
       			}
       			elseif ( self::getStudentAssignmentTable()->isSumitted($assignment_master_id, $student_id, $target_date) ) 
       			{
       				$status= 'Submitted';
       			}
       			
       			$show_remarks = self::getAssignmentRemarkTable()->getByAssignment($content['assignment_id'], 'show_remarks', $student_id);
       			
       			$remarks = $show_remarks ? self::getAssignmentRemarkTable()->getByAssignment($content['assignment_id'], 'remark', $student_id): '';
       			
       			$show_marks = self::getAssignmentRemarkTable()->getByAssignment($content['assignment_id'], 'show_marks', $student_id);
       			
       			$marks = $show_marks? self::getAssignmentRemarkTable()->getByAssignment($content['assignment_id'], 'marks', $student_id) : '';
       			$this->output['contents'][] = array(
       			'assignment_master_id'	=> $content['assignment_id'],
       			'assignment_title'		=> self::getAssignmentMasterTable()->getInfoById($content['assignment_id'], 'assignment_title'),
       			'subject_id'			=> $content['subject_id'],
       			'subject_name'			=> self::getSubjectTable()->getName($content['subject_id']),
       			'assigned_by'			=> self::getStaffTable()->getFullname($content['staff_id']),
       			'target_date'			=> $target_date,
       			'attachments'			=> Attachment_Helper::getAttachment($content['upload_file']),
       			'content'				=> $content['content'],
       			'remark'				=> $remarks,
       			'marks'					=> $marks,
       			'status'				=> $status
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
	
	public function deleteAction()
	{
		try {
			$assignment_student_id = $this->getParam('assignment_student_id');
			
			if ( ! self::getStudentAssignmentTable()->remove($assignment_student_id) )
			{
				throw new Exception('Error');
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