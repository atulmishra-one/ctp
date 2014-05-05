<?php

include_once APPLICATION_PATH.'/models/Attendence.php';
include_once APPLICATION_PATH.'/models/Student.php';
include_once APPLICATION_PATH.'/models/UserSection.php';

class Attendenceinfo extends Zend_Controller_Request_Http
{
	private $output = array(
		'status' => 0
	);
	
	protected static function getStudentTable()
	{
		$studentTable = new Api_Model_Student();
		return $studentTable;
	}
	
	protected static function getAllAttendenceId( $attendence_master_id)
	{
		$attendenceTable = new Api_Model_Attendence();
		return $attendenceTable->getAllAttendenceMasterId($attendence_master_id);
	}
	
	protected static function getRollno( $user_id )
	{
		$userSectionTable = new Api_Model_UserSection();
		return $userSectionTable->getRollNo($user_id);
	}
	public function getAction()
	{
		try {
			
			$attendence_master_id = $this->getParam('attendence_master_id');
			
			
			if ( empty( $attendence_master_id) )
			{
				throw new Exception('Please provide attendence_master_id');
			}
			
			$ids = self::getAllAttendenceId($attendence_master_id);
			
			if ( sizeof($ids) >= 1 )
			{
				foreach ( $ids as $id )
				{
					if ( !empty( $id['student_id']) )
					{
						$this->output['status'] = 1;
						$this->output['contents'][] = array(
							'student_id'	=> $id['student_id'],
							'student_fname' => self::getStudentTable()->getInfoById($id['student_id'], 'fname'),
							'student_lname' => self::getStudentTable()->getInfoById($id['student_id'], 'lname'),
							'attend'		=> $id['attend'],
							'joined'		=> $id['join_status'],
							'roll_no'		=> self::getRollno(self::getStudentTable()->getInfoById($id['student_id'], 'user_id'))
						);
					}
				}
			}
			else {
				throw new Exception('No records');
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