<?php

class Api_Model_BtnSync extends Zend_Db_Table_Abstract
{
	protected $_name = 'ctp_btnsync';
	
	public function getStatus($school_id, $class_id, $section_id, $btnname)
	{
		/*$row = $this->fetchRow(
			$this->select()
			->where('school_id=?', $school_id)
			->where('class_id=?', $class_id)
			->where('section_id=?', $section_id)
			->where('button_name=?', $btnname)
			->limit(1, 0)
		);
		
		return ( count( $row) ) ? $row->status: 0;
		*/
		$sql = "SELECT status FROM $this->_name WHERE
		school_id=$school_id
		AND
		class_id=$class_id
		AND
		section_id=$section_id
		AND
		button_name= '$btnname'
		LIMIT 1
		";
		
		$row = Zend_Db_Table::getDefaultAdapter()->query($sql)->fetch();
		return sizeof($row) ? $row['status'] : '';
	}
	
	public function save( array $data)
	{
		if( $this->getStatus($data['school_id'], $data['class_id'], $data['section_id'], $data['button_name']) )
		{
			$this->update( array(
				'status'		=> $data['status']
			),
			 array(
				'school_id=?' 	=> $data['school_id'],
				'class_id=?'	=> $data['class_id'],
				'section_id=?'	=> $data['section_id'],
				'button_name=?'	=> $data['button_name']
			) );
			
			return 1;
		}
		else
		{
			return $this->insert( array(
				'school_id' 	=> $data['school_id'],
				'class_id'		=> $data['class_id'],
				'section_id'	=> $data['section_id'],
				'button_name'	=> $data['button_name'],
				'status'		=> $data['status'],
				'date_created'	=> new Zend_Db_Expr('NOW()')
			));	
		}
		
	}
	
	public function remove($school_id, $class_id, $section_id)
	{
		$this->delete( array(
			'school_id=?' 	=> $school_id,
			'class_id=?'	=> $class_id,
			'section_id=?'	=> $section_id,
		));
	}
	
	
	
	
	
	
	
	
}