<?php

class Api_Model_AssignmentStudent extends Zend_Db_Table_Abstract
{
	protected $_name = 'assignment_student';
	
	public function save(array $data)
	{
		if( sizeof($this->getByStudentAndAssignment($data['student_id'] , $data['assignment_master_id'])))
        {
		   return $this->update( array(
				'staff_id' 			=> $data['teacher_id'],
				'submission_date'	=> new Zend_Db_Expr('TIMESTAMPADD(MINUTE,1,NOW())'),
				'status'			=> $data['status'],
				'content'			=> $data['content'],
				'added_by'			=> 'Student',
				'subject_id'		=> $data['subject_id'],
				'upload_file'       => $data['attachments'],
				'assignment_status' => $data['assignment_status'],
                'school_auto_id'    => $data['school_id']
			), array('student_id=?' => $data['student_id'], 'assignment_id=?' => $data['assignment_master_id']) );
            
        }
        else
        {
           
            return $this->insert( array(
				'student_id'  		=> $data['student_id'],
				'staff_id' 			=> $data['teacher_id'],
				'submission_date'	=> new Zend_Db_Expr('TIMESTAMPADD(MINUTE,1,NOW())'),
				'status'			=> $data['status'],
				'content'			=> $data['content'],
				'added_by'			=> 'Student',
				'subject_id'		=> $data['subject_id'],
				'upload_file'       => $data['attachments'],
				'assignment_id'		=> $data['assignment_master_id'],
				'assignment_status' => $data['assignment_status'],
                'school_auto_id'    => $data['school_id']
			) );
        }
		
	}
	
	public function getInfoById($assignment_id, $student_id, $str)
	{
		  $row = $this->fetchRow(
			$this->select()
			->where('student_id=?', $student_id)
			->where('assignment_id=?', $assignment_id)
			->limit(1, 0)
		);
		
		return ( count($row) )? $row->$str:'';
	}
	
	public function isSumitted($assignment_master_id, $student_id, $date){
		 /*$row = $this->fetchRow(
			$this->select()
			->where('student_id=?', $student_id)
			->where('assignment_id=?', $assignment_master_id)
            ->where('DATE(submission_date) <= ?', (string)$date)
            ->orWhere('DATE(submission_date)= ?', (string)$date)
			->limit(1, 0)
		);
		
		return ( count($row) )? true:false;
		*/
		$sql = "SELECT * FROM `assignment_student` 
		WHERE `student_id`=$student_id AND `assignment_id`=$assignment_master_id 
		AND ( ( DATE(`submission_date`) <= '$date' ) or ( DATE(`submission_date`)='$date' ) )";
		
		
		$row = Zend_Db_Table::getDefaultAdapter()->query($sql)->fetchAll();
		//print_r($row);
		return ( count( $row ) > 0 ) ? 1 : 0;
	}
	
	public function isPending($assignment_master_id, $student_id){
		
		$sql = "SELECT * FROM $this->_name WHERE student_id=$student_id AND assignment_id=$assignment_master_id LIMIT 1";
		$row = Zend_Db_Table::getDefaultAdapter()->query($sql)->fetchAll();
		
		return ( count($row) > 0 )? 1:0;
	}
    
    public function isLate($assignment_master_id, $student_id, $date){
		/* $row = $this->fetchAll(
			$this->select()
            ->where('student_id=?', $student_id)
			->where('assignment_id=?', $assignment_master_id)
            ->where('DATE(submission_date) > ?', (string)$date)
			->limit(1, 0)
		);
		*/
		$sql = "SELECT * FROM $this->_name WHERE student_id=$student_id AND assignment_id=$assignment_master_id
		AND DATE(submission_date) > '$date' ";
		
		$row = Zend_Db_Table::getDefaultAdapter()->query($sql)->fetchAll();
		return ( count($row) > 0 )? 1:0;
	}
	
	public function remove($id)
	{
		 return $this->delete( array( 'id=?' => $id ));
	}
    
    public function getTotal($assignment_id)
    {
        $row = $this->fetchAll(
			$this->select()
			->where('assignment_id=?', $assignment_id)
		);
		return count($row);
    }
    
    public function getByStudentAndAssignment($student_id, $assignment_id)
    {
        $row = $this->fetchAll(
			$this->select()
            ->where('student_id=?', $student_id)
			->where('assignment_id=?', $assignment_id)
		)->toArray();
		
		return ( count($row) )? $row: array();
    }
    
	public function getByStudentAndAssignmentId($student_id, $assignment_id)
    {
        $sql = "
        SELECT sa.*, a.submission_date as target_submission_date FROM $this->_name sa, assignment_master a 
        WHERE
        sa.assignment_id=a.id
        AND
        sa.student_id=$student_id
        AND
        sa.assignment_id=$assignment_id
        ";
        
        return Zend_Db_Table::getDefaultAdapter()->query($sql)->fetchAll();
    }
    
    
}