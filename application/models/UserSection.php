<?php

class Api_Model_UserSection extends Zend_Db_Table_Abstract
{
	protected $_name = 'user_sections';
	
	public function get($master_section_id, $user_type = 'Student', $school_id)
	{
		
			$sql = $this->select()->setIntegrityCheck(false)
			->from('user_sections as us')
			->from('user_login as ul')
            ->from('year_section as ys')
            ->from('school_cce_session as session')
			->where('us.assigned_sections=?', $master_section_id)
			->where('us.user_type=?', $user_type)
			->where('us.status=?', 'Active')
			->where('us.year=session.session_id')
            ->where('session.school_auto_id=?', $school_id)
            ->where('session.status=?', 'Active')
            ->where('us.year=ys.session_id')
			->where('ul.user_id=us.user_id')
			->where('ul.status=?', 'Activate')
            ->where('us.assigned_sections = ys.section_id')
            ->where('ys.school_id=?', $school_id);
            print $sql;
		return $this->fetchAll($sql);
		
	}
	
	public function getAllTeachersByClassSection($school_id, $class_id, $section_id)
	{
		$sql = "
		SELECT ul.user_id, st.initial_name, st.id, st.fname, st.lname FROM user_sections us, user_login ul , year_section ys, school_cce_session scs , 
		staff st
		WHERE
		us.assigned_sections=$section_id 
		AND
		us.mainclass=$class_id
		AND 
		us.user_type='Teacher' 
		AND
		st.user_id=ul.user_id
		AND 
		us.status='Active' 
		AND
		us.year=scs.session_id 
		AND 
		scs.school_auto_id=$school_id
		AND
		scs.status='Active'
		AND
		us.year=ys.session_id
		AND
		ul.user_id=us.user_id
		AND
		ul.status='Activate'
		AND
		us.assigned_sections = ys.section_id
		AND
		ys.school_id=$school_id
		";

		return Zend_Db_Table::getDefaultAdapter()->query($sql)->fetchAll();
	}
	
	public function getByUserId($user_id, $school_id)
	{
		
		$db = Zend_Db_Table::getDefaultAdapter();
		$row= $db->query("SELECT `us`.`assigned_sections` AS `section_id`, us.mainclass as class_id
					FROM staff st, user_login ul, user_sections us, year_section ys, school_cce_session as session 
					WHERE st.user_id = ul.user_id
					AND us.user_id = ul.user_id
					AND us.user_id = $user_id
					AND us.year = session.session_id
                    AND session.school_auto_id=$school_id
                    AND session.status='Active'
					AND ul.status = 'Activate'	
					AND us.year = ys.session_id
					AND us.assigned_sections = ys.section_id
					AND ys.school_id =$school_id
					AND ys.status = 'Active'
                    AND us.status='Active'
                    group by `us`.`assigned_sections`
				");
		
		return ( sizeof( $row) )? $row->fetchAll(): array();
	}
	
	
	public function getStudentAssignedSectionByUserID($user_id, $school_id)
	{
		
		$db = Zend_Db_Table::getDefaultAdapter();
		$row= $db->query("SELECT `us`.`assigned_sections` AS `section_id`, us.mainclass as class_id
					FROM student st, user_login ul, user_sections us, year_section ys, school_cce_session as session 
					WHERE st.user_id = ul.user_id
					AND us.user_id = ul.user_id
					AND us.user_id = $user_id
					AND us.year = session.session_id
                    AND session.school_auto_id=$school_id
                    AND session.status='Active'
					AND ul.status = 'Activate'	
					AND us.year = ys.session_id
					AND us.assigned_sections = ys.section_id
					AND ys.school_id = $school_id
					AND ys.status = 'Active'
					LIMIT 0, 1
				");
	
		$s = $row->fetch();
	
	
		return ( sizeof( $row) )? $s['section_id']: array();
	}
	
	public function getStudentAssignedClassSection($user_id, $school_id)
	{
		$sql = "SELECT `us`.`assigned_sections` AS `section_id`, us.mainclass as class_id
					FROM student st, user_login ul, user_sections us, year_section ys, school_cce_session as session 
					WHERE st.user_id = ul.user_id
					AND us.user_id = ul.user_id
					AND us.user_id = $user_id
					AND us.year = session.session_id
                    AND session.school_auto_id=$school_id
                    AND session.status='Active'
					AND ul.status = 'Activate'	
					AND us.year = ys.session_id
					AND us.assigned_sections = ys.section_id
					AND ys.school_id = $school_id
					AND ys.status = 'Active'
					LIMIT 0, 1
				";
		return Zend_Db_Table::getDefaultAdapter()->query($sql)->fetch();
	}
	
	public function getRollNo($user_id)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$row= $db->query("SELECT roll_no FROM `user_sections` WHERE `user_type` = 'Student' and `status` = 'Active' and `user_id`= $user_id
		LIMIT 1");
		
		$s = $row->fetch();
	
	
		return ( sizeof( $row) )? $s['roll_no']: '';
	}
	
	
	
}