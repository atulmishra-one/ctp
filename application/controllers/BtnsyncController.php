<?php

include_once APPLICATION_PATH.'/models/BtnSync.php';

class Btnsync extends Zend_Controller_Request_Http
{
	private $output = array(
		'status' => 0
	);
	
	protected static function getBtnSyncTable()
	{
		$btnSyncTable = new Api_Model_BtnSync();
		return $btnSyncTable;
	}
	
	public function postAction()
	{
		try {
			$button_name = $this->getParam('button_name');
			$school_id   = $this->getParam('school_id');
			$class_id    = $this->getParam('class_id');
			$section_id  = $this->getParam('section_id');
			$status      = $this->getParam('status');
			
			if ( empty( $button_name) || empty( $school_id) || empty($class_id) || empty($section_id) || empty($status))
			{
				throw new Exception('Missing parameters');
			}
			
			$save = self::getBtnSyncTable()->save( array(
				'school_id' 	=> $school_id,
				'class_id'  	=> $class_id,
				'section_id'	=> $section_id,
				'button_name'	=> $button_name,
				'status'		=> $status
			));
			
			if ( $save) 
			{
				$this->output['status'] = 1;
				$this->output['message'] = 'success';
			}
			else {
				$this->output['status'] = 0;
				$this->output['message'] = 'Error';
			}
			
		}catch (Exception $e)
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
	
	public function getAction()
	{
		try {
			$school_id  = $this->getParam('school_id');
			$class_id   = $this->getParam('class_id');
			$section_id = $this->getParam('section_id');
			
			if ( empty($school_id) || empty($class_id) || empty($section_id) )
			{
				throw new Exception('Missing parameters');
			}
			
			$this->output['status'] = 1;
			$this->output['contents'][] = array(
				'mute'		=> self::getBtnSyncTable()->getStatus($school_id, $class_id, $section_id, 'MUTE'),
				'blackout'	=> self::getBtnSyncTable()->getStatus($school_id, $class_id, $section_id, 'BLACKOUT'),
				'lock'		=> self::getBtnSyncTable()->getStatus($school_id, $class_id, $section_id, 'LOCK'),
			);
			
		}catch (Exception $e)
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
	
	public function deleteAction()
	{
		try {
			$school_id  = $this->getParam('school_id');
		    $class_id   = $this->getParam('class_id');
		    $section_id = $this->getParam('section_id');
		    
		    if ( (self::getBtnSyncTable()->remove($school_id, $class_id, $section_id) ) === true )
		    {
		    	$this->output['status'] = 1;
		    	$this->output['message'] = 'success';
		    }
		    else {
		    	throw new Exception('Error');
		    }
		    
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