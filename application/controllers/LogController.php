<?php

include_once APPLICATION_PATH.'/models/EventlogMaster.php';
include_once APPLICATION_PATH.'/models/ActivityLog.php';


class Log extends Zend_Controller_Request_Http
{ 
	private $output = array(
	'status'	=> 0
	);
	
	public function postAction()
	{
		try {
			$event_name = (string)$this->getParam('event_name');
			$user_id 	= (int)$this->getParam('user_id');
			$start_time = (string)$this->getParam('start_time');
			$end_time 	= (string)$this->getParam('end_time');
			$school_id 	= (int)$this->getParam('school_id');
			$mode 		= (string)$this->getParam('mode'); 
			$event_id 	= (int)$this->getParam('event_id');
			
			$end_time = ( !empty($end_time) and !is_null($end_time) and $end_time != 'null' )? date('Y-m-d h:i:s', strtotime($end_time)) : '0000-00-00 00:00:00';
			
			$eventMasterTable = new Api_Model_EventlogMaster();
		
			$activityLogTable = new Api_Model_ActivityLog();
		
			if ( empty($event_id) )
			{
				$eventMaster_id = $eventMasterTable->getIdByName($event_name);
				if($eventMaster_id )
				{
					
					$id = $activityLogTable->save( array(
						'user_id'			=> $user_id,
						'eventMaster_id'	=> $eventMaster_id,
						'start_time'		=> $start_time,
						'end_time'			=> $end_time,
						'school_id'			=> $school_id,
						'mode'				=> $mode
					));
				
					$this->output['status'] = 1;
					$this->output['event_id'] = $id;
				}
				else
				{
					$this->output['status'] = 0;
					$this->output['message'] = 'Invalid event name';
				}
				
			}
			else
			{
				$activityLogTable->updateEndTime($end_time, $event_id);
				$this->output['status'] = 1;
				$this->output['event_id'] = $event_id;
			}
			
		}
		catch (Exception $e)
		{
			$this->output['status']	= 0;
			$this->output['message'] = $e->getMessage();
		}
		
		$response = new Response();
		$response->getResponse()
		->setHttpResponseCode(200)
		->setHeader( 'Content-Type', 'application/json' )
		->appendBody( json_encode( $this->output ) );
	}
}