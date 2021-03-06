<?php

include_once APPLICATION_PATH.'/helpers/Socket.php';
include_once APPLICATION_PATH.'/helpers/Attachment.php';
include_once APPLICATION_PATH.'/models/Notes.php';
include_once APPLICATION_PATH.'/models/GetClass.php';
include_once APPLICATION_PATH.'/models/GetSection.php';
include_once APPLICATION_PATH.'/models/GetSubject.php';
include_once APPLICATION_PATH.'/models/Notelinks.php';
include_once APPLICATION_PATH.'/models/Student.php';
include_once APPLICATION_PATH.'/models/Staff.php';
include_once APPLICATION_PATH.'/models/GroupNotes.php';
include_once APPLICATION_PATH.'/models/GroupTable.php';
include_once APPLICATION_PATH.'/models/NotesShare.php';
include_once APPLICATION_PATH.'/models/NotesSysn.php';
include_once APPLICATION_PATH.'/models/NotificationType.php';
include_once APPLICATION_PATH.'/models/Notification.php';
include_once APPLICATION_PATH.'/models/GroupMember.php';


class Notes extends Zend_Controller_Request_Http
{ 
	private $output = array(
	'status' => 0
	);
	
	protected static function getGroupMemberTable()
	{
		return new Api_Model_GroupMember();
	}
	
	protected static function getNotifyTypeTable()
	{
		return new Api_Model_NotificationType();
	}
	
	protected static function getNotifyTable()
	{
		return new Api_Model_Notification();
	}
	
	protected static function getNotesTable()
	{
		return new Api_Model_Notes();
	}
	
	protected static function getNotesSyncTable()
	{
		return new Api_Model_NotesSysn();
	}
	
	protected static function getNotesLink()
	{
		return new Api_Model_Notelinks();
	}
	
	protected static function getStudentTable()
	{
		return new Api_Model_Student();
	}
	
	protected static function getNotesGroupTable()
	{
		return new Api_Model_GroupNotes();
	}
	
	protected static function getNotesShareTable()
	{
		return new Api_Model_NotesShare();
	}
	
	protected static function getGroupTable()
	{
		return new Api_Model_GroupTable();
	}
	
	protected static function getStaffTable()
	{
		return new Api_Model_Staff();
	}
	
	protected static function getClassTable()
	{
		return new Api_Model_GetClass();
	}
	
	protected static function getSectionTable()
	{
		return new Api_Model_GetSection();
	}
	
	protected static function getSubjectTable()
	{
		return new Api_Model_GetSubject();
	}
	
	protected static function getDBCache()
	{
		return new DBCache();
	}
	
	public function postAction()
	{
		try {
			$school_id 	= $this->getParam('school_id');
			$path 		= $this->getParam('path');
        	$path 		= ( empty($path)) ? '' : $path;
        	
        	$data 		= $this->getParam('data');
        	$data		= json_decode($data, true);
        	$notes_id 	= $this->getParam('notes_id');
        	
        	$teacher_id =& $data['teacher_id'];
        	$student_id =& $data['student_id'];
        	
        	$notify_type_id = self::getNotifyTypeTable()->get('NOTES');
			
			$mode = isset($data['mode'] ) ? $data['mode'] : 'TABLET';
        	
        	$input = array(
        	'notes_title'		=> (string)$data['title'],
        	'notes_filename'	=> Attachment_Helper::makeAttachment($data['attachments'], $path),
        	'school_id'			=> (int)$school_id,
        	'class_id'			=> (int)$data['class_id'],
        	'section_id'		=> (int)$data['section_id'],
        	'subject_id'		=> (int)$data['subject_id'],
        	'teacher_id'		=> (int)$teacher_id,
        	'student_id'		=> (int)$student_id,
        	'notes_text'		=> (string)$data['notes_text'],
        	'notes_author'		=> (string)$data['author'],
        	'notes_shared'		=> (int)$data['share_status'],
        	'notes_status'		=> (string)ucfirst($data['status']),
        	'public'			=> (int)$data['public'],
        	'notes_id'			=> $notes_id,
			'mode'				=> (string)$mode
        	);
        	
        	$who = $input['teacher_id'] ? 'Teacher' : 'Student';
        	
        	if ( empty($school_id) )
        	{
        		throw new Exception('Please provide school_id');
        	}
        	
        	if ( empty( $input['notes_id']) )
        	{// CREATE NEW NOTES
        		if ( $input['public'] )
        		{
        			$input['notes_shared_with'] = 'public';
        			$lastId = self::getNotesTable()->create($input);
        			
        			self::getNotesLink()->saveBatch($lastId, $data['notes_link'] );
        			$this->output['status'] = 1;
        			$this->output['message'] = 'success';
        			$this->output['notes_id'] = $lastId;
        		}
        		elseif ( is_array($data['group_id']) && sizeof($data['group_id']) && !empty($data['group_id'][0]) )
        		{
        			$input['notes_shared_with'] = 'group';
        			$lastId = self::getNotesTable()->create($input);
        			
        			self::getNotesLink()->saveBatch($lastId, $data['notes_link'] );
        			
        			$mapGroupSharewithNotes = array();
        			
        			$mapGroupSharewithNotes[] = array(
        			'class_id'		=> $input['class_id'],
        			'section_id'	=> $input['section_id'],
        			'subject_id'	=> $input['subject_id'],
        			'notes_id'		=> $lastId
        			);
        			
        			$temp_class 	= $input['class_id'];
                    $temp_section 	= $input['section_id'];
                    $temp_subject 	= $input['subject_id'];
                    
                    foreach ( $data['group_id'] as $group )
                    {
                    	if ( isset( $group) )
                    	{
                    		$gInfo = self::getGroupTable()->getById($group);
                    		
                    		if ( $gInfo->class_id == $temp_class && $gInfo->section_id == $temp_section && $gInfo->subject_id == $temp_subject)
                    		{
                    			self::getNotesGroupTable()->save($lastId, $group);
                    			// SEND NOTIFICATION TO MEMBERS
                    			self::sendNotificationToMembers($notify_type_id, $input['teacher_id'], $group, $school_id, $lastId);
                    		}
                    		else 
                    		{
                    			$matchId = null;
                    			foreach ( $mapGroupSharewithNotes as $map )
                    			{
                    				if ( $gInfo->class_id == $map['class_id'] && $gInfo->section_id == $map['section_id'] 
                    				&& $gInfo->subject_id == $map['subject_id'])
                    				{
                    					$matchId = $map['notes_id'];
                    					break;
                    				}
                    			}
                    			
                    			if ( $matchId != null )
                    			{
                    				self::getNotesGroupTable()->save($matchId, $group);
                    				//SEND NOTIFICATION TO MEMBERS
                    				self::sendNotificationToMembers($notify_type_id, $input['teacher_id'], $group, $school_id, $matchId);
                    				$matchId = null;
                    			}
                    			else 
                    			{
                    				
                    				$input['class_id'] 			= $gInfo->class_id;
                                	$input['section_id'] 		= $gInfo->section_id;
                                	$input['subject_id'] 		= $gInfo->subject_id;
                                	$input['notes_shared_with'] = 'group';
                                	
                                	$hID = self::getNotesTable()->create($input);
                                	self::getNotesLink()->saveBatch($hID, $data['notes_link'] );
                                	
                                	$mapGroupSharewithNotes[] = array(
        							'class_id'		=> $input['class_id'],
        							'section_id'	=> $input['section_id'],
        							'subject_id'	=> $input['subject_id'],
        							'notes_id'		=> $hID
        							);
                                	
        							$matchId = null;
        							self::getNotesGroupTable()->save($hID, $group);
        							//SEND NOTIFICATION TO MEMBERS
        							self::sendNotificationToMembers($notify_type_id, $input['teacher_id'], $group, $school_id, $hID);
                    			}
                    		}
                    	}
                    }
                    $this->output['status'] = 1;
                    $this->output['notes_id'] = $lastId;
                    $this->output['message'] = 'success';
        		}
        		elseif ( is_array($data['shared_teacher_ids']) && sizeof($data['shared_teacher_ids']) && !empty($data['shared_teacher_ids'][0]) )
        		{
        			$input['notes_shared_with'] = 'teacher';
        			$lastId = self::getNotesTable()->create($input);
        			
        			self::getNotesLink()->saveBatch($lastId, $data['notes_link'] );
        			
        			if ( $who == 'Teacher')
        			{
        				self::getNotesSyncTable()->saveForTeacher($input['teacher_id'], $lastId);
        			}
        			elseif ( $who == 'Student')
        			{
					// Changes done by Amar bcz Notes sharing for student was not working
        			//	self::getNotesSyncTable()->saveForStudent($input['student_id'], $notes_id);
					self::getNotesSyncTable()->saveForStudent($input['student_id'], $lastId);
        			}
        			
        			
        			foreach ( $data['shared_teacher_ids'] as $sTeacher_id )
        			{
        				$valueNotesShare[] 	= "($lastId, $sTeacher_id, NOW() )";
        				$valueNotesSync[] 	= "($sTeacher_id,NOW(), $lastId, 'OFF')";
        				$valueNotify[] 		= "($notify_type_id, $school_id, $input[class_id] ,$input[section_id], $sTeacher_id,NOW(),'$who', $input[teacher_id])";
        			}
        			self::getNotesShareTable()->saveForTeacherBatch($valueNotesShare);
        			self::getNotesSyncTable()->saveForTeacherBatch($valueNotesSync);
        			self::getNotifyTable()->saveForTeacherBatch($valueNotify);
        			
        			foreach ( $data['shared_teacher_ids'] as $sTeacher_id )
        			{
        				Socket_Helper::write( array(
        				'class_id'		=> 0,
        				'section_id'	=> 0,
        				'teacher_id'	=> $sTeacher_id
        				));
        			}
        			
        			$this->output['status'] = 1;
                    $this->output['notes_id'] = $lastId;
                    $this->output['message'] = 'success';
        		}
        		elseif ( is_array($data['shared_students_ids']) && sizeof($data['shared_students_ids']) && !empty($data['shared_students_ids'][0]))
        		{
        			$input['notes_shared_with'] = 'student';
        			$lastId = self::getNotesTable()->create($input);
        			
        			self::getNotesLink()->saveBatch($lastId, $data['notes_link'] );
        			
        			if ( $who == 'Teacher')
        			{
        				self::getNotesSyncTable()->saveForTeacher($input['teacher_id'], $lastId);
        			}
        			
        			foreach ( $data['shared_students_ids'] as $sStudent_id )
        			{
        				$valueNotesShare[] 			= "($lastId, $sStudent_id, NOW() )";
        				$valueNotesSync[]  			= "($sStudent_id, NOW(), $lastId, 'OFF' )";
        				$students[]['student_id'] 	= $sStudent_id;
        			}
        			
        			
        			self::getNotesShareTable()->saveForStudentBatch($valueNotesShare);
        			self::getNotesSyncTable()->saveForStudentBatch($valueNotesSync);
        			
        			$notifyData = json_encode( array(
					'type_id' 		=> $notify_type_id,
					'students'  	=> $students,
					'school_id' 	=> $school_id,
					'class_id'		=> $input['class_id'],
					'section_id'	=> $input['section_id'],
					'teacher_id'	=> $teacher_id
					));
					
					exec("/opt/lampp/bin/php /opt/lampp/htdocs/schoolerp/soap/ctp/application/helpers/Notification_Student.php '$notifyData' > /dev/null &");
        			
        			Socket_Helper::write( array(
        			'class_id'		=> $input['class_id'],
        			'section_id'	=> $input['section_id'],
        			'teacher_id'	=> 0
        			));
        			
        			$this->output['status'] = 1;
                    $this->output['notes_id'] = $lastId;
                    $this->output['message'] = 'success';
        		}
        		elseif ( is_array($data['class_section']) && sizeof($data['class_section']) && !empty($data['class_section'][0]) )
        		{
        			$input['notes_shared_with'] = '';
        			$lastId = self::getNotesTable()->create($input);
        			
        			self::getNotesLink()->saveBatch($lastId, $data['notes_link'] );
        			self::getNotesSyncTable()->saveForTeacher($input['teacher_id'], $lastId);
        			
        			$temp_class 	= $input['class_id'];
                    $temp_section 	= $input['section_id'];
                    
        			foreach ( $data['class_section'] as $classSection )
        			{
        				if ( $temp_class != $classSection['class_id'] || $temp_section != $classSection['section_id'] )
        				{
        					$input['class_id'] 	 = $classSection['class_id'];
                            $input['section_id'] = $classSection['section_id'];
                            $input['notes_shared_with'] = 'class';
        					$lastIds = self::getNotesTable()->create($input);
        					self::getNotesLink()->saveBatch($lastIds, $data['notes_link'] );
        					// SEND NOTIFICATION TO ALL CLASS
        					self::sendNotificationToClass($notify_type_id, $input['teacher_id'], $school_id, $classSection['class_id'], $classSection['section_id'], $lastIds);
        				}
        				elseif ( $temp_class == $classSection['class_id'] && $temp_section == $classSection['section_id']  )
        				{
        					self::getNotesTable()->updateNotesShared($lastId, 'class');
        					
        					self::sendNotificationToClass($notify_type_id, $input['teacher_id'], $school_id, $temp_class, $temp_section, $lastId);
        				}
        			}
        			
        			
        			/*$students1 = self::getStudentTable()->getStudentListIds($school_id, $temp_class, $temp_section);
        			
        			foreach ( $students1 as $sStudents )
        			{
        				$valueNotesSync[] = "($sStudents[student_id], NOW(), $lastId, 'OFF' )";
        			}
        			
        			self::getNotesSyncTable()->saveForStudentBatch($valueNotesSync);
        			$notifyData1 = json_encode( array(
					'type_id' 		=> $notify_type_id,
					'students'  	=> $students1,
					'school_id' 	=> $school_id,
					'class_id'		=> $temp_class,
					'section_id'	=> $temp_class,
					'teacher_id'	=> $teacher_id
					));
					
					exec("/opt/lampp/bin/php /opt/lampp/htdocs/schoolerp/soap/ctp/application/helpers/Notification_Student.php '$notifyData1' > /dev/null &");
					
					Socket_Helper::write( array(
					'class_id'		=> $temp_class,
					'section_id'	=> $temp_section,
					'teacher_id'	=> 0
					));
					*/
					$this->output['status'] = 1;
                    $this->output['notes_id'] = $lastId;
                    $this->output['message'] = 'success';
        			
        		}
        		else 
        		{
        			$input['notes_shared_with'] = '';
        			$lastId = self::getNotesTable()->create($input);
        			
        			self::getNotesLink()->saveBatch($lastId, $data['notes_link'] );
        			if ( $who == 'Teacher')
        			{
        				self::getNotesSyncTable()->saveForTeacher($input['teacher_id'], $lastId);
        			}
        			else 
        			{
        				self::getNotesSyncTable()->saveForStudent($input['student_id'], $lastId);
        			}
        			
        			$this->output['status'] = 1;
                    $this->output['notes_id'] = $lastId;
                    $this->output['message'] = 'success';
        		}
        	}
        	else 
        	{// UPDATE
        		if ( $input['public'] )
        		{
        			$input['notes_shared_with'] = 'public';
        			self::getNotesTable()->updateNotes($input);
        			
        			self::getNotesLink()->saveBatch($input['notes_id'], $data['notes_link'] );
        			$this->output['status'] = 1;
                    $this->output['message'] = 'success';
        		}
        		elseif ( is_array($data['group_id']) && sizeof($data['group_id']) && !empty($data['group_id'][0]) )
        		{
        			$input['notes_shared_with'] = 'group';
        			self::getNotesTable()->updateNotes($input);
        			
        			self::getNotesLink()->saveBatch($input['notes_id'], $data['notes_link'] );
        			
        			$mapGroupSharewithNotes = array();
        			
        			$mapGroupSharewithNotes[] = array(
        			'class_id'		=> $input['class_id'],
        			'section_id'	=> $input['section_id'],
        			'subject_id'	=> $input['subject_id'],
        			'notes_id'		=> $input['notes_id']
        			);
        			
        			$temp_class 	= $input['class_id'];
                    $temp_section 	= $input['section_id'];
                    $temp_subject 	= $input['subject_id'];
                    
                    foreach ( $data['group_id'] as $group )
                    {
                    	if ( isset( $group) )
                    	{
                    		$gInfo = self::getGroupTable()->getById($group);
                    		
                    		if ( $gInfo->class_id == $temp_class && $gInfo->section_id == $temp_section && $gInfo->subject_id == $temp_subject)
                    		{
                    			self::getNotesGroupTable()->save($input['notes_id'], $group);
                    			// SEND NOTIFICATION TO MEMBERS
                    			self::sendNotificationToMembers($notify_type_id, $input['teacher_id'], $group, $school_id, $input['notes_id']);
                    		}
                    		else 
                    		{
                    			$matchId = null;
                    			foreach ( $mapGroupSharewithNotes as $map )
                    			{
                    				if ( $gInfo->class_id == $map['class_id'] && $gInfo->section_id == $map['section_id'] 
                    				&& $gInfo->subject_id == $map['subject_id'])
                    				{
                    					$matchId = $map['notes_id'];
                    					break;
                    				}
                    			}
                    			
                    			if ( $matchId != null )
                    			{
                    				self::getNotesGroupTable()->save($matchId, $group);
                    				//SEND NOTIFICATION TO MEMBERS
                    				self::sendNotificationToMembers($notify_type_id, $input['teacher_id'], $group, $school_id, $matchId);
                    				$matchId = null;
                    			}
                    			else 
                    			{
                    				$input['class_id'] 			= $gInfo->class_id;
                                	$input['section_id'] 		= $gInfo->section_id;
                                	$input['subject_id'] 		= $gInfo->subject_id;
                                	$input['notes_shared_with'] = 'group';
                                	
                                	$hID = self::getNotesTable()->create($input);
                                	self::getNotesLink()->saveBatch($hID, $data['notes_link'] );
                                	
                                	$mapGroupSharewithNotes[] = array(
        							'class_id'		=> $input['class_id'],
        							'section_id'	=> $input['section_id'],
        							'subject_id'	=> $input['subject_id'],
        							'notes_id'		=> $hID
        							);
                                	
        							$matchId = null;
        							self::getNotesGroupTable()->save($hID, $group);
        							//SEND NOTIFICATION TO STUDENTS
        							self::sendNotificationToMembers($notify_type_id, $input['teacher_id'], $group, $school_id, $hID);
                    			}
                    		}
                    	}
                    }
        			$this->output['status'] = 1;
                    $this->output['message'] = 'success';
        		}// CLOSE GROUP
        		elseif ( is_array($data['shared_teacher_ids']) && sizeof($data['shared_teacher_ids']) && !empty($data['shared_teacher_ids'][0]))
        		{
        			$input['notes_shared_with'] = 'teacher';
        			self::getNotesTable()->updateNotes($input);
        			
        			self::getNotesLink()->saveBatch($input['notes_id'], $data['notes_link'] );
        			
        			if ( $who == 'Teacher' )
        			{
        				self::getNotesSyncTable()->saveForTeacher($input['teacher_id'], $input['notes_id']);
        			}
        			else 
        			{
        				self::getNotesSyncTable()->saveForStudent($input['student_id'], $input['notes_id']);
        			}
        			
        			foreach ( $data['shared_teacher_ids'] as $sTeacher_id )
        			{
        				$valueNotesShare[] 	= "($input[notes_id], $sTeacher_id, NOW() )";
        				$valueNotesSync[] 	= "($sTeacher_id,NOW(), $input[notes_id], 'OFF')";
        				$valueNotify[] 		= "($notify_type_id, $school_id, $input[class_id],
        				$input[section_id], $sTeacher_id,NOW(),'$who', $input[teacher_id])";
        			}
        			self::getNotesShareTable()->saveForTeacherBatch($valueNotesShare);
        			self::getNotesSyncTable()->saveForTeacherBatch($valueNotesSync);
        			self::getNotifyTable()->saveForTeacherBatch($valueNotify);
        			
        			foreach ( $data['shared_teacher_ids'] as $sTeacher_id )
        			{
        				Socket_Helper::write( array(
        				'class_id'		=> 0,
        				'section_id'	=> 0,
        				'teacher_id'	=> $sTeacher_id
        				));
        			}
        			
        			$this->output['status'] = 1;
                    $this->output['message'] = 'success';
        			
        		}
        		elseif ( is_array($data['shared_students_ids']) && sizeof($data['shared_students_ids']) && !empty($data['shared_students_ids'][0]))
        		{
        			$input['notes_shared_with'] = 'student';
        			self::getNotesTable()->updateNotes($input);
        			
        			self::getNotesLink()->saveBatch($input['notes_id'], $data['notes_link'] );
        			
        			self::getNotesSyncTable()->saveForTeacher($input['teacher_id'], $input['notes_id']);
        			
        			foreach ( $data['shared_students_ids'] as $sStudent_id )
        			{
        				$valueNotesShare[] 			= "($input[notes_id], $sStudent_id, NOW() )";
        				$valueNotesSync[]  			= "($sStudent_id, NOW(), $input[notes_id], 'OFF' )";
        				$students[]['student_id'] 	= $sStudent_id;
        			}
        			
        			
        			self::getNotesShareTable()->saveForStudentBatch($valueNotesShare);
        			self::getNotesSyncTable()->saveForStudentBatch($valueNotesSync);
        			
        			$notifyData = json_encode( array(
					'type_id' 		=> $notify_type_id,
					'students'  	=> $students,
					'school_id' 	=> $school_id,
					'class_id'		=> $input['class_id'],
					'section_id'	=> $input['section_id'],
					'teacher_id'	=> $input['teacher_id']
					));
					
					exec("/opt/lampp/bin/php /opt/lampp/htdocs/schoolerp/soap/ctp/application/helpers/Notification_Student.php '$notifyData' > /dev/null &");
        			
        			Socket_Helper::write( array(
        			'class_id'		=> $input['class_id'],
        			'section_id'	=> $input['section_id'],
        			'teacher_id'	=> 0
        			));
        			
        			$this->output['status'] = 1;
                    $this->output['message'] = 'success';
        		}
        		elseif ( is_array($data['class_section']) && sizeof($data['class_section']) && !empty($data['class_section'][0]))
        		{
        			$input['notes_shared_with'] = '';
        			self::getNotesTable()->updateNotes($input);
        			
        			self::getNotesLink()->saveBatch($input['notes_id'], $data['notes_link'] );
        			self::getNotesSyncTable()->saveForTeacher($input['teacher_id'], $input['notes_id']);
        			
        			$temp_class 	= $input['class_id'];
                    $temp_section 	= $input['section_id'];
                    
        			foreach ( $data['class_section'] as $classSection )
        			{
        				if ( $temp_class != $classSection['class_id'] || $temp_section != $classSection['section_id'] )
        				{
        					$input['class_id'] 	 = $classSection['class_id'];
                            $input['section_id'] = $classSection['section_id'];
                            $input['notes_shared_with'] = 'class';
        					$lastIds = self::getNotesTable()->create($input);
        					self::getNotesLink()->saveBatch($lastIds, $data['notes_link'] );
        					// SEND NOTIFICATION TO ALL CLASS
        					self::sendNotificationToClass($notify_type_id, $input['teacher_id'], $school_id, $classSection['class_id'], $classSection['section_id'], $lastIds);
        				}
        				elseif ( $temp_class == $classSection['class_id'] && $temp_section == $classSection['section_id'] )
        				{
							self::getNotesTable()->updateNotesShared($input['notes_id'], 'class');
        					self::sendNotificationToClass($notify_type_id, $input['teacher_id'], $school_id, $temp_class, $temp_section, $input['notes_id']);
        				}
        			}
        			
        			
        			/*$students1 = self::getStudentTable()->getStudentListIds($school_id, $temp_class, $temp_section);
        			
        			foreach ( $students1 as $sStudents )
        			{
        				$valueNotesSync[] = "($sStudents[student_id], NOW(), $input[notes_id], 'OFF' )";
        			}
        			
        			self::getNotesSyncTable()->saveForStudentBatch($valueNotesSync);
        			
        			$notifyData1 = json_encode( array(
					'type_id' 		=> $notify_type_id,
					'students'  	=> $students1,
					'school_id' 	=> $school_id,
					'class_id'		=> $temp_class,
					'section_id'	=> $temp_class,
					'teacher_id'	=> $teacher_id
					));
					
					Socket_Helper::write( array(
					'class_id'	 => $temp_class,
					'section_id' => $temp_section,
					'teacher_id' => 0
					));
					
					exec("/opt/lampp/bin/php /opt/lampp/htdocs/schoolerp/soap/ctp/application/helpers/Notification_Student.php '$notifyData1' > /dev/null &");
					*/
					
					
					
					$this->output['status'] = 1;
                    $this->output['message'] = 'success';
        		}
        		else 
        		{
        			$input['notes_shared_with'] = '';
        			self::getNotesTable()->updateNotes($input);
        			
        			self::getNotesLink()->saveBatch($input['notes_id'], $data['notes_link'] );
        			
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
	
	public function indexAction()
	{
		try {
		 	$school_id 		= $this->getParam('school_id');
         	$class_id 		= $this->getParam('class_id');
         	$section_id 	= $this->getParam('section_id');
         	$subject_id 	= $this->getParam('subject_id');
         	$date 			= $this->getParam('date');
         	$teacher_id 	= $this->getParam('teacher_id');
         	$student_id 	= $this->getParam('student_id');
         	$keyword 		= $this->getParam('keyword');
         	
         	if ( !empty( $student_id) )
         	{
         		if ( empty($school_id) && empty($class_id) && empty($section_id) )
         		{
         			throw new Exception('Missing parameters');
         		}
         		
         		if ( !empty($keyword) )
         		{
         			
         			$notes = self::getNotesTable()->getNotesStudentByKeyword( array(
         			'school_id'		=> $school_id,
         			'class_id'		=> $class_id,
         			'section_id'	=> $section_id,
         			'student_id'	=> $student_id,
         			'keyword'		=> $keyword
         			));
         			
         			if ( !sizeof($notes) )
         			{
         				throw new Exception('No results');
         			}
         			
         			$this->output['status'] = 1;
         			foreach ( $notes as $note )
         			{
         				$this->output['contents'][] = self::_output($note, 'Student', $student_id);
         			}
         			
         		}
         		elseif ( empty($subject_id) && empty($date) )
         		{
         			$notes = self::getNotesTable()->getNotesStudent( array(
         			'school_id'		=> $school_id,
         			'class_id'		=> $class_id,
         			'section_id'	=> $section_id,
         			'student_id'	=> $student_id
         			));
         			
         			if ( !sizeof($notes) )
         			{
         				throw new Exception('No results');
         			}
         			
         			$this->output['status'] = 1;
         			foreach ( $notes as $note )
         			{
         				$this->output['contents'][] = self::_output($note, 'Student', $student_id);
         			}
         		}
         		elseif ( empty($subject_id) && !empty($date) )
         		{
         			$notes = self::getNotesTable()->getNotesStudent( array(
         			'school_id'		=> $school_id,
         			'class_id'		=> $class_id,
         			'section_id'	=> $section_id,
         			'student_id'	=> $student_id,
         			'date'			=> $date
         			));
         			
         			if ( !sizeof($notes) )
         			{
         				throw new Exception('No results');
         			}
         			
         			$this->output['status'] = 1;
         			foreach ( $notes as $note )
         			{
         				$this->output['contents'][] = self::_output($note, 'Student', $student_id);
         			}
         		}
         		elseif ( !empty($subject_id) && empty($date) )
         		{
         			$notes = self::getNotesTable()->getNotesStudent( array(
         			'school_id'		=> $school_id,
         			'class_id'		=> $class_id,
         			'section_id'	=> $section_id,
         			'student_id'	=> $student_id,
         			'subject_id'	=> $subject_id
         			));
         			
         			if ( !sizeof($notes) )
         			{
         				throw new Exception('No results');
         			}
         			
         			$this->output['status'] = 1;
         			foreach ( $notes as $note )
         			{
         				$note['subject_id'] = '';
         				$this->output['contents'][] = self::_output($note, 'Student', $student_id);
         			}
         		}
         		elseif ( !empty($subject_id) && !empty($date) )
         		{
         			$notes = self::getNotesTable()->getNotesStudent( array(
         			'school_id'		=> $school_id,
         			'class_id'		=> $class_id,
         			'section_id'	=> $section_id,
         			'student_id'	=> $student_id,
         			'subject_id'	=> $subject_id,
         			'date'			=> $date
         			));
         			
         			if ( !sizeof($notes) )
         			{
         				throw new Exception('No results');
         			}
         			
         			$this->output['status'] = 1;
         			foreach ( $notes as $note )
         			{
         				$note['subject_id'] = '';
         				$this->output['contents'][] = self::_output($note, 'Student', $student_id);
         			}
         		}
         		
         	}
         	elseif ( !empty($teacher_id) )
         	{
         		if ( !empty( $keyword) )
         		{
         			$notes = self::getNotesTable()->getByKeyword( array(
         			'school_id'		=> $school_id,
         			'teacher_id'	=> $teacher_id,
         			'keyword'		=> $keyword
         			));
         			
         			if ( !sizeof($notes) )
         			{
         				throw new Exception('No results');
         			}
         			
         			$this->output['status'] = 1;
         			foreach ( $notes as $note )
         			{
         				$this->output['contents'][] = self::_output($note, 'Teacher', $teacher_id);
         			}
         		}
         		elseif ( empty($class_id) && empty($section_id) && empty($subject_id) && empty($date) )
         		{
         			$notes = self::getNotesTable()->getByTeacherN( array(
         			'school_id'		=> $school_id,
         			'teacher_id'	=> $teacher_id
         			));
         			
         			if ( !sizeof($notes) )
         			{
         				throw new Exception('No results');
         			}
         			
         			$this->output['status'] = 1;
         			foreach ( $notes as $note )
         			{
         				$this->output['contents'][] = self::_output($note, 'Teacher', $teacher_id);
         			}
         		}
         		elseif ( empty($class_id) && empty($section_id) && empty($subject_id) && !empty($date) )
         		{
         			$notes = self::getNotesTable()->getByTeacherN( array(
         			'school_id'		=> $school_id,
         			'teacher_id'	=> $teacher_id,
         			'date'			=> $date
         			));
         			
         			if ( !sizeof($notes) )
         			{
         				throw new Exception('No results');
         			}
         			
         			$this->output['status'] = 1;
         			foreach ( $notes as $note )
         			{
         				$this->output['contents'][] = self::_output($note, 'Teacher', $teacher_id);
         			}
         		}
         		elseif ( empty($class_id) && empty($section_id) && !empty($subject_id) && empty($date) )
         		{
         			$notes = self::getNotesTable()->getByTeacherN( array(
         			'school_id'		=> $school_id,
         			'teacher_id'	=> $teacher_id,
         			'subject_id'	=> $subject_id
         			));
         			
         			if ( !sizeof($notes) )
         			{
         				throw new Exception('No results');
         			}
         			
         			$this->output['status'] = 1;
         			foreach ( $notes as $note )
         			{
         				$note['subject_id'] = '';
         				$this->output['contents'][] = self::_output($note, 'Teacher', $teacher_id);
         			}
         		}
         		elseif ( empty($class_id) and empty($section_id) and !empty($subject_id) and !empty($date) )
         		{
         			
         		}
         		elseif ( empty($class_id) and !empty($section_id) and empty($subject_id) and empty($date) )
         		{
         			
         		}
         		elseif ( empty($class_id) and !empty($section_id) and empty($subject_id) and !empty($date) )
         		{
         			
         		}
         		elseif ( empty($class_id) && !empty($section_id) && !empty($subject_id) && empty($date) )
         		{
         			$notes = self::getNotesTable()->getByTeacherN( array(
         			'school_id'		=> $school_id,
         			'teacher_id'	=> $teacher_id,
         			'subject_id'	=> $subject_id,
         			'section_id'	=> $section_id
         			));
         			
         			if ( !sizeof($notes) )
         			{
         				throw new Exception('No results');
         			}
         			
         			$this->output['status'] = 1;
         			foreach ( $notes as $note )
         			{
         				$note['subject_id'] = '';
         				$note['section_id'] = '';
         				$this->output['contents'][] = self::_output($note, 'Teacher', $teacher_id);
         			}
         		}
         		elseif ( empty($class_id) && !empty($section_id) && !empty($subject_id) && !empty($date) )
         		{
         			
         		}
         		elseif ( !empty($class_id) && empty($section_id) && empty($subject_id) && empty($date) )
         		{
         			$notes = self::getNotesTable()->getByTeacherN( array(
         			'school_id'		=> $school_id,
         			'teacher_id'	=> $teacher_id,
         			'class_id'		=> $class_id
         			));
         			
         			if ( !sizeof($notes) )
         			{
         				throw new Exception('No results');
         			}
         			
         			$this->output['status'] = 1;
         			foreach ( $notes as $note )
         			{
         				$note['class_id'] = '';
         				$this->output['contents'][] = self::_output($note, 'Teacher', $teacher_id);
         			}
         		}
         		elseif ( !empty($class_id) && empty($section_id) && empty($subject_id) && !empty($date) )
         		{
         			$notes = self::getNotesTable()->getByTeacherN( array(
         			'school_id'		=> $school_id,
         			'teacher_id'	=> $teacher_id,
         			'class_id'		=> $class_id,
         			'date'			=> $date
         			));
         			
         			if ( !sizeof($notes) )
         			{
         				throw new Exception('No results');
         			}
         			
         			$this->output['status'] = 1;
         			foreach ( $notes as $note )
         			{
         				$note['class_id'] = '';
         				$this->output['contents'][] = self::_output($note, 'Teacher', $teacher_id);
         			}
         		}
         		elseif ( !empty($class_id) && empty($section_id) && !empty($subject_id) && empty($date) )
         		{
         			
         		}
         		elseif ( !empty($class_id) && empty($section_id) && !empty($subject_id) && !empty($date) )
         		{
         			
         		}
         		elseif ( !empty($class_id) && !empty($section_id) && empty($subject_id) && empty($date) )
         		{
         			$notes = self::getNotesTable()->getByTeacherN( array(
         			'school_id'		=> $school_id,
         			'teacher_id'	=> $teacher_id,
         			'class_id'		=> $class_id,
         			'section_id'	=> $section_id
         			));
         			
         			if ( !sizeof($notes) )
         			{
         				throw new Exception('No results');
         			}
         			
         			$this->output['status'] = 1;
         			foreach ( $notes as $note )
         			{
         				$note['class_id'] = '';
         				$note['section_id'] = '';
         				$this->output['contents'][] = self::_output($note, 'Teacher', $teacher_id);
         			}
         		}
         		elseif ( !empty($class_id) && !empty($section_id) && empty($subject_id) && !empty($date) )
         		{
         			$notes = self::getNotesTable()->getByTeacherN( array(
         			'school_id'		=> $school_id,
         			'teacher_id'	=> $teacher_id,
         			'class_id'		=> $class_id,
         			'section_id'	=> $section_id,
         			'date'			=> $date
         			));
         			
         			if ( !sizeof($notes) )
         			{
         				throw new Exception('No results');
         			}
         			
         			$this->output['status'] = 1;
         			foreach ( $notes as $note )
         			{
         				$note['class_id'] = '';
         				$note['section_id'] = '';
         				$this->output['contents'][] = self::_output($note, 'Teacher', $teacher_id);
         			}
         		}
         		elseif ( !empty($class_id) && !empty($section_id) && !empty($subject_id) && empty($date) )
         		{
         			$notes = self::getNotesTable()->getByTeacherN( array(
         			'school_id'		=> $school_id,
         			'teacher_id'	=> $teacher_id,
         			'class_id'		=> $class_id,
         			'section_id'	=> $section_id,
         			'subject_id'	=> $subject_id
         			));
         			
         			if ( !sizeof($notes) )
         			{
         				throw new Exception('No results');
         			}
         			
         			$this->output['status'] = 1;
         			foreach ( $notes as $note )
         			{
         				$note['class_id'] = '';
         				$note['section_id'] = '';
         				$note['subject_id'] = '';
         				$this->output['contents'][] = self::_output($note, 'Teacher', $teacher_id);
         			}
         		}
         		elseif ( !empty($class_id) && !empty($section_id) && !empty($subject_id) && !empty($date) )
         		{
         			$notes = self::getNotesTable()->getByTeacherN( array(
         			'school_id'		=> $school_id,
         			'teacher_id'	=> $teacher_id,
         			'class_id'		=> $class_id,
         			'section_id'	=> $section_id,
         			'subject_id'	=> $subject_id,
         			'date'			=> $date
         			));
         			
         			if ( !sizeof($notes) )
         			{
         				throw new Exception('No results');
         			}
         			
         			$this->output['status'] = 1;
         			foreach ( $notes as $note )
         			{
         				$note['class_id'] = '';
         				$note['section_id'] = '';
         				$note['subject_id'] = '';
         				$this->output['contents'][] = self::_output($note, 'Teacher', $teacher_id);
         			}
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
	
	protected static function _output($data, $user_type, $uid)
	{
		$nstatus = '';
		if ( $user_type == 'Student')
		{
			if ( sizeof( self::getNotesTable()->createdStudent($data['notes_id'], $uid) ) )
			{
				$nstatus = 'Created';
			}
			elseif ( sizeof( self::getNotesTable()->sharedStudent($data['notes_id'], $uid) ))
			{
				$nstatus = 'Shared';
			}
			elseif ( sizeof( self::getNotesTable()->recievedStudent($data['notes_id'], $uid)))
			{
				$nstatus = 'Recieved';
			}
		}
		else 
		{
			if ( sizeof( self::getNotesTable()->sharedTeacher($data['notes_id'], $uid) ) )
			{
				$nstatus = 'Shared';
			}
			elseif ( sizeof( self::getNotesTable()->createdTeacher($data['notes_id'], $uid) ) )
			{
				$nstatus = 'Created';
			}
			elseif ( sizeof( self::getNotesTable()->recievedTeacher($data['notes_id'], $uid)) )
			{
				$nstatus = 'Recieved';
			}
		}
		
		$noteslinks = array();
		$noteslink = self::getNotesLink()->getByNotesId($data['notes_id']);
		if ( sizeof($noteslink))
		{
			foreach ( $noteslink as $nl )
			{
				$noteslinks[] = array(
				'word'	=> $nl['notes_word'],
				'link'	=> $nl['notes_link']
				);
			}
		}
		
		if ( $data['teacher_id'] )
		{
			$sname = self::getStaffTable()->getFullname($data['teacher_id']);
		}
		else 
		{
			$sname = self::getStudentTable()->getInfoById($data['student_id'], 'fname').' '.self::getStudentTable()->getInfoById($data['student_id'], 'lname');
		}
		
		$posted_to = array();
		
		if ( $data['notes_shared_with'] == 'class')
		{
			$posted_to[] = self::getClassTable()->getName($data['school_id'], $data['class_id']).' '.self::getSectionTable()->getName($data['school_id'], $data['section_id']);
		}
		elseif ( $data['notes_shared_with'] == 'group' )
		{
			$ps = self::getNotesGroupTable()->getGroupById($data['notes_id'] );
			if ( sizeof($ps) )
			{
				foreach ( $ps as $p)
				{
					$class_name = self::getClassTable()->getName($data['school_id'], $data['class_id']);
					$section_name = self::getSectionTable()->getName($data['school_id'], $data['section_id']);
					//$subject_name = self::getSubjectTable()->getName($data['subject_id']);
					$group_name = self::getGroupTable()->getInfoById($p['group_id'], 'ctp_group_name');
					$posted_to[] = $class_name.'-'.$section_name.'-'.$group_name;
 				}
			}
		}
		elseif ( $data['notes_shared_with'] == 'teacher' )
		{
			$ps = self::getNotesShareTable()->getPostedToTeacher($data['notes_id']);
			if ( sizeof($ps) )
			{
				foreach ( $ps as $p )
				{
					$posted_to[] = self::getStaffTable()->getFullname($p['teacher_id']);
				}
			}
		}
		elseif ( $data['notes_shared_with'] == 'student' )
		{
			$ps = self::getNotesShareTable()->getPostedToStudent($data['notes_id']);
			if ( sizeof($ps) )
			{
				foreach ( $ps as $p )
				{
					$posted_to[] = self::getStudentTable()->getInfoById($p['student_id'], 'fname').' '.self::getStudentTable()->getInfoById($p['student_id'], 'lname');
				}
			}
		}
		elseif ( $data['notes_shared_with'] == 'public' )
		{
			$posted_to = array('public');
		}
		
		return array(
		'notes_id'	=> $data['notes_id'],
		'notes_title'	=> html_entity_decode($data['notes_title'], ENT_QUOTES),
		'attachments'	=> Attachment_Helper::getAttachment($data['notes_filename']),
		'school_id'		=> $data['school_id'],
		'class_id'		=> $data['class_id'],
		'class_name'	=> self::getClassTable()->getName($data['school_id'], $data['class_id']),
		'section_id'	=> $data['section_id'],
		'section_name'	=> self::getSectionTable()->getName($data['school_id'], $data['section_id']),
		'subject_id'	=> $data['subject_id'],
		'subject_name'	=> self::getSubjectTable()->getName($data['subject_id']),
		'notes_text'	=> html_entity_decode($data['notes_text'], ENT_QUOTES),
		'notes_author'	=> $data['notes_author'],
		'date_created'	=> date('Y-m-d', strtotime($data['notes_date_created']) ),
		'notes_status'	=> $data['notes_status'],
		'shared_status' => $nstatus,
		'notes_link'	=> $noteslinks,
		'shared_by'		=> $sname,
		'shared_with'	=> $data['notes_shared_with'],
		'posted_to'		=> implode(',', $posted_to)
		
		);
	}
	
	protected static function sendNotificationToMembers($notify_type_id, $teacher_id, $group_id, $school_id, $notes_id) {

        $members = self::getGroupMemberTable()->getMembersByGroupId($group_id);
        
        self::getNotesSyncTable()->saveForTeacher($teacher_id, $notes_id);

        if (sizeof($members)) {
        	
            foreach ($members as $member) {
                $class_id = (int)self::getGroupTable()->getInfoById($group_id, 'class_id');
                $section_id = (int)self::getGroupTable()->getInfoById($group_id, 'section_id');

                self::getNotifyTable()->saveForStudent(array(
                    'type_id' => $notify_type_id,
                    'school_id' => $school_id,
                    'class_id' => $class_id,
                    'section_id' => $section_id,
                    'student_id' => (int)$member,
                    'notify_by' => 'Teacher',
                    'notify_by_id' => $teacher_id
                ));

			self::getNotesSyncTable()->saveForStudent((int)$member, $notes_id);
        
            }
            Socket_Helper::write( array(
            'class_id'	=> $class_id,
            'section_id'	=> $section_id,
            'teacher_id'	=> 0
            ));
        }
    }
    
	protected static function sendNotificationToClass($notify_type_id, $teacher_id, $school_id, $class_id, $section_id, $notes_id) {
        self::getNotesSyncTable()->saveForTeacher($teacher_id, $notes_id);

        $student_list = self::getStudentTable()->getStudentList($school_id, $class_id, $section_id);

        foreach ($student_list as $student) {
        	
            self::getNotifyTable()->saveForStudent(array(
                'type_id' => $notify_type_id,
                'school_id' => $school_id,
                'class_id' => $class_id,
                'section_id' => $section_id,
                'student_id' => (int)$student['sid'],
                'notify_by' => 'Teacher',
                'notify_by_id' => $teacher_id
            ));
            
           self::getNotesSyncTable()->saveForStudent((int)$student['sid'], $notes_id);
        }
        Socket_Helper::write( array(
            'class_id'	=> $class_id,
            'section_id'=> $section_id,
        	'teacher_id' => 0
         ));
    }
    
    
}
