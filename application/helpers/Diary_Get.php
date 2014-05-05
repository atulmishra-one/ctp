<?php

include_once APPLICATION_PATH.'/models/DiaryMaster.php';
include_once APPLICATION_PATH.'/models/DiaryShare.php';
include_once APPLICATION_PATH.'/models/Staff.php';
include_once APPLICATION_PATH.'/models/GetClass.php';
include_once APPLICATION_PATH.'/models/GetSection.php';
include_once APPLICATION_PATH.'/models/GetSubject.php';
include_once APPLICATION_PATH.'/models/GroupTable.php';
include_once APPLICATION_PATH.'/models/Student.php';
include_once APPLICATION_PATH.'/helpers/Attachment.php';

class Diary_Get
{ 
	
	protected static function getGroupTable()
	{
		return new Api_Model_GroupTable();
	}
	
	protected static function getStudentTable()
	{
		return new Api_Model_Student();
	}
	protected static function getSubjectTable()
	{
		return new Api_Model_GetSubject();
	}
	
	protected static function getDiaryMasterTable()
	{
		return new Api_Model_DiaryMaster();
	}
	
	protected static function getClassTable()
	{
		return new Api_Model_GetClass();
	}
	
	protected static function getDiaryShareTable()
	{
		return new Api_Model_DiaryShare();
	}
	
	protected static function getSectionTable()
	{
		return new Api_Model_GetSection();
	}
	
	protected static function getStaffTable()
	{
		return new Api_Model_Staff();
	}
	
	public static function getResultsStudent( $data)
	{
		if ( !isset($data) )
		{
			throw new Exception('Please provide input');
		}
		
		$student_id = self::getStudentTable()->getInfo($data['user_id'], 'id');
		
		$date = date('Y-m-d', time());
		
		if ( !empty( $data['date']) )
		{
			$output = self::getDiaryMasterTable()
			->getResultsForStudent($data['school_id'], $data['class_id'], $data['section_id'], $data['user_id'], $student_id, $data['date']);
		}
		else 
		{
			$output = self::getDiaryMasterTable()
			->getResultsForStudent($data['school_id'], $data['class_id'], $data['section_id'], $data['user_id'], $student_id);
		}
		
		$notices = self::getDiaryMasterTable()->notice($data['section_id'], $date, 'Section');
		$circulars = self::getDiaryMasterTable()->circular($data['user_id'], $date, 'Section');
		
		$result = array();
		foreach ( $output as $out )
		{
			$out['mid'] = $out['id'];
			$result[] = self::_output($out, $data['user_id']);
		}
		
		if ( sizeof($notices) )
		{
			foreach ( $notices as $notice )
			{
				$notice['mid'] = 0;
				$notice['text'] = $notice['title'];
				$notice['attachments'] = self::announcementFile($notice['file']);
				$notice['school_id']	= $data['school_id'];
				$notice['class_id']	= $data['class_id'];
				$notice['section_id'] = $data['section_id'];
				$notice['subject_id'] = '';
				$notice['date_created'] = $notice['start_date'];
				$notice['shared_with'] = '';
				$notice['user_id'] = $data['user_id'];
				$notice['user_type'] = 'Admin';
				$result[] = self::_output($notice, $data['user_id']);
			}
		}
		
		if ( sizeof($circulars) )
		{
			foreach ( $circulars as $circular )
			{
				$circular['mid'] = 0;
				$circular['text'] = $circular['title'];
				$circular['attachments'] = self::announcementFile($circular['file']);
				$circular['school_id']	= $circular['school_id'];
				$circular['class_id']	= $circular['class_id'];
				$circular['section_id'] = $circular['section_id'];
				$circular['subject_id'] = '';
				$circular['date_created'] = $circular['start_date'];
				$circular['shared_with'] = '';
				$circular['user_id'] = $data['user_id'];
				$circular['user_type'] = 'Admin';
				$result[] = self::_output($circular, $data['user_id']);
			}
		}
		
		return $result;
	}
	public static function getResultsTeacher( $data )
	{
		if ( !isset( $data) )
		{
			throw new Exception('Please provide input');
		}
		
		$teacher_id = self::getStaffTable()->getId($data['user_id']);

		$sharedIds = self::getDiaryShareTable()->getMasterIdsByTeacher( $teacher_id);
		if ( !sizeof($sharedIds) )
		{
			$sharedIds = array('0');
		}
		
		$sharedWithSelf = self::getDiaryMasterTable()->getResultsByIdTeacher($teacher_id , $sharedIds);
		
		$materDiaryResult = self::getDiaryMasterTable()->getResultsForTeacher('TEACHER', $data, $teacher_id);
		
		$output = array_merge($sharedWithSelf, $materDiaryResult);
		
		$output = array_intersect_key($output, array_unique(array_map('serialize', $output)));
		
		$date = date('Y-m-d', time());
		
		$notices = self::getDiaryMasterTable()->notice($data['user_id'], $date, 'Teacher');
		
		$circulars = self::getDiaryMasterTable()->circular($data['user_id'], $date, 'Teacher');
		
		$result = array();
		
		foreach ( $output as $out )
		{
			$result[] = self::_output($out, $data['user_id']);
		}
		
		if ( sizeof($notices) )
		{
			foreach ( $notices as $notice )
			{
				$notice['mid'] = 0;
				$notice['text'] = $notice['title'];
				$notice['attachments'] = self::announcementFile($notice['file']);
				$notice['school_id'] = $data['school_id'];
				$notice['class_id']	 = $data['class_id'];
				$notice['section_id'] = $data['section_id'];
				$notice['subject_id'] = '';
				$notice['date_created'] = $notice['start_date'];
				$notice['shared_with']  = '';
				$notice['user_id']  = $data['user_id'];
				$notice['user_type'] = 'Admin';
				$result[] = self::_output($notice, $data['user_id']);
			}
		}
		
		if ( sizeof($circulars) )
		{
			foreach ( $circulars as $circular )
			{
				$circular['mid'] = 0;
				$circular['text'] = $circular['title'];
				$circular['attachments'] = self::announcementFile($circular['file']);
				$circular['school_id'] = $circular['school_id'];
				$circular['class_id']	 = $circular['class_id'];
				$circular['section_id'] = $circular['section_id'];
				$circular['subject_id'] = '';
				$circular['date_created'] = $circular['start_date'];
				$circular['shared_with']  = '';
				$circular['user_id']  = $circular['user_id'];
				$circular['user_type'] = 'Admin';
				$result[] = self::_output($circular, $data['user_id']);
			}
		}
		
		return $result;
	}
	
	protected static function announcementFile($file)
	{
		$file = array('upload/microsite/announcement/' . $file);
        return serialize($file); 
	}
	
	protected static function getPostedToClass( $mid, $sid)
	{
		$ps = self::getDiaryShareTable()->getPostedByClass($mid);
		$postedTo = array();
		if ( sizeof( $ps) )
		{
			foreach ( $ps as $p )
			{
				$postedTo[] = self::getClassTable()->getName($sid, $p['class_id']).' '.self::getSectionTable()->getName($sid, $p['section_id']);
			}
		}
		
		return $postedTo;
	}
	
	protected static function getPostedToGroup( $mid, $sid)
	{
		$ps = self::getDiaryShareTable()->getPostedByGroup($mid);
		$postedTo = array();
		if ( sizeof($ps) )
		{
			foreach ( $ps as $p )
			{
				$class_name = self::getClassTable()
				->getName($sid, self::getGroupTable()->getInfoById( $p['group_id'], 'class_id'));
				
				$section_name = self::getSectionTable()->getName($sid, self::getGroupTable()->getInfoById( $p['group_id'], 'section_id') );
				
				//$subject_name = self::getSubjectTable()->getName(self::getGroupTable()->getInfoById( $p['group_id'], 'subject_id'));
				$postedTo[] = $class_name.'-'.$section_name.' '.self::getGroupTable()->getInfoById( $p['group_id'], 'ctp_group_name');
			}
		}
		
		return $postedTo;
	}
	
	protected static function getPostedToteacher( $mid)
	{
		$ps = self::getDiaryShareTable()->getPostedByTeacher($mid);
		$postedTo = array();
		if ( sizeof($ps) )
		{
			foreach ( $ps as $p )
			{
				$postedTo[] = self::getStaffTable()->getFullname($p['teacher_id']);
			}
		}
		return $postedTo;
	}
	
	protected static function getPostedToStudent( $mid)
	{
		$ps = self::getDiaryShareTable()->getPostedByStudent($mid);
		$postedTo = array();
		if ( sizeof($postedTo) )
		{
			foreach ( $ps as $p )
			{
				$postedTo[] = self::getStudentTable()->getInfoById($p['student_id'], 'fname').' '.self::getStudentTable()->getInfoById($p['student_id'], 'lname');
			}
		}
		return $postedTo;
	}
	protected static function _output( $data , $user_id )
	{
		$dstatus = self::getDiaryMasterTable()->isCreated($data['mid'], $user_id)? 'created':'recieved';
		
		if ( $data['user_type'] == 'Admin')
		{
			$posted_by = 'Admin';
		}
		elseif ( $data['user_type'] == 'TEACHER') 
		{
			$posted_by = self::getStaffTable()->getFullname( $data['user_id'], true);
		}
		elseif ( $data['user_type'] == 'STUDENT')
		{
			$posted_by = self::getStudentTable()->getInfo($data['user_id'], 'fname').' '.self::getStudentTable()->getInfo($data['user_id'], 'lname');
		}
		
		
		$postedTo = array();
		
		if ( $data['shared_with'] == 'class')
		{
			$postedTo = self::getPostedToClass($data['mid'], $data['school_id']);
		}
		elseif ( $data['shared_with'] == 'group')
		{
			$postedTo = self::getPostedToGroup($data['mid'], $data['school_id']);
		}
		elseif ( $data['shared_with'] == 'teacher')
		{
			$postedTo = self::getPostedToteacher($data['mid']);
		}
		elseif ( $data['shared_with'] == 'student')
		{
			$postedTo = self::getPostedToStudent($data['mid']);
		}
		
		return array(
		'diary_id' 				=> $data['mid'],
		'type'	   				=> $data['type'],
		'text'	   				=> html_entity_decode($data['text'], ENT_QUOTES),
		'attachments'			=> Attachment_Helper::getAttachment($data['attachments']),
		'class_id'				=> $data['class_id'],
		'class_name'			=> $data['class_name'],
		'section_id'			=> $data['section_id'],
		'section_name'			=> $data['section_name'],
		'subject_id'			=> $data['subject_id'],
		'subject_name'			=> $data['subject_name'],
		'date_created' 			=> $data['date_created'],
		'diary_shared_status'	=> $dstatus,
		'shared_with'			=> $data['shared_with'],
		'posted_by'				=> $posted_by,
		'posted_to'				=> implode(',', $postedTo)
		);
	}
}


