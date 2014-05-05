<?php

class Api_Model_JoinStatus extends Zend_Db_Table_Abstract
{
	protected $_name = 'ctp_student_join_status';
	
	public function save(array $data)
	{
		if( $this->getStudent($data['student_id'] ) ) :
			 $this->update( array(
			'joined'		=> $data['status'],
			'date_created' => new Zend_Db_Expr('TIMESTAMPADD(MINUTE,1,NOW())')
		) , array(
			'student_id =?'=> $data['student_id']
		) );
		 return 1;
		else :
		 return $this->insert( array(
			'student_id' 	=> $data['student_id'],
			'joined'		=> $data['status'],
			'date_created'  => new Zend_Db_Expr('TIMESTAMPADD(MINUTE,1,NOW())')
		) );
		
		endif;
		
	}
	
	public function updateAttend($student_id)
	{
		 $this->update( array(
						'joined' => 0
			) , array(
					'student_id =?' => (int)$student_id
		) );
	}
	
	public function getStudent($id)
	{
		$row = $this->fetchAll(
			$this->select()
			->where('student_id=?', $id)
			->limit(1, 0)
		);
		
		return (count($row) )? 1: 0;
	}
	
	
	public function getStatus($student_id)
	{
	   $row = $this->fetchRow(
			$this->select()
			->where('student_id=?', $student_id)
			->where('DATE(date_created)=CURDATE()')
			->order('date_created DESC')
			->limit(1, 0)
		);
		
		return (count($row) )? $row->joined: 0;
		
	}
	
	public function getStatusArray( $ids)
	{
		$ids = implode(',', $ids);
		$sql = "
		SELECT joined, student_id FROM $this->_name WHERE
		student_id IN( $ids) AND DATE(date_created)=CURDATE()
		ORDER BY date_created DESC
		";
		//print $sql;
		return Zend_Db_Table::getDefaultAdapter()->query($sql)->fetchAll();
	}
}










