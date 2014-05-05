<?php

include_once APPLICATION_PATH.'/models/Staff.php';

class Schoolinfo extends Zend_Controller_Request_Http
{ 
	private $output = array(
	'status' => 0
	);
	
	protected static function getStaffTable()
	{
		return new Api_Model_Staff();
	}
	
	public function getAction()
	{
		try {
			$school_id = $this->getParam('school_id');
			
			$staffs = self::getStaffTable()->getBySchoolId($school_id);
			
			if ( !sizeof($staffs) )
			{
				throw new Exception('No teachers for this school');
			}
			
			$this->output['status']	 = 1;
			foreach ( $staffs as $staff )
			{
				$this->output['contents'][] = array(
				'name'			=> $staff['initial_name'].' '.$staff['fname'].' '.$staff['lname'],
				'teacher_id'	=> $staff['id'],
				'user_id'		=> $staff['user_id']
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