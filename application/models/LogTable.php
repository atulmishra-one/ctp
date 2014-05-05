<?php

class Api_Model_LogTable extends Zend_Db_Table_Abstract
{
	protected $_name = 'ctp_log';

	public function save(array $data)
	{	
		 $this->insert( array(
			'log_message'          => $data['msg'],
			'log_status'	   	   => $data['status'],
			'log_date_occured'	   => new Zend_Db_Expr('TIMESTAMPADD(MINUTE,1,NOW())')
		) );
	}
	
	public function getAll($keyword = false)
	{
		if( $keyword and !is_null( $keyword) )
		{
			$adapter = new Zend_Paginator_Adapter_DbSelect(
				$this->select()
				->from('ctp_log')
				->order('log_date_occured DESC')
				->where('DATE(log_date_occured)=?', $keyword)
				);
		}
		else{
			$adapter = new Zend_Paginator_Adapter_DbSelect(
				$this->select()
				->from('ctp_log')
				->order('log_date_occured DESC')
				);
		}
		
		return $adapter;
 
	}
	
	public function get($id)
	{
		
		$row = $this->fetchAll(
				$this->select()
				->where('log_id=?', $id)
				->limit(1, 0)
		);
		
		return $row;
 
	}
	
	public function remove($ids)
	{
		 return $this->delete( array( 'log_id IN(?)' => $ids ));
	}
	
}