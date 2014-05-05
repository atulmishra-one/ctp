<?php

include_once APPLICATION_PATH.'/models/AssignmentMaster.php';
include_once APPLICATION_PATH.'/models/NotificationType.php';
include_once APPLICATION_PATH.'/models/Notification.php';
include_once APPLICATION_PATH.'/models/AssignmentRemark.php';
include_once APPLICATION_PATH.'/models/AssignmentSysn.php';
include_once APPLICATION_PATH.'/helpers/Socket.php';

class Remark extends Zend_Controller_Request_Http
{ 
	private $output = array(
	'status'	=> 0
	);
	
	protected static function getAssignmentMasterTable()
	{
		return new Api_Model_AssignmentMaster();
	}
	
	protected static function getNotifyTypeTable()
	{
		return new Api_Model_NotificationType();
	}
	
	protected static function getNotifyTable()
	{
		return new Api_Model_Notification();
	}
	
	protected static function getRemarkTable()
	{
		return new Api_Model_AssignmentRemark();
	}
	
	protected static function getAssignmentSysncTable()
	{
		return new Api_Model_AssignmentSysn();
	}
	
	public function postAction()
	{
		try {
			$data = $this->getParam('data');
			$data = json_decode($data, true);
			
			if ( empty($data) )
			{
				throw new Exception('Please provide data');
			}
			
			if ( empty($data['assignment_master_id']) )
			{
				throw new Exception('Please provide assignment_master_id');
			}
			
			$assignInfo = self::getAssignmentMasterTable()->getInfo($data['assignment_master_id']);
			
			$data['school_id']  = $assignInfo['school_auto_id'];
			$data['class_id']   = $assignInfo['class_auto_id'];
			$data['section_id'] = $assignInfo['section_id'];
			$data['subject_id'] = $assignInfo['subject_id'];
			
			if ( self::getRemarkTable()->hasRemark($data['assignment_master_id'], $data['student_id'], $data['teacher_id'] ) > 0)
			{
				self::getRemarkTable()->updateRemark( $data);
			}
			else 
			{
				self::getRemarkTable()->save($data);
			}
			
			
			if ( $data['show_marks'] == 1 || $data['show_remarks'] == 1)
			{
				self::getAssignmentSysncTable()->save( array(
				'student_id'			=> $data['student_id'],
				'assignment_master_id'	=> $data['assignment_master_id']
				));
			
				self::getAssignmentSysncTable()->setOFFTeacher($data['assignment_master_id'], $data['teacher_id'] );
			
				self::getNotifyTable()->saveForStudent( array(
				'type_id'		=> self::getNotifyTypeTable()->get('REMARK'),
				'school_id'		=> $data['school_id'],
				'student_id'	=> $data['student_id'],
				'class_id'		=> $data['class_id'],
				'section_id'	=> $data['section_id'],
				'notify_by'		=> 'Teacher',
				'notify_by_id'	=> $data['teacher_id']
				));
			
				Socket_Helper::write( array(
				'class_id'		=> $data['class_id'],
				'section_id'	=> $data['section_id'],
				'teacher_id'	=> 0
				));
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
}