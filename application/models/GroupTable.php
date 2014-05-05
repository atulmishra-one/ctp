<?php

class Api_Model_GroupTable extends Zend_Db_Table_Abstract
{
	protected $_name = 'ctp_group';
	
	public function isGroupExitsForMember($school_id, $class_id, $section_id, $owner_id,$subject_id,$group_name)
	{
		/*$row = $this->fetchAll(
			$this->select()
			->where('school_id=?', $school_id)
			->where('class_id=?', $class_id)
			->where('section_id=?', $section_id)
			->where('ctp_group_owner_id=?', $owner_id)
			->where('subject_id=?', $subject_id)
			->where('ctp_group_name=?', $group_name)
			
		)->toArray();
		
		return count( $row)? true : false;
		*/
		$sql = "
		SELECT ctp_group_id FROM $this->_name WHERE
		school_id=$school_id
		AND
		class_id=$class_id
		AND
		section_id=$section_id
		AND
		ctp_group_owner_id=$owner_id
		AND
		subject_id=$subject_id
		AND
		ctp_group_name='$group_name'
		";
		
		$row = Zend_Db_Table::getDefaultAdapter()->query($sql)->fetch();
		return $row['ctp_group_id'];
	}
	
	public function save(array $data)
	{
		return $this->insert( array(
			'ctp_group_name' 	     => $data['group_name'],
			'ctp_group_owner_id'     => $data['group_owner_id'],
			'ctp_group_owner_type'   => $data['group_owner_type'],
			'ctp_group_date_created' => new Zend_Db_Expr('NOW()'),
			'ctp_group_status'		 => $data['group_status'],
			'class_id'				 => $data['class_id'],
			'section_id'			 => $data['section_id'],
			'subject_id'			 => $data['subject_id'],
			'school_id'				 => $data['school_id']
		));
	}
	
	public function updateGroupName($name, $id)
	{
		$this->update( array(
			'ctp_group_name'=> $name,
			'ctp_group_status' => 'ACTIVE',
            'ctp_group_date_created' => new Zend_Db_Expr('NOW()')
		),array('ctp_group_id=?' => $id) );
	}
	
	public function updateGroupStatus($status,$id)
	{
		return $this->update( array('ctp_group_status' => $status), array( 'ctp_group_id=?' => $id ));
	}
	
	public function getResult( $data )
	{
		$cond = '';
		
		if ( isset( $data['owner_id']) )
		{
			$cond .= " and ctp_group_owner_id= $data[owner_id]";
		}
		
		if ( isset( $data['ctp_group_owner_type']) )
		{
			$cond .= " and ctp_group_owner_type= $data[ctp_group_owner_type]";
		}
		
		if ( isset( $data['class_id']) )
		{
			$cond .= " and class_id=$data[class_id]";
		}
		
		if ( isset( $data['section_id']) )
		{
			$cond .= " and section_id=$data[section_id]";
		}
		
		if ( isset( $data['subject_id']) )
		{
			$cond .= " and subject_id=$data[subject_id]";
		}
		
		
		$sql = "
		SELECT * FROM $this->_name WHERE
		school_id=$data[school_id]
		$cond
		AND
		ctp_group_status='ACTIVE'
		ORDER BY ctp_group_status
		";
		
		return Zend_Db_Table::getDefaultAdapter()->query($sql)->fetchAll();
	}
	public function getBySchoolOwnerIdType($school_id, $owner_id, $owner_type)
	{
		$row = $this->fetchAll(
			$this->select()
			->where('school_id=?', $school_id)
			->where('ctp_group_owner_id=?', $owner_id)
			->where('ctp_group_owner_type=?', $owner_type)
			->order('ctp_group_status')
			
		)->toArray();
		
		return count( $row) ? $row : array();
	}
	
	public function getBySchoolOwnerIdTypeClass($school_id, $owner_id, $owner_type, $class_id)
	{
		$row = $this->fetchAll(
			$this->select()
			->where('school_id=?', $school_id)
			->where('ctp_group_owner_id=?', $owner_id)
			->where('ctp_group_owner_type=?', $owner_type)
			->where('class_id=?', $class_id)
			->order('ctp_group_status')
		)->toArray();
		
		return count( $row) ? $row : array();
	}
	
	public function getBySchoolOwnerIdTypeClassSubject($school_id, $owner_id, $owner_type, $class_id, $subject_id)
	{
		$row = $this->fetchAll(
			$this->select()
			->where('school_id=?', $school_id)
			->where('ctp_group_owner_id=?', $owner_id)
			->where('ctp_group_owner_type=?', $owner_type)
			->where('class_id=?', $class_id)
			->where('subject_id=?', $subject_id)
			->order('ctp_group_status')
		)->toArray();
		
		return count( $row) ? $row : array();
	}
	
	public function getBySchoolOwnerIdTypeClassSection($school_id, $owner_id, $owner_type, $class_id, $section_id)
	{
		$row = $this->fetchAll(
			$this->select()
			->where('school_id=?', $school_id)
			->where('ctp_group_owner_id=?', $owner_id)
			->where('ctp_group_owner_type=?', $owner_type)
			->where('class_id=?', $class_id)
			->where('section_id=?', $section_id)
            ->where('ctp_group_status=?', 'ACTIVE')
			->order('ctp_group_status')
		)->toArray();
		
		return count( $row) ? $row : array();
	}
	
	public function getBySchoolOwnerIdTypeClassSectionSubject($school_id, $owner_id, $owner_type, $class_id, $section_id, $subject_id)
	{
		$row = $this->fetchAll(
			$this->select()
			->where('school_id=?', $school_id)
			->where('ctp_group_owner_id=?', $owner_id)
			->where('ctp_group_owner_type=?', $owner_type)
			->where('class_id=?', $class_id)
			->where('section_id=?', $section_id)
			->where('subject_id=?', $subject_id)
			->order('ctp_group_status')
		)->toArray();
		
		return count( $row) ? $row : array();
	}
	
	public function getBySchoolOwnerIdTypeSubject($school_id, $owner_id, $owner_type, $subject_id)
	{
		$row = $this->fetchAll(
			$this->select()
			->where('school_id=?', $school_id)
			->where('ctp_group_owner_id=?', $owner_id)
			->where('ctp_group_owner_type=?', $owner_type)
			->where('subject_id=?', $subject_id)
			->order('ctp_group_status')
		)->toArray();
		
		return count( $row) ? $row : array();
	}
	
	public function getBySchoolOwnerIdTypeSection($school_id, $owner_id, $owner_type, $section_id)
	{
		$row = $this->fetchAll(
			$this->select()
			->where('school_id=?', $school_id)
			->where('ctp_group_owner_id=?', $owner_id)
			->where('ctp_group_owner_type=?', $owner_type)
			->where('section_id=?', $section_id)
			->order('ctp_group_status')
		)->toArray();
		
		return count( $row) ? $row : array();
	}
	
	public function getBySchoolOwnerIdTypeSectionSubject($school_id, $owner_id, $owner_type, $section_id, $subject_id)
	{
		$row = $this->fetchAll(
			$this->select()
			->where('school_id=?', $school_id)
			->where('ctp_group_owner_id=?', $owner_id)
			->where('ctp_group_owner_type=?', $owner_type)
			->where('section_id=?', $section_id)
			->where('subject_id=?', $subject_id)
			->order('ctp_group_status')
		)->toArray();
		
		return count( $row) ? $row : array();
	}
	
	public function getById($id)
	{
		$row = $this->fetchRow(
			$this->select()
			->where('ctp_group_id=?', $id)
		);
		
		return count( $row) ? $row : array();
	}
    
    public function getAllById($id)
    {
   	   $row = $this->fetchAll(
			$this->select()
			->where('ctp_group_id=?', $id)
		);
		
		return count( $row) ? $row : array();
    }
    
    public function getInfoById($id, $str)
	{
		$row = $this->fetchRow(
			$this->select()
			->where('ctp_group_id=?', $id)
            ->limit(1, 0)
		);
		
		return count( $row) ? $row->$str : '';
	}
	
	
	public function getAllGroupArray($ids)
	{
		$ids = implode(',' , $ids);
		$sql = "
		SELECT * FROM $this->_name WHERE 
		ctp_group_id IN( $ids)
		";
		return Zend_Db_Table::getDefaultAdapter()->query($sql)->fetchAll();
	}
	
	public function removeGroup($id)
	{
		$this->delete( array(
			'ctp_group_id=?' => $id
		));
	}
	
    public function setInactive($id)
	{
		$this->update(  array('ctp_group_status' => 'INACTIVE'), array(
			'ctp_group_id=?' => $id
		));
	}
    
    public function updateDateCreated($id){
        $this->update(  array('ctp_group_date_created' => new Zend_Db_Expr('NOW()')), array(
			'ctp_group_id=?' => $id
		));
    }
}