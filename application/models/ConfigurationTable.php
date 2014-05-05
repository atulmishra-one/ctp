<?php

class Api_Model_ConfigurationTable extends Zend_Db_Table_Abstract
{
	protected $_name = 'ctp_configuration';
    
    public function getIpAddress(){
        $row = $this->fetchRow(
            $this->select()
            ->where('tag=?', 'SOCKET_IP')
        );
        
        return count($row)? $row->value : '';
    }
    
    public function getHighPort(){
        
        $row = $this->fetchRow(
            $this->select()
            ->where('tag=?', 'HIGH_PRIORITY_PORT')
        );
        
        return count($row)? $row->value : '';
        
    }
    
    public function getLowPort(){
        
        $row = $this->fetchRow(
            $this->select()
            ->where('tag=?', 'LOW_PRIORITY_PORT')
        );
        
        return count($row)? $row->value : '';
        
    }
}