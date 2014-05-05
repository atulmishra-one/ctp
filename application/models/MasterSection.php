<?php

class Api_Model_MasterSection extends Zend_Db_Table_Abstract
{
	protected $_name = 'master_section';
	
	public function get($class_id, $group_id, $school_id)
	{
		$row = $this->fetchRow(
			$this->select()->setIntegrityCheck(false)
			->from('master_section as ms' , array('ms.id as mid') )
			->from('year_section as ys')
            ->from('school_cce_session as session')
			->where('ms.class_id=?', $class_id)
			->where('ms.group_id=?', $group_id)
			->where('ys.section_id=ms.id')
			->where('ys.session_id=session.session_id')
            ->where('session.school_auto_id=?', $school_id)
            ->where('session.status=?', 'Active')
			->where('ys.status=?', 'Active')
			->limit(1, 0)
		);
		
		return ( sizeof( $row) ) ? $row->mid : 0;
		
	}
	
	public function getById($master_section)
	{
		$row = $this->fetchAll(
			$this->select()->setIntegrityCheck(false)
			->from('master_section as ms')
			->from('year_section as ys')
			->where('ms.id=?', $master_section)
			->where('ys.section_id=ms.id')
			->where('ys.status=?', 'Active')
			->limit(1, 0)
		);
		
		return ( count($row) )? $row: array();
	}
	
	public function getInfoById($master_section, $str)
	{
		$row=  $this->fetchRow(
			$this->select()->setIntegrityCheck(false)
			->from('master_section as ms')
			->from('year_section as ys')
			->where('ms.id=?', $master_section)
			->where('ys.section_id=ms.id')
			->where('ys.status=?', 'Active')
			->limit(1, 0)
		);
		
		return ( count($row) )? $row->$str:0;
	}
	
	public function getGroupId($id)
	{
		$row = $this->fetchRow(
			$this->select()
			->where('id=?', $id)
			->where('status=?', 'Active')
			->limit(1, 0)
		);
		
		return ( count($row) )? $row->group_id: array();
	}
}