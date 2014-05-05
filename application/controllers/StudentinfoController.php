<?php 

include_once APPLICATION_PATH.'/models/UserSection.php';


class Studentinfo extends Zend_Controller_Request_Http
{ 
	private $output = array(
	'status'	=> 0
	);
	
	protected static function getUserSectionTable()
	{
		return new Api_Model_UserSection();
	}
	
	public function getAction()
	{
		try {
			$user_id 	= $this->getParam('user_id');
        	$school_id 	= $this->getParam('school_id');
        	
        	if ( empty($user_id) or empty($school_id))
        	{
        		throw new Exception('Please provide user_id and school_id');
        	}
        	
        	$assignedSection = self::getUserSectionTable()->getStudentAssignedClassSection($user_id, $school_id);
        	
        	if ( !sizeof($assignedSection) )
        	{
        		throw new Exception('No class and section assigned');
        	}
        	
        	$this->output['status'] = 1;
        	$this->output['contents'][] = array(
        	'class_id'			=> $assignedSection['class_id'],
        	'section_id'		=> $assignedSection['section_id'],
        	'master_section_id'	=> $assignedSection['section_id']
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
}