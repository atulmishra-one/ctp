<?php

class Api_Model_GroupMember extends Zend_Db_Table_Abstract
{
	protected $_name = 'ctp_group_member';
	
	public function getCountByGroupIdAndExitDate($id)
	{
		$row = $this->fetchAll(
			$this->select()
			->where('ctp_group_id=?', $id)
			->where('date_exit <= ?', 0)
			
		)->toArray();
		
		return count( $row);
	}
	
	public function getCountByGroupIdAndExitDateArray($id)
	{
		$id = implode(',', $id);
		$sql = "
		SELECT ctp_group_id FROM $this->_name WHERE
		ctp_group_id IN( $id)
		AND
		date_exit <= 0
		";
		
		$row = Zend_Db_Table::getDefaultAdapter()->query($sql)->fetchAll();
		return sizeof($row);
	}
	
	public function getCountByGroupId($id)
	{
		$row = $this->fetchAll(
		  $this->select()
			->where('ctp_group_id=?', $id)
            ->where('date_exit <= ?', 0)
						
		)->toArray();
		
		return count( $row);
	}
	
	public function save(array $data)
	{
		$this->insert( array(
			'member_id' 	=> $data['member_id'],
			'ctp_group_id'	=> $data['id'],
			'date_joined'	=> new Zend_Db_Expr('NOW()')
		));
	}
	
	public function saveBatch(array $data)
	{
		foreach ( $data['member_id'] as $mid )
		{
			$values[] = "($mid, $data[id], NOW() )";
		}
		
		$value = implode(',', $values);
		$sql = "
		INSERT INTO $this->_name (member_id,ctp_group_id,date_joined)
		VALUES
		$value
		";
		
		Zend_Db_Table::getDefaultAdapter()->query($sql)->fetch();
	}
    
    public function isInGroupMid($mid, $gid)
    {
        $row = $this->fetchRow(
            $this->select()
            ->where('member_id=?', $mid)
            ->where('ctp_group_id=?', $gid)
            ->where('date_exit <= ?', 0)
        );
        
        return count($row)? 1 : 0;
    }
	
	public function getNonMembers($id, $owner_type, $owner_id, $school_id, $class_id, $section_id, $subject_id)
	{
		$row = $this->fetchRow(
			$this->select()->setIntegrityCheck(false)
			->from('ctp_group as g')
			->from('ctp_group_member as gm')
			->where('g.ctp_group_owner_type=?', $owner_type)
			->where('g.ctp_group_owner_id=?', $owner_id)
			->where('g.school_id=?', $school_id)
			->where('g.class_id=?', $class_id)
			->where('g.section_id=?', $section_id)
			->where('g.subject_id=?', $subject_id)
			->where('g.ctp_group_id=gm.ctp_group_id')
			->where('gm.member_id =?', $id)
			->where('date_exit <= ?', 0)
		);
		
		return sizeof( $row) ? true : false;
	}
	
	
	public function getMembersByGroupId($id)
	{
		$sql = "
		SELECT member_id FROM $this->_name WHERE ctp_group_id=$id AND date_exit <= 0
		";
		
		return Zend_Db_Table::getDefaultAdapter()->query($sql)->fetchAll(false, 'member_id');
	}
	
	public function getMembersAndDateJoinedByGroupId($id)
	{
		$sql = "
		SELECT member_id, date_joined FROM $this->_name WHERE ctp_group_id=$id AND date_exit <= 0
		";
		
		return Zend_Db_Table::getDefaultAdapter()->query($sql)->fetchAll();
	}
	
	public function getMembersByGroupIdArray( $ids )
	{
		$ids = implode(',', $ids);
		
		$sql = "
		SELECT member_id as student_id FROM $this->_name WHERE date_exit <= 0 AND ctp_group_id IN( $ids )
		";
		
		return Zend_Db_Table::getDefaultAdapter()->query($sql)->fetchAll();
	}
	
	public function getMembersByMemberId($id, $uid, $utype ='TEACHER')
	{
		$row = $this->fetchAll(
			$this->select()->setIntegrityCheck(false)
            ->from('ctp_group_member as gm')
            ->from('ctp_group as g', array('ctp_group_name'))
			->where('gm.member_id=?', $id)
            ->where('gm.ctp_group_id=g.ctp_group_id')
            ->where('g.ctp_group_owner_id=?', $uid)
            ->where('g.ctp_group_owner_type=?', $utype)
		)->toArray();
		
		return sizeof( $row) ? $row : array();
	}
    
    public function getActiveGroupByMember($id)
	{
		$row = $this->fetchAll(
			$this->select()
			->where('member_id=?', $id)
            ->where('date_exit <= ?', 0)
		)->toArray();
		
		return sizeof( $row) ? $row : array();
	}
	
	public function getActiveGroupByMemberById($id)
	{
		$sql = "
		SELECT ctp_group_id FROM $this->_name WHERE
		member_id=$id
		AND
		date_exit <= 0
		";
		
		return Zend_Db_Table::getDefaultAdapter()->query($sql)->fetchAll(false, 'ctp_group_id');
	}
    
    public function isInGroup($uid, $mid, $class_id, $section_id)
    {
        
            $sql = $this->select()->setIntegrityCheck(false)
            ->from('ctp_group_member as gm')
            ->from('ctp_group as g')
            ->where('g.ctp_group_owner_id=?', $uid)
            ->where('g.ctp_group_owner_type=?', 'TEACHER')
            ->where('gm.member_id=?', $mid)
            ->where('g.ctp_group_id=gm.ctp_group_id')
            ->where('g.class_id=?', $class_id)
            ->where('g.section_id=?', $section_id)
            ->where('gm.date_exit <=?', 0);
            
           // print $sql;
        $row = $this->fetchAll($sql);
        
        return sizeof($row)? 1: 0;
    }
    
    
    public function getDateJoined($group_id, $member_id)
	{
		$sql = "
		SELECT date_joined FROM $this->_name WHERE 
		member_id=$member_id AND ctp_group_id=$group_id
		";
		return Zend_Db_Table::getDefaultAdapter()->query($sql)->fetch();
	}
	
	
	public function removeMember($group_id, $member_id)
	{
		return $this->update( array(
		'date_exit' => new Zend_Db_Expr('NOW()')
		), array(
			'member_id=?' =>  $member_id,
			'ctp_group_id=?' => $group_id
		)
		);
	}
	
	public function removeForEver( $id)
	{
		$this->delete( array(
			'ctp_group_id=?' => $id
		));
	}
	
}