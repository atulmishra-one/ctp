<?php

include_once APPLICATION_PATH.'/models/Staff.php';
include_once APPLICATION_PATH.'/models/Student.php';
include_once APPLICATION_PATH.'/models/UserSection.php';
include_once APPLICATION_PATH.'/models/GroupMember.php';


class Teacher extends Zend_Controller_Request_Http
{ 
	private $output = array(
	'status'	=> 0
	);
	
	protected static function getStaffTable()
	{
		return new Api_Model_Staff();
	}
	
	protected static function getStudentTable()
	{
		return new Api_Model_Student();
	}
	
	protected static function getUserSectionTable()
	{
		return new Api_Model_UserSection();
	}
	
	protected static function getGroupMemberTable()
	{
		return new Api_Model_GroupMember();
	}
	
	public function indexAction()
	{
		try {
		  
			$info_type 	= $this->getParam('info_type');
			$user_id 	= (int)$this->getParam('user_id');
            $school_id 	= (int)$this->getParam('school_id');
            $class_id 	= (int)$this->getParam('class_id');
            $section_id = (int)$this->getParam('section_id');
            
            if ( empty($school_id) or empty($info_type) )
            {
            	throw new Exception('Please provide school_id and info_type');
            }
		  	
			switch ($info_type)
			{
				case 'STUDENT_LIST':
					if ( !empty($class_id) && !empty($section_id) )
					{
						$student_list = self::getStudentTable()->getStudentList($school_id, $class_id, $section_id);
						
						$this->output['status'] = 1;
						foreach ( $student_list as $student )
						{
							$this->output['contents'][] = array(
							'class_id'		=> $class_id,
							'section_id'	=> $section_id,
							'student_id'	=> $student['sid'],
							'name'			=> $student['fname'].' '.$student['lname'],
							'user_id'		=> $student['user_id']
							);
						}
					}
					elseif ( !empty($user_id) )
					{
						$assignedClassSection = self::getUserSectionTable()->getByUserId($user_id, $school_id);
						if ( !sizeof($assignedClassSection) )
						{
							throw new Exception('Invalid user_id');
						}
						
						foreach ( $assignedClassSection as $acs )
						{
							$classes[] 	= $acs['class_id'];
							$sections[] = $acs['section_id'];
						}
						
						$student_list = self::getStudentTable()
						->getStudentListAll($school_id, $classes, $sections);
						
						if ( !sizeof($student_list) )
						{
							throw new Exception('No results');
						}
						
						$this->output['status'] = 1;
						foreach ( $student_list as $student )
						{
								$this->output['contents'][] = array(
								'class_id'		=> $student['class'],
								'section_id'	=> $student['section_id'],
								'student_id'	=> $student['sid'],
								'name'			=> $student['fname'].' '.$student['lname'],
								'user_id'		=> $student['user_id'],
								'roll_no'		=> (int)$student['roll_no'],
								'is_grouped'	=> self::getGroupMemberTable()->isInGroup($student['user_id'], $student['sid'], $student['class'], $student['section_id'])
								);
						}
						
					}
				break;
				
				case 'TEACHER_LIST':
					$teachers = self::getUserSectionTable()->getAllTeachersByClassSection($school_id, $class_id, $section_id);
					
					if ( !sizeof($teachers) )
					{
						throw new Exception('No teachers');
					}
					$this->output['status'] = 1;
					foreach ( $teachers as $teacher )
					{
						$this->output['contents'][] = array(
						'teacher_id'	=> $teacher['id'],
						'user_id'		=> $teacher['user_id'],
						'name'			=> $teacher['initial_name'].' '.$teacher['fname'].' '.$teacher['lname']
						);
					}
				break;
				
				default:
				break;
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