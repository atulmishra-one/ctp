<?php
/**
 * This web service is not in use as Assignment/get is doing same thing.
 * 
 */
include_once APPLICATION_PATH.'/models/AssignmentSysn.php';
include_once APPLICATION_PATH.'/helpers/Attachment.php';
include_once APPLICATION_PATH.'/models/AssignmentStudent.php';

class Assignmentinfo extends Zend_Controller_Request_Http
{
	private $output = array();
	
	protected static function getAssignmentSyncTable()
	{
		$assignmentSyncTable = new Api_Model_AssignmentSysn();
		return $assignmentSyncTable;
	}
	
	protected static function getAssignStudentTable()
	{
		$assignStudentTable = new Api_Model_AssignmentStudent();
		return $assignStudentTable;
	}
	
	protected static function getSubmittedDate($aid, $sid)
	{
		return self::getAssignStudentTable()->getInfoById($aid, $sid, 'submission_date');
	}
	
	protected static function hasSubmitted($aid, $sid)
	{
		return self::getAssignStudentTable()->isSumitted($aid, $sid, $date);
	}
	public function getAction()
	{
		
		try {
			
			$assignment_master_id = $this->getParam('assignment_master_id');
			
			$students = self::getAssignmentSyncTable()->get($assignment_master_id);
			
			if ( sizeof($students) >= 1)
			{
				foreach ( $students as $student )
				{
					//$this->output['contents'][] = array(
					//	'submitted_date'	=> ''
					//);
				}
			}
			
			
		}
		catch (Exception $e)
		{
			$this->output['status']  = 0;
			$this->output['message'] = $e->getMessage();
		}
		
	    if ( sizeof( $this->output) < 1 )
		{
			$this->output['status'] = 0;
			$this->output['message'] = 'No records';
		}
		$response = new Response();
			$response->getResponse()
			->setHttpResponseCode(200)
			->setHeader( 'Content-Type', 'application/json' )
			->appendBody( json_encode( $this->output ) );
	}
}