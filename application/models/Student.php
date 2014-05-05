<?php

class Api_Model_Student extends Zend_Db_Table_Abstract
{
	protected $_name = 'student';
	
	public function get($user_id, $user_type)
	{
		$row = $this->fetchAll(
			$this->select()
			->where('user_id=?', $user_id)
			->where('user_type=?', $user_type)
			->limit(1, 0)
		);
		
		return (count($row) )? 1: 0;
	}
	
	public function getInfo($user_id, $str)
	{
		$row = $this->fetchRow(
			$this->select()
			->where('user_id=?', $user_id)
			->limit(1, 0)
		);
		
		return (count($row) ) ? $row->$str: '';
	}
	
	public function getById($id)
	{
		return $this->fetchAll(
			$this->select()
			->where('id=?', $id)
		);
	}
	public function getInfoById($id, $str)
	{
		$sql = "
		SELECT $str FROM $this->_name WHERE id=$id LIMIT 1
		";
		
		$row = Zend_Db_Table::getDefaultAdapter()->query($sql)->fetch();
		return sizeof($row) ? $row[$str] : '';
	}
	
	public function isStudentValid($user_id)
	{
		/*$row = $this->fetchRow(
			$this->select()->setIntegrityCheck(false)
			->from('student as st')
			->from('user_login as ul')
			->where('st.user_id=ul.user_id')
			->where('ul.user_id=?', $user_id)
			->where('st.user_id=?', $user_id)
			->where('ul.status=?', 'Activate')
		);
		*/
		$sql = "
		SELECT st.id FROM $this->_name as st, user_login as ul WHERE
		st.user_id=ul.user_id
		AND
		ul.user_id=$user_id
		AND
		st.user_id=$user_id
		AND
		ul.status='Activate'
		LIMIT 1
		";
		
		$row = Zend_Db_Table::getDefaultAdapter()->query($sql)->fetch();
		
		return ( sizeof( $row) )? true: false;
	}
	public function getStudentList($school_id, $class_id, $section_id)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		return $db->query("SELECT st.id as sid, st.fname, ul.user_id, st.lname, st.user_type, `us`.`assigned_sections` AS `section_id` , 
						`us`.`mainclass` AS `class` , 			us.year, ys.status
						FROM student st, user_login ul, user_sections us, year_section ys, master_section ms, school_cce_session scs
						WHERE 1
						AND st.user_id = ul.user_id
						AND us.user_id = ul.user_id
						AND ul.status = 'Activate'
						AND us.status = 'Active'
						AND ms.id = ys.section_id
						AND ys.section_id =$section_id
						AND us.mainclass =$class_id
						AND us.year = ys.session_id
						AND us.assigned_sections = ys.section_id
						AND ys.school_id =$school_id
						AND us.year = scs.session_id
						AND scs.status = 'Active'
                        group by st.id
						")->fetchAll();
	}
	
	public function getStudentListAll($school_id, $class_id, $section_id)
	{
		$class_id = implode(',', $class_id);
		$section_id = implode(',', $section_id);
		
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$sql = "SELECT st.id as sid, st.fname, ul.user_id, st.lname, st.user_type, `us`.`assigned_sections` AS `section_id` , 
						`us`.`mainclass` AS `class` , us.roll_no, ys.status
						FROM student st, user_login ul, user_sections us, year_section ys, master_section ms, school_cce_session scs
						WHERE 1
						AND st.user_id = ul.user_id
						AND us.user_id = ul.user_id
						AND ul.status = 'Activate'
						AND us.status = 'Active'
						AND ms.id = ys.section_id
						AND us.year = ys.session_id
						AND us.assigned_sections = ys.section_id
						AND ys.school_id =$school_id
						AND us.year = scs.session_id
						AND scs.status = 'Active'
						AND 
						(
						  ys.section_id IN( $section_id )
							AND 
						  us.mainclass IN( $class_id )
						)
                        group by st.id
						";
		return $db->query($sql)->fetchAll();
	}
	
	public function getStudentListIds($school_id, $class_id, $section_id)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		return $db->query("SELECT st.id as student_id
						FROM student st, user_login ul, user_sections us, year_section ys, master_section ms, school_cce_session scs
						WHERE 1
						AND st.user_id = ul.user_id
						AND us.user_id = ul.user_id
						AND ul.status = 'Activate'
						AND us.status = 'Active'
						AND ms.id = ys.section_id
						AND ys.section_id =$section_id
						AND us.mainclass =$class_id
						AND us.year = ys.session_id
						AND us.assigned_sections = ys.section_id
						AND ys.school_id =$school_id
						AND us.year = scs.session_id
						AND scs.status = 'Active'
                        group by st.id
						")->fetchAll();
	}
	
	public function getFnameLname( $ids )
	{
		$ids = implode(',', $ids);
		$sql = "
		SELECT st.id , st.fname , st.lname , us.roll_no FROM $this->_name as st, user_sections as us WHERE
		us.user_type='Student'
		AND
		us.status='Active'
		AND
		us.user_id=st.user_id
		AND
		st.id IN( $ids)
		";
		//print $sql;
		return Zend_Db_Table::getDefaultAdapter()->query($sql)->fetchAll();
	}
	
	public function getFullname( $id )
	{
		$sql = "
		SELECT fname,lname FROM $this->_name WHERE
		id=$id
		LIMIT 1
		";
		
		$row = Zend_Db_Table::getDefaultAdapter()->query($sql)->fetch();
		return $row['fname'].' '.$row['lname'];
	}
	/*public function getStudentList($school_id, $class_id, $section_id)
	{
		//$row = $this->fetchAll(
			$row= $this->select()->setIntegrityCheck(false)
			->from('student as st', new Zend_Db_Expr('DISTINCT st.id as sid, st.fname, st.lname, st.user_type') )
			->from('user_login as ul', array('user_id') )
			->from('user_sections as us', array('assigned_sections', 'mainclass', 'year') )
			->from('year_section as ys', array('status'))
			->from('master_section as ms')
			->from('school_cce_session as scs')
			->where('st.user_id=ul.user_id')
			->where('us.user_id=ul.user_id')
			->where('ul.status=?', 'Activate')
			->where('us.status=?', 'Active')
			->where('ms.group_id=?', $section_id)
			->where('us.mainclass=?', $class_id)
			->where('us.year=ys.session_id')
			->where('us.assigned_sections=ys.section_id')
			->where('ys.school_id=?', $school_id)
			->where('us.year=scs.session_id')
			->where('scs.status=?', 'Active');
		//)->toArray();
		
		//return ( sizeof( $row) ) ? $row : array();
		
		echo $row->__toString();
	}*/
	
}
