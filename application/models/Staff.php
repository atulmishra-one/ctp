<?php

class Api_Model_Staff extends Zend_Db_Table_Abstract
{
	protected $_name = 'staff';


	public function isTeacherValid($user_id)
	{
		$row = $this->fetchRow(
		$this->select()->setIntegrityCheck(false)
		->from('user_login as ul')
		->from('user_sections as us')
		->from('user_type as ut')
		->where('ul.user_id=?', $user_id)
		->where('ut.id=ul.user_type_id')
		->where('ut.type=?', 'Teacher')
		->where('us.user_id=?', $user_id)
		->where('us.status=?', 'Active')
		->where('ul.status=?', 'Activate')
		);


		return ( sizeof( $row) )? true: false;
	}

	public function get($user_id, $school_id)
	{
		$row = $this->fetchRow(
		$this->select()
		->where('user_id=?', $user_id)
		->where('user_type=?', 'Teacher')
		->where('status=?', 'Active')
		->where('school_auto_id=?', $school_id)
		->limit(1, 0)
		);

		return ( count( $row) )? $row : 0;
	}

	public function getById($id, $school_id)
	{
		$row = $this->fetchRow(
		$this->select()
			
		->where('id=?', $id)
		->where('user_type=?', 'Teacher')
		->where('status=?', 'Active')
		->where('school_auto_id=?', $school_id)
		->limit(1, 0)
		);

		return ( count( $row) )? $row->user_id: false;
	}
	public function getBySchoolId($school_id)
	{
		return $this->fetchAll(
		$this->select()
		->where('school_auto_id=?', $school_id)
		->where('status=?', 'Active')
		);
	}
	public function getInfoById($id, $str)
	{
		$row = $this->fetchRow(
		$this->select()
		->where('id=?', $id)
		->where('user_type=?', 'Teacher')
		->where('status=?', 'Active')
		->limit(1, 0)
		);

		return ( count( $row) )? $row->$str : '';
	}

	public function getFullname( $id, $by = false )
	{
		$fullname = '';

		if ( $by)
		{
			$cond = " user_id=$id";
		}
		else {
			$cond = " id=$id";
		}

		$sql = "SELECT initial_name, fname, lname FROM staff WHERE $cond and user_type='Teacher' and status='Active' LIMIT 1";
		$row = Zend_Db_Table::getDefaultAdapter()->query($sql)->fetch();
		if ( sizeof( $row) )
		{
			$fullname = $row['initial_name'].' '.$row['fname'].' '.$row['lname'];
		}

		return $fullname;
	}
	
	public function getId( $user_id )
	{
		$id = 0;
		
		$sql = "SELECT id FROM staff WHERE user_id=$user_id and user_type='Teacher' and status='Active' LIMIT 1";
		$row = Zend_Db_Table::getDefaultAdapter()->query($sql)->fetch();
		if ( sizeof( $row) )
		{
			$id = $row['id'];
		}

		return $id;
	}


	public function getClassAndSection($school_id, $user_id)
	{
		return Zend_Db_Table::getDefaultAdapter()->query(
            "
            select 
                ms.class_id, ms.group_id as section_id
            from
            master_section as ms,
            user_sections as us,
            year_section as ys
                where
            us.assigned_sections = ms.id
            and us.user_id = $user_id
            and us.year = ys.session_id
            and ys.school_id = $school_id
            and us.user_type = 'Teacher'
            and us.status = 'Active'
            and ms.status = 'Active'
            group by us.assigned_sections
            "
		)->fetchAll();
	}
}







