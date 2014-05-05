<?php

class Api_Model_GroupHomework extends Zend_Db_Table_Abstract
{
    protected $_name = 'ctp_group_homework';
    
    
    public function save($id, $group_id)
    {
        $this->insert( array(
            'assignment_master_id' => $id,
            'group_id'             => $group_id
        ));
    }
    
    public function updateGroupHomework($id, $group_id)
    {
        $this->update( array(
            'group_id' => $group_id
        ) , array(
            'assignment_master_id=?' => (int)$id
		) );
    }
    
    public function getGroupId($id)
    {
       return $this->fetchAll(
			$this->select()
			->where('assignment_master_id=?', $id)
		)->toArray();
    }
    
    public function getTotal($id)
    {
       $row= $this->fetchAll(
			$this->select()
			->where('assignment_master_id=?', $id)
		);
        
        return count($row)? $row: array();
    }
    
    public function getAssignmentId($id)
    {
       return $this->fetchAll(
			$this->select()
			->where('group_id=?', $id)
		)->toArray();
    }
    
    public function hasAssignment($id)
    {
        $row = $this->fetchRow(
			$this->select()
            ->setIntegrityCheck(false)
            ->from('ctp_group_homework as gh')
            ->from('ctp_assignment_topic as at')
			->where('gh.group_id=?', $id)
            ->where('gh.assignment_master_id=at.assignment_master_id')
            ->where('DATE(at.target_submission_date) >= CURDATE()')
            
		);
        
        return count($row)? true : false;
    }
}