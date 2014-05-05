<?php


class Api_Model_SchoolSession extends Zend_Db_Table_Abstract
{
	protected $_name = 'school_cce_session';
    
    public function get($school_id)
    {
        return $this->fetchRow(
            $this->select()
            ->where('school_auto_id=?', $school_id)
            ->where('status=?', 'Active')
            ->order('session_id desc')
            ->limit(1, 0)
        );
    }
	
}