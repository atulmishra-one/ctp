<?php

include_once APPLICATION_PATH.'/models/UserLogin.php';
include_once APPLICATION_PATH.'/models/SchoolSession.php';


class Date extends Zend_Controller_Request_Http
{ 
	private $output = array(
		'status'	=> 0
	);
	
	public function getAction()
	{
		try {
			
			$type 		= $this->getParam('type');
			$school_id  = $this->getParam('school_id');
			
			if ( empty($type) || $type != 'CUR_DATETIME')
			{
				throw new Exception('Unknown type');
			}
			
			$dateTable = new Api_Model_UserLogin();
            $school_session = new Api_Model_SchoolSession();
            
            if ( $dateTable->getCurDateTime() )
            {
            	$res = $school_session->get($school_id);
            	if ( !sizeof($res) )
            	{
            		throw new Exception('Please provide school_id');
            	}
            	
            	$this->output['status'] = 1;
            	$this->output['start_date'] = $res->start_date;
            	$this->output['end_date']	 = $res->end_date;
            	$this->output['CUR_DATETIME']	= $dateTable->getCurDateTime();
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