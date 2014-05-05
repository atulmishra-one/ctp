<?php

include_once APPLICATION_PATH.'/models/Staff.php';
include_once APPLICATION_PATH.'/models/AssignmentMaster.php';
include_once APPLICATION_PATH.'/models/Student.php';
include_once APPLICATION_PATH.'/models/NotificationType.php';
include_once APPLICATION_PATH.'/models/Notification.php';


class Reassign extends Zend_Controller_Request_Http
{	
	private $output = array(
	'status'	=> 0
	);
	
	protected static function getAssignmentMasterTable()
	{
		return new Api_Model_AssignmentMaster();
	}
	
	protected static function getStudentTable()
	{
		return new Api_Model_Student();
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
			$assignment_master_id = $this->getParam('assignment_master_id');
			
			if ( empty($assignment_master_id) )
			{
				throw new Exception('Please provide assignment_master_id');
			}
			
			$assign = self::getAssignmentMasterTable()->getByIdInfoDetails($assignment_master_id);
			
			if ( !sizeof($assign) )
			{
				throw new Exception('Invalid assignment_master_id');
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