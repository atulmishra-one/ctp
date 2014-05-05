<?php

class Api_Model_AssignmentTopic extends Zend_Db_Table_Abstract
{
	protected $_name = 'ctp_assignment_topic';
	
	
    public function save(array $data)
	{
		
		  return $this->insert( array(
				'assignment_master_id'    => (int)$data['assignment_id'],
				'date_created'			  => new Zend_Db_Expr('NOW()'),
				'version'           	  => 0,
				'mode'					  => (string)$data['mode'],
				'assignment_topic_status' => strtoupper((string)$data['assigned_status']),
                'target_submission_date'  => $data['target_date']
			) );
		
	}
    
    public function updateTopic(array $data)
	{
		
		  return $this->update( array(
				'date_modified'			  => new Zend_Db_Expr('NOW()'),
				'version'           	  => 0,
				'mode'					  => (string)$data['mode'],
				'assignment_topic_status' => (string)$data['assigned_status'],
                'target_submission_date'  => $data['target_date']
			) , array(
				'assignment_master_id=?' => (int)$data['assignment_id']
			)  );
		
	}
	
	public function get($assignment_master_id, $str)
	{
		
		$row = $this->fetchRow(
			$this->select()
			->where('assignment_master_id=?', $assignment_master_id)
			->limit(1, 0)
		);
		
		return ( count($row)) ?$row->$str: '';
	}
	
	public function remove($id)
	{
		 return $this->delete( array( 'assignment_master_id=?' => $id ));
	}
	
	public function updateTopicStatus($id)
	{
		$this->update( array('assignment_topic_status' =>'YES'), array('assignment_master_id=?' => $id) );
	}
}






