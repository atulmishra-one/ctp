<?php

class Api_Model_AttendenceMaster extends Zend_Db_Table_Abstract
{
	protected $_name = 'ctp_attendence_master';
	
	public function save(array $data)
	{
		
		  return $this->insert( array(
				'class_id' 			=> $data['class_id'],
				'section_id'		=> $data['section_id'],
				'teacher_id' 		=> $data['teacher_id'],
				'date_created'		=> new Zend_Db_Expr('NOW()'),
				'school_id'			=> $data['school_id'],
				'subject_id'		=> $data['subject_id']
			) );

	}
	
	 public function XtimeDiFFbyDate($class_id, $section_id, $teacher_id,$subject_id, $x_time)
	 {
		 
		/*$sql = $this->select()
			->where('class_id=?', $class_id)
			->where('section_id=?', $section_id)
			->where('teacher_id=?', $teacher_id)
			->where('subject_id=?', $subject_id)
			->where('TIMESTAMPDIFF(MINUTE, date_created, NOW() ) < (?)', $x_time)
            ->order('id desc')
            ->limit(1, 0);
			
		$row =  $this->fetchRow($sql);
		
		//echo $sql->__toString();
		
		return ( count($row) )? $row->id : 0;
		*/
	 	$sql = "SELECT id FROM $this->_name WHERE 
	 	class_id=$class_id
	 	AND
	 	section_id=$section_id
	 	AND
	 	teacher_id=$teacher_id
	 	AND
	 	subject_id=$subject_id
	 	AND
	 	TIMESTAMPDIFF(MINUTE, date_created, NOW() ) < $x_time
	 	ORDER BY id DESC
	 	LIMIT 1
	 	";
	 	//print $sql;
	 	$row = Zend_Db_Table::getDefaultAdapter()->query($sql)->fetch();
	 	return sizeof($row) ? $row['id'] : 0;
	 }
	
	public function getId($id)
	{
		  $row = $this->fetchRow(
			$this->select()
			->where('id=?', $id)
			->limit(1, 0)
		);
		
		return ( count($row) )? $row : array();
	}
	
	public function getByTeacherSchool($teacher_id, $school_id)
	{
		  $row = $this->fetchAll(
			$this->select()
			->where('teacher_id=?', (int)$teacher_id)
			->where('school_id=?', (int)$school_id)
			->order('date_created DESC')
		)->toArray();
		
		return ( count($row) )? $row : array();
	}
	
	public function getByTeacherSchoolDate($teacher_id, $school_id, $date)
	{
		
		$row = $this->fetchAll(
		 $this->select()
			->where('teacher_id=?', (int)$teacher_id)
			->where('school_id=?', (int)$school_id)
			->where('DATE(date_created)=?', $date)
			->order('date_created DESC')
		 )->toArray();
	
		//echo $row->__toString();
		return ( count($row) )? $row : array();
	}
	
	public function getByTeacherSchoolSubject($teacher_id, $school_id, $subject_id)
	{
		  $row = $this->fetchAll(
			$this->select()
			->where('teacher_id=?', (int)$teacher_id)
			->where('school_id=?', (int)$school_id)
			->where('subject_id=?', (int)$subject_id)
			->order('date_created DESC')
		)->toArray();
		
		return ( count($row) )? $row : array();
	}
	public function getByTeacherSchoolSectionSubject($teacher_id, $school_id, $section_id, $subject_id)
	{
		  $row = $this->fetchAll(
			$this->select()
			->where('teacher_id=?', (int)$teacher_id)
			->where('school_id=?', (int)$school_id)
			->where('subject_id=?', (int)$subject_id)
			->where('section_id=?', (int)$section_id)
			->order('date_created DESC')
		)->toArray();
		
		return ( count($row) )? $row : array();
	}
	
	public function getByTeacherSchoolClass($teacher_id, $school_id, $class_id)
	{
		  $row = $this->fetchAll(
			$this->select()
			->where('teacher_id=?', (int)$teacher_id)
			->where('school_id=?', (int)$school_id)
			->where('class_id=?', (int)$class_id)
			->order('date_created DESC')
		)->toArray();
		
		return ( count($row) )? $row : array();
	}
	
	public function getByTeacherSchoolClassDate($teacher_id, $school_id, $class_id, $date)
	{
		
		  $row = $this->fetchAll(
			$this->select()
			->where('teacher_id=?', (int)$teacher_id)
			->where('school_id=?', (int)$school_id)
			->where('class_id=?', (int)$class_id)
			->where('DATE(date_created)=?', (string)$date)
			->order('date_created DESC')
		  )->toArray();
		
		return ( count($row) )? $row : array();
	}
	
	public function getByTeacherSchoolClassSection($teacher_id, $school_id, $class_id, $section_id)
	{
		  $row = $this->fetchAll(
			$this->select()
			->where('teacher_id=?', (int)$teacher_id)
			->where('school_id=?', (int)$school_id)
			->where('class_id=?', (int)$class_id)
			->where('section_id=?', (int)$section_id)
			->order('date_created DESC')
		)->toArray();
		
		return ( count($row) )? $row : array();
	}
	
	public function getByTeacherSchoolClassSectionDate($teacher_id, $school_id, $class_id, $section_id, $date)
	{
		
		  $row = $this->fetchAll(
			$this->select()
			->where('teacher_id=?', (int)$teacher_id)
			->where('school_id=?', (int)$school_id)
			->where('class_id=?', (int)$class_id)
			->where('section_id=?', (int)$section_id)
			->where('DATE(date_created)=?', (string)$date)
			->order('date_created DESC')
		 )->toArray();
		
		return ( count($row) )? $row : array();
	}
	
	public function getByTeacherSchoolClassSectionSubject($teacher_id, $school_id, $class_id, $section_id, $subject_id)
	{
		 $row = $this->fetchAll(
			$this->select()
			->where('teacher_id=?', (int)$teacher_id)
			->where('school_id=?', (int)$school_id)
			->where('class_id=?', (int)$class_id)
			->where('section_id=?', (int)$section_id)
			->where('subject_id=?', (int)$subject_id)
			->order('date_created DESC')
		)->toArray();
		
		return ( count($row) )? $row : array();
	}
	
	public function getOldId($teacher_id, $school_id, $class_id, $section_id, $subject_id)
	{
		/* $row = $this->fetchRow(
			$this->select()
			->where('teacher_id=?', (int)$teacher_id)
			->where('school_id=?', (int)$school_id)
			->where('class_id=?', (int)$class_id)
			->where('section_id=?', (int)$section_id)
			->where('subject_id=?', (int)$subject_id)
			->order('date_created DESC')
			->limit(1, 0)
		);
		
		return ( count($row) )? $row->id : 0;
		*/
		$sql = "SELECT id FROM $this->_name WHERE
		teacher_id= $teacher_id
		AND
		school_id=$school_id
		AND
		class_id=$class_id
		AND
		section_id=$section_id
		AND
		subject_id=$subject_id
		ORDER BY date_created DESC
		LIMIT 1
		";
		
		$row = Zend_Db_Table::getDefaultAdapter()->query($sql)->fetch();
		
		return sizeof( $row ) ? $row['id'] : 0;
	}
	
	public function getByTeacherSchoolClassSectionSubjectDate($teacher_id, $school_id, $class_id, $section_id, $subject_id, $date)
	{
		
		 $row = $this->fetchAll(
		  $this->select()
			->where('teacher_id=?', (int)$teacher_id)
			->where('school_id=?', (int)$school_id)
			->where('class_id=?', (int)$class_id)
			->where('section_id=?', (int)$section_id)
			->where('subject_id=?', (int)$subject_id)
			->where('DATE(date_created)=?', (string)$date)
			->order('date_created DESC')
		  )->toArray();
		
		return ( count($row) )? $row : array();
	}
	
	public function getBySchoolClassSection($school_id, $class_id, $section_id)
	{
		  $row = $this->fetchAll(
			$this->select()
			->where('school_id=?', (int)$school_id)
			->where('class_id=?', (int)$class_id)
			->where('section_id=?', (int)$section_id)
			->order('date_created DESC')
		)->toArray();
		
		return ( count($row) )? $row : array();
	}
	
	public function getBySchoolClassSectionDate($school_id, $class_id, $section_id, $date)
	{
		
		if( strlen( trim( $date) ) >= 1 && strlen( trim( $date) ) < 3 )
		{
			
		   $row = $this->fetchAll(
				$this->select()
			->where('school_id=?', (int)$school_id)
			->where('class_id=?', (int)$class_id)
			->where('section_id=?', (int)$section_id)
			->where('date_created BETWEEN DATE_SUB(NOW(), INTERVAL '.$date.' DAY) AND NOW() ')
			->order('date_created DESC')
		  )->toArray();
		}
		else
		{
		
		  $row = $this->fetchAll(
			$this->select()
			->where('school_id=?', (int)$school_id)
			->where('class_id=?', (int)$class_id)
			->where('section_id=?', (int)$section_id)
			->where('DATE(date_created)=?', (string)$date)
			->order('date_created DESC')
		  )->toArray();
		}
		
		
		return ( count($row) )? $row : array();
	}
	
	public function getBySchoolClassSectionSubject($school_id, $class_id, $section_id, $subject_id)
	{
		  $row = $this->fetchAll(
			$this->select()
			->where('school_id=?', (int)$school_id)
			->where('class_id=?', (int)$class_id)
			->where('section_id=?', (int)$section_id)
			->where('subject_id=?', (int)$subject_id)
			->order('date_created DESC')
		)->toArray();
		
		return ( count($row) )? $row : array();
	}
	
	public function getBySchoolClassSectionSubjectDate($school_id, $class_id, $section_id, $subject_id, $date)
	{
		if( strlen( trim( $date) ) >= 1 && strlen( trim( $date) ) < 3)
		{
			$row = $this->fetchAll(
			$this->select()
			->where('school_id=?', (int)$school_id)
			->where('class_id=?', (int)$class_id)
			->where('section_id=?', (int)$section_id)
			->where('subject_id=?', (int)$subject_id)
			->where('date_created BETWEEN DATE_SUB(NOW(), INTERVAL '.$date.' DAY) AND NOW() ')
			->order('date_created DESC')
		  )->toArray();
		}
		else
		{
		
		  $row = $this->fetchAll(
			$this->select()
			->where('school_id=?', (int)$school_id)
			->where('class_id=?', (int)$class_id)
			->where('section_id=?', (int)$section_id)
			->where('subject_id=?', (int)$subject_id)
			->where('DATE(date_created)=?', (string)$date)
			->order('date_created DESC')
		  )->toArray();
		}
		
		return ( count($row) )? $row : array();
	}
	
	public function getCurrentId($teacher_id, $school_id, $class_id, $section_id, $subject_id)
	{
		/* $row = $this->fetchRow(
			$this->select()
			->where('teacher_id=?', (int)$teacher_id)
			->where('school_id=?', (int)$school_id)
			->where('class_id=?', (int)$class_id)
			->where('section_id=?', (int)$section_id)
			->where('subject_id=?', (int)$subject_id)
            ->order('id desc')
			->limit(1, 0)
		);
		
		return ( count($row) )? $row->id : 0;
		*/
		$sql = "
		SELECT id FROM $this->_name WHERE
		teacher_id=$teacher_id
		AND
		school_id=$school_id
		AND
		class_id=$class_id
		AND
		section_id=$section_id
		AND
		subject_id=$subject_id
		ORDER BY id desc
		LIMIT 1
		";
		
		$row = Zend_Db_Table::getDefaultAdapter()->query($sql)->fetch();
		return sizeof($row) ? $row['id'] : 0;
	}
	
	public function XtimeDiFF($attendence_master_id, $x_time)
	 {
		
		//$sql = $this->select()
		//	->where('id=?', $attendence_master_id)
		//	->where('TIMESTAMPDIFF(MINUTE,date_created, NOW() ) < (?)', $x_time);
		  $sql = "SELECT id FROM ctp_attendence_master 
		WHERE id=$attendence_master_id 
		AND
		TIMESTAMPDIFF( MINUTE,date_created, NOW() ) < $x_time 
		";
	$row =  Zend_Db_Table::getDefaultAdapter()->query($sql)->fetch();
		
	return ( sizeof($row) )? true : false;
     }
 
 //// For Attendance issue make  Addons XtimeDiFF() Start
 public function XtimeDiFFExtra($attendence_master_id, $x_time)
	 {
		//$sql = $this->select()
		//	->where('id=?', $attendence_master_id)
		//	->where('TIMESTAMPDIFF(MINUTE,date_created, NOW() ) < (?)', $x_time);
		  $sql = "SELECT id FROM ctp_attendence_master 
		WHERE id=$attendence_master_id 
		AND
		TIMESTAMPDIFF( MINUTE,date_created, NOW() ) < $x_time 
		";
	 	$row =  Zend_Db_Table::getDefaultAdapter()->query($sql)->fetch();
		return $row;
	 }
  /// End 
	public function getLatestAttendence($teacher_id, $school_id, $class_id, $section_id, $subject_id)
	{
		 $row = $this->fetchRow(
			$this->select()
			->where('teacher_id=?', (int)$teacher_id)
			->where('school_id=?', (int)$school_id)
			->where('class_id=?', (int)$class_id)
			->where('section_id=?', (int)$section_id)
			->where('subject_id=?', (int)$subject_id)
            ->order('date_created desc')
		);
		
		return ( count($row) )? $row->id : 0;
	}
	
	public function getAttendenceRecords( $data )
	{
		$cond = "";
		
		if ( isset( $data['teacher_id']) )
		{
			$cond .= " and teacher_id = $data[teacher_id]";
		}
		if ( isset( $data['date']) )
		{
			if ( strlen( $data['date']) >=1 && strlen($data['date']) < 3  )
			{
				$cond .= " and date_created BETWEEN DATE_SUB(NOW(), INTERVAL $data[date] DAY) AND NOW() ";
			}
			else {
				$cond .= " and DATE(date_created)='$data[date]' ";
			}
			
		}
		if ( isset( $data['class_id']) )
		{
			$cond .= " and class_id=$data[class_id]";
		}
		if ( isset( $data['section_id']) )
		{
			$cond .= " and section_id=$data[section_id]";
		}
		if ( isset( $data['subject_id']) )
		{
			$cond .= " and subject_id=$data[subject_id]";
		}
		
		$sql = "
		SELECT * FROM $this->_name WHERE
		school_id=$data[school_id]
		$cond
		ORDER BY date_created DESC
		";
		//print $sql;
		return Zend_Db_Table::getDefaultAdapter()->query($sql)->fetchAll();
	}
	
}