<?php

class Api_Model_UserLogin extends Zend_Db_Table_Abstract
{
	protected $_name = 'user_login';
	
	
	public function get($school_id, $user_id)
	{
		$row =  $this->fetchRow(
			$this->select()
			->where('user_id=?', $user_id)
			->where('school_id=?', $school_id)
			->where('status=?', 'Activate')
			->limit(1, 0)
		);
		
		return ( count( $row) )? $row: array(); 
	}
    
    public function getCurDate()
    {
        $row = $this->fetchRow(
            $this->select()
            ->from('user_login', array('CURDATE() as cur_date'))
        );
        
        return $row->cur_date;
    }
    
    public function getCurDateTime()
    {
        $row = $this->fetchRow(
            $this->select()
            ->from('user_login', array('NOW() as cur_datetime'))
        );
        
        return $row->cur_datetime;
    }
    
    
}