<?php

include_once APPLICATION_PATH.'/models/GetSubject.php';

class Student extends Zend_Controller_Request_Http
{ 
	private $output = array(
	'status'	=> 0
	);
	
	protected static function getSubjectTable()
	{
		return new Api_Model_GetSubject();
	}
	
	public function getAction()
	{
		try {
			$class_id 	= $this->getParam('class_id');
			
			if ( empty($class_id) )
			{
				throw new Exception('Please provide class_id');
			}
			
			$subjects = self::getSubjectTable()->getByClassId($class_id);
			
			if ( !sizeof($subjects) )
			{
				throw new Exception('No subjects');
			}
			
			$this->output['status'] = 1;
			foreach ( $subjects as $subject )
			{
				$this->output['contents'][] = array(
				'subject_id'	=> $subject->sub_auto_id,
				'subject_name'	=> $subject->subject_name,
				'subject_type'	=> $subject->subject_type,
				'subject_code'	=> $subject->subject_code
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