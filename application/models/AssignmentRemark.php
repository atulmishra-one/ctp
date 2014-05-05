<?php

class Api_Model_AssignmentRemark extends Zend_Db_Table_Abstract
{
	protected $_name = 'assignment_remark';
	
	public function save(array $data)
	{
		
		  return $this->insert( array(
				'assignment_id'  		=> $data['assignment_master_id'],
				'remark'				=> $data['remark'],
				'status'				=> $data['status'],
				'added_date'			=> new Zend_Db_Expr('NOW()'),
				'staff_id'           	=> $data['teacher_id'],
				'student_id' 			=> $data['student_id'],
				'subject_id'			=> $data['subject_id'],
                'school_auto_id'        => $data['school_id'],
                'show_remarks'          => $data['show_remarks'],
                'show_marks'            => $data['show_marks'],
                'marks'                 => $data['marks']
			) );
		
	}
    
    public function updateRemark(array $data)
	{
		
		  return $this->update( array(
				'remark'				=> $data['remark'],
				'status'				=> $data['status'],
				'added_date'			=> new Zend_Db_Expr('NOW()'),
				'subject_id'			=> $data['subject_id'],
                'school_auto_id'        => $data['school_id'],
                'show_remarks'          => $data['show_remarks'],
                'show_marks'            => $data['show_marks'],
                'marks'                 => $data['marks']
			), array('assignment_id=?'  => $data['assignment_master_id'], 'student_id=?' => $data['student_id'], 'staff_id=?' => $data['teacher_id']) );
		
	}
    
   	public function get($assignment_master_id, $student_id, $teacher_id)
	{
		
		$row = $this->fetchRow(
			$this->select()
			->where('assignment_id=?', $assignment_master_id)
            ->where('student_id=?', $student_id)
            ->where('staff_id=?', $teacher_id)
			->limit(1, 0)
		);
		
		return ( count($row)) ? true: false;
	}
	
	public function hasRemark($assignment_master_id, $student_id, $teacher_id)
	{
		$sql = "
		SELECT id FROM $this->_name WHERE 
		assignment_id=$assignment_master_id AND student_id=$student_id AND staff_id=$teacher_id LIMIT 1
		";
		
		$row = Zend_Db_Table::getDefaultAdapter()->query($sql)->fetch();
		return $row['id'];
	}
	
    
	
	public function getByAssignment($assignment_master_id, $str,$student_id)
	{
		
		$row = $this->fetchRow(
			$this->select()
			->where('assignment_id=?', $assignment_master_id)
			->where('student_id=?', $student_id)
			->limit(1, 0)
		);
		
		return ( count($row)) ?$row->$str: '';
	}
    
   	public function getByAssignmentAndStudent($assignment_master_id, $str,$student_id)
	{
		
		$row = $this->fetchRow(
			$this->select()
			->where('assignment_id=?', $assignment_master_id)
            ->where('student_id=?', $student_id)
			->limit(1, 0)
		);
		
		return ( count($row)) ?$row->$str: '';
	}
	
	public function getTotal( $id )
	{
		$sql = "SELECT count(assignment_id) as total FROM $this->_name WHERE assignment_id=$id";
		
		$row=  Zend_Db_Table::getDefaultAdapter()->query($sql)->fetch();
		return $row['total'];
	}
}
