<?php

class Api_Model_GroupNotes extends Zend_Db_Table_Abstract
{
    protected $_name = 'ctp_group_notes';
    
    public function hasNotes($id)
    {
        $row = $this->fetchRow(
			$this->select()
			->where('group_id=?', $id)
		);
        
        return count($row)? true : false;
    }
    
    public function save($id, $group_id)
    {
        $this->insert( array(
            'notes_id' => $id,
            'group_id' => $group_id
        ));
    }
    
    public function getGroupById($id)
    {
        return $this->fetchAll(
            $this->select()
            ->where('notes_id=?', $id)
        );
    }
}