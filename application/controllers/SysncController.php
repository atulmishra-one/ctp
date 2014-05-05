<?php 

include_once APPLICATION_PATH.'/models/AssignmentSysn.php';
include_once APPLICATION_PATH.'/models/NotesSysn.php';
include_once APPLICATION_PATH.'/models/DiarySysn.php';


class Sysnc extends Zend_Controller_Request_Http
{ 
	private $output = array(
	'status'	=> 0
	);
	
	protected static function getAssignmentSyncTable()
	{
		return new Api_Model_AssignmentSysn();
	}
	
	protected static function getNotesSyncTable()
	{
		return new Api_Model_NotesSysn();
	}
	
	protected static function getDiarySyncTable()
	{
		return new Api_Model_DiarySysn();
	}
	
	
	public function postAction()
	{
		try {
			$type 		= $this->getParam('type');
        	$teacher_id = $this->getParam('teacher_id');
        	$student_id = $this->getParam('student_id');
        
        	$id = $this->getParam('id');
        	$id = json_decode($id, true);
        	
        	if ( empty($type) or empty($id) )
        	{
        		throw new Exception('Please provide type and id');
        	}
        	
        	switch ( $type )
        	{
        		case 'ASSIGNMENT':
        			if ( !empty($student_id))
        			{
        				self::getAssignmentSyncTable()->setONStudentBatch($id, $student_id);
        			}
        			elseif ( !empty($teacher_id))
        			{
        				self::getAssignmentSyncTable()->setONTeacherBatch($id, $teacher_id);
        			}
        			
        			$this->output['status'] = 1;
        			$this->output['message'] = 'success';
        		break;
        		
        		case 'NOTES':
        			if ( !empty($student_id))
        			{
        				self::getNotesSyncTable()->setONForStudentBatch($id, $student_id);
        			}
        			elseif ( !empty($teacher_id))
        			{
        				self::getNotesSyncTable()->setONForTeacherBatch($id, $teacher_id);
        			}
        			
        			$this->output['status'] = 1;
        			$this->output['message'] = 'success';
        			
        		break;
        		
        		case 'DIARY':
        			if ( !empty($student_id))
        			{
        				self::getDiarySyncTable()->setONForStudentBatch($id, $student_id);
        			}
        			elseif ( !empty($teacher_id))
        			{
        				self::getDiarySyncTable()->setONForTeacherBatch($id, $teacher_id);
        			}
        			
        			$this->output['status'] = 1;
        			$this->output['message'] = 'success';
        		break;
        	}// close switch
        	
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