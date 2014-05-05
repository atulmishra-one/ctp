<?php

include_once APPLICATION_PATH.'/helpers/Diary_Get.php';
include_once APPLICATION_PATH.'/helpers/Attachment.php';
include_once APPLICATION_PATH.'/models/DiaryMaster.php';
include_once APPLICATION_PATH.'/models/DiaryShare.php';
include_once APPLICATION_PATH.'/models/Staff.php';
include_once APPLICATION_PATH.'/models/GroupMember.php';
include_once APPLICATION_PATH.'/models/NotificationType.php';
include_once APPLICATION_PATH.'/models/Notification.php';
include_once APPLICATION_PATH.'/models/Student.php';
include_once APPLICATION_PATH.'/models/DiarySysn.php';
include_once APPLICATION_PATH.'/helpers/Socket.php';

class Diary extends Zend_Controller_Request_Http
{
	private $output = array(
	'status'	=> 0
	);
	
	protected static function getNotifyTypeTable()
	{
		return new Api_Model_NotificationType();
	}
	
	protected static function getNotifyTable()
	{
		return new Api_Model_Notification();
	}
	
	protected static function getGroupMemberTable()
	{
		return new Api_Model_GroupMember();
	}
	
	protected static function getDiaryMasterTable()
	{
		return new Api_Model_DiaryMaster();
	}
	
	protected static function getDiaryShareTable()
	{
		return new Api_Model_DiaryShare();
	}
	
	protected static function getStaffTable()
	{
		return new Api_Model_Staff();
	}
	
	protected static function getStudentTable()
	{
		return new Api_Model_Student();
	}
	
	protected static function getDiarySyncTable()
	{
		return new Api_Model_DiarySysn();
	}
	
	public function postAction()
	{
		try {
			$school_id = $this->getParam('school_id');
			$data	   = $this->getParam('data');
			$data	   = json_decode($data, true);
			$path 	   = $this->getParam('path');
			$path	   = empty($path)? '': $path;
			$diary_id  = $this->getParam('diary_id');
			
			if ( empty($school_id) || empty($data) )
			{
				throw new Exception('Please provide school_id and data');
			}
			$input = array(
			'type'			=> $data['type'],
			'user_id'		=> $data['user_id'],
			'user_type' 	=> strtoupper($data['user_type']),
			'text'			=> htmlentities($data['text'], ENT_QUOTES),
			'attachments'	=> Attachment_Helper::makeAttachment($data['attachments'], $path),
			'school_id'		=> $school_id,
			'class_id'		=> $data['class_id'],
			'section_id'	=> $data['section_id'],
			'subject_id'	=> $data['subject_id'],
			'status'		=> $data['status'],
			'shared_with'	=> $data['shared_with'],
			'diary_id'		=> $diary_id
			);
			
			if ( $input['user_type'] == 'TEACHER' || $input['user_type'] == 'Teacher' )
			{
				$teacher_id = self::getStaffTable()->getId($input['user_id']);
			}
			else 
			{
				$student_id = self::getStudentTable()->getInfo($input['user_id'], 'id');
			}
			
			if (empty( $input['diary_id']) )
			{// INSERT | CREATE NEW DIARY
				if ( isset($data['group_id']) && is_array($data['group_id']) && !empty($data['group_id'][0]) )
				{
					$input['shared_with'] = 'group';
					$lastId = self::getDiaryMasterTable()->save($input);
					
					self::getDiarySyncTable()->saveForTeacher($teacher_id, $lastId);
					
					self::getDiaryShareTable()->saveForGroupBatch($lastId, $data['group_id']);
					
					
					$groupMembers = self::getGroupMemberTable()->getMembersByGroupIdArray($data['group_id']);
					
					self::getDiarySyncTable()->saveForStudentBatch($groupMembers, $lastId);
					
					$notifyData = json_encode( array(
					'type_id' 		=> self::getNotifyTypeTable()->get('DIARY'),
					'students'  	=> $groupMembers,
					'school_id' 	=> $school_id,
					'class_id'		=> $input['class_id'],
					'section_id'	=> $input['section_id'],
					'teacher_id'	=> $teacher_id
					));
					exec("/opt/lampp/bin/php /opt/lampp/htdocs/schoolerp/soap/ctp/application/helpers/Notification_Student.php '$notifyData' > /dev/null &");
					
					Socket_Helper::write( array(
					'class_id' 	 => $input['class_id'],
					'section_id' => $input['section_id'],
					'teacher_id' => 0
					));
					
					$this->output['status'] = 1;
					$this->output['message'] = 'success';
				}
				elseif ( isset($data['class_section']) && is_array( $data['class_section']) && !empty( $data['class_section'][0]))
				{
					$input['shared_with'] = 'class';
					$lastId = self::getDiaryMasterTable()->save($input);
					
					self::getDiarySyncTable()->saveForTeacher($teacher_id, $lastId);
					self::getDiaryShareTable()->saveForClassSectionBatch($lastId, $data['class_section']);
					
					foreach ( $data['class_section'] as $classSection )
					{
						$classStudents[] = self::getStudentTable()->getStudentListIds($school_id, $classSection['class_id'], $classSection['section_id']);
					}
					
					foreach ( $classStudents as $student )
					{
						foreach ( $student as $st )
						{
							$students[] = $st;
						}
					}

					self::getDiarySyncTable()->saveForStudentBatch($students, $lastId);
					
					$notifyData = json_encode( array(
					'type_id' 		=> self::getNotifyTypeTable()->get('DIARY'),
					'students'  	=> $students,
					'school_id' 	=> $school_id,
					'class_id'		=> $input['class_id'],
					'section_id'	=> $input['section_id'],
					'teacher_id'	=> $teacher_id
					));
					exec("/opt/lampp/bin/php /opt/lampp/htdocs/schoolerp/soap/ctp/application/helpers/Notification_Student.php '$notifyData' > /dev/null &");
					
					Socket_Helper::write( array(
					'class_id' 	 => $input['class_id'],
					'section_id' => $input['section_id'],
					'teacher_id' => 0
					));
					
					$this->output['status'] = 1;
					$this->output['message'] = 'success';
					
				}
				elseif ( isset($data['shared_teacher_ids']) && is_array($data['shared_teacher_ids']) && !empty($data['shared_teacher_ids'][0]))
				{
					$input['shared_with'] = 'teacher';
					$lastId = self::getDiaryMasterTable()->save($input);
					
					if( !empty( $teacher_id ) ) {
						self::getDiarySyncTable()->saveForTeacher($teacher_id, $lastId);
					}else {
						self::getDiarySyncTable()->saveForStudent($student_id, $lastId);
					}
					
					
					foreach ( $data['shared_teacher_ids'] as $tid )
					{
						self::getDiaryShareTable()->saveForTeacher($lastId, $tid);
						
						if( !empty( $teacher_id) ) {
							self::getNotifyTable()->saveForTeacher( array(
							'type_id' 		=> self::getNotifyTypeTable()->get('DIARY'),
							'school_id'		=> $school_id,
							'class_id'		=> $input['class_id'],
							'section_id'	=> $input['section_id'],
							'teacher_id'	=> $tid,
							'notify_by' 	=> 'Teacher',
							'notify_by_id'  => $teacher_id
							));
							self::getDiarySyncTable()->saveForTeacher($tid, $lastId);
							Socket_Helper::write( array(
							'class_id'   => 0,
							'section_id' => 0,
							'teacher_id' => $tid
							));
						}else {
							self::getNotifyTable()->saveForTeacher( array(
							'type_id' 		=> self::getNotifyTypeTable()->get('DIARY'),
							'school_id'		=> $school_id,
							'class_id'		=> $input['class_id'],
							'section_id'	=> $input['section_id'],
							'teacher_id'	=> $tid,
							'notify_by' 	=> 'Student',
							'notify_by_id'  => $student_id
							));
							self::getDiarySyncTable()->saveForTeacher($tid, $lastId);
							Socket_Helper::write( array(
							'class_id'   => 0,
							'section_id' => 0,
							'teacher_id' => $tid
							));
						}
						
					}
					
					$this->output['status'] = 1;
					$this->output['message'] = 'success';
				}
				elseif ( isset($data['shared_students_ids']) && is_array($data['shared_students_ids']) && !empty($data['shared_students_ids'][0]))
				{
					$input['shared_with'] = 'student';
					$lastId = self::getDiaryMasterTable()->save($input);
					
					self::getDiarySyncTable()->saveForTeacher($teacher_id, $lastId);
					
					self::getDiaryShareTable()->saveForStudentBatch($lastId, $data['shared_students_ids']);
					
					$students = array();
					foreach ( $data['shared_students_ids'] as $st )
					{
						$students[]['student_id'] = $st;
					}
					
					self::getDiarySyncTable()->saveForStudentBatch($students, $lastId);
					
					$notifyData = json_encode( array(
					'type_id' 		=> self::getNotifyTypeTable()->get('DIARY'),
					'students'  	=> $students,
					'school_id' 	=> $school_id,
					'class_id'		=> $input['class_id'],
					'section_id'	=> $input['section_id'],
					'teacher_id'	=> $teacher_id
					));
					exec("/opt/lampp/bin/php /opt/lampp/htdocs/schoolerp/soap/ctp/application/helpers/Notification_Student.php '$notifyData' > /dev/null &");
					
					Socket_Helper::write( array(
					'class_id' 	 => $input['class_id'],
					'section_id' => $input['section_id'],
					'teacher_id' => 0
					));
					
					$this->output['status'] = 1;
					$this->output['message'] = 'success';
				
				}
			}
			else 
			{// UPDATE DIARY
				if ( is_array($data['group_id']) && sizeof($data['group_id']) && !empty($data['group_id'][0]) )
				{
					$input['shared_with'] = 'group';
					self::getDiaryMasterTable()->updateDiary($input);
					
					self::getDiaryShareTable()->saveForGroupBatch($input['diary_id'], $data['group_id']);
					$this->output['status'] = 1;
					$this->output['message'] = 'success';
				}
				elseif ( is_array( $data['class_section']) && sizeof($data['class_section']) && !empty( $data['class_section'][0]))
				{
					self::getDiaryMasterTable()->updateDiary($input);
					
					self::getDiaryShareTable()->saveForClassSectionBatch($input['diary_id'],  $data['class_section'] );
					$this->output['status'] = 1;
					$this->output['message'] = 'success';
				}
				elseif ( is_array($data['shared_teacher_ids']) && sizeof($data['shared_teacher_ids']) && !empty($data['shared_teacher_ids'][0]))
				{
					self::getDiaryMasterTable()->updateDiary($input);
					foreach ( $data['shared_teacher_ids'] as $tid )
					{
						self::getDiarySyncTable()->saveForTeacher($tid, $input['diary_id']);
					}
					$this->output['status'] = 1;
					$this->output['message'] = 'success';
				}
				elseif ( is_array($data['shared_students_ids']) && sizeof($data['shared_students_ids']) && !empty($data['shared_students_ids'][0]))
				{
					self::getDiaryMasterTable()->updateDiary($input);
					
					self::getDiaryShareTable()->saveForStudentBatch($input['diary_id'], $data['shared_students_ids']);
					
					$this->output['status'] = 1;
					$this->output['message'] = 'success';
					
				}
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

	public function getAction()
	{
		try {
		 $school_id 	= $this->getParam('school_id');
		 $class_id 		= $this->getParam('class_id');
		 $section_id 	= $this->getParam('section_id');
		 $subject_id 	= $this->getParam('subject_id');
		 $user_id 		= $this->getParam('user_id');
		 $user_type 	= $this->getParam('user_type');
		 $date 			= $this->getParam('date');
		  
		 	if ( empty($school_id) )
		 	{
		 		throw new Exception('Please provide school_id');
		 	}
		 
		  switch ( $user_type )
		  {
		  	case 'TEACHER':
		  		if ( !empty($date) )
		  		{
		  			$results = Diary_Get::getResultsTeacher( array(
		  			'date' 		 => $date,
		  			'user_id' 	 => $user_id,
		  			'school_id'	 => $school_id,
		  			'class_id'	 => $class_id,
		  			'section_id' => $section_id
		  			));
		  			
		  			if ( !sizeof($results) )
		  			{
		  				throw new Exception('No records');
		  			}
		  			$this->output['status'] = 1;
		  			$this->output['contents'] = $results;
		  		}
		  		else 
		  		{
		  			$results = Diary_Get::getResultsTeacher( array(
		  			'user_id' 	 => $user_id,
		  			'school_id'	 => $school_id,
		  			'class_id'	 => $class_id,
		  			'section_id' => $section_id
		  			));
		  			
		  			if ( !sizeof($results) )
		  			{
		  				throw new Exception('No records');
		  			}
		  			$this->output['status'] = 1;
		  			$this->output['contents'] = $results;
		  		}
		  	break;
		  	
		  	case 'STUDENT':
		  		$results = Diary_Get::getResultsStudent( array(
		  		'user_id'	=> $user_id,
		  		'school_id'	=> $school_id,
		  		'class_id'	=> $class_id,
		  		'section_id'=> $section_id
		  		));
		  		
		  		if ( !sizeof($results) )
		  		{
		  			throw new Exception('No records');
		  		}
		  		$this->output['status'] = 1;
		  		$this->output['contents'] = $results;
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