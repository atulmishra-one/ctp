<?php

class Api_Model_AssignSubject extends Zend_Db_Table_Abstract
{
	protected $_name = 'school_cce_assign_subject';
    
    protected $_primary = 'assign_auto_id';
    
    
	
	public function get($master_section_id, $user_id, $school_id)
	{
	   $sql =  $this->select()->setIntegrityCheck(false)
            ->from('school_cce_assign_subject as scas', array('scas.subject_id') )
            ->from('master_section as ms', array('ms.class_id', 'ms.group_id') )
            ->from('school_cce_session as session')
			->where('ms.id=?', $master_section_id)
			->where('ms.id=scas.group_id')
			->where('ms.class_id=scas.class_id')
			->where('scas.user_id=?', $user_id)
			->where('scas.status=?', 'Active')
			->where('scas.year=session.session_id')
            ->where('session.school_auto_id=?', $school_id)
            ->where('session.status=?', 'Active');
            
        $row = $this->fetchAll($sql);
        
        //print $sql;
		return ( count($row)) ? $row: array();
		
	}
	
	public function getAssignedSubjectArray( $master_section_id, $user_id, $school_id )
	{
		$master_section_id = implode(',', $master_section_id);
		
		$sql = "
		SELECT 
		scas.subject_id, 
		ms.class_id, 
		ms.id as section_id,
		salaah_class.class_name,
		school_cce_group.group_name,
		school_cce_subject.subject_name
		FROM 
		school_cce_assign_subject AS scas,
 		master_section AS ms,
 		school_cce_session AS session,
 		salaah_class,
 		school_cce_group,
 		school_cce_subject
 		WHERE 
 		ms.id IN( $master_section_id)
 		AND 
 		(ms.id=scas.group_id) 
 		AND 
 		(ms.class_id=scas.class_id) 
 		AND 
 		(scas.user_id=$user_id) 
 		AND 
 		(scas.status='Active') 
 		AND 
 		(scas.year=session.session_id) 
 		AND 
 		(session.school_auto_id=$school_id) 
 		AND 
 		(session.status='Active')
 		AND
 		(salaah_class.class_auto_id=ms.class_id)
 		AND
 		(school_cce_group.group_id=ms.group_id)
 		AND
 		(school_cce_subject.sub_auto_id=scas.subject_id)
		";
		//print $sql;
		
		return Zend_Db_Table::getDefaultAdapter()->query($sql)->fetchAll();
	}
	
	public function getByMasterSectionAndSubjectId($master_section_id, $subject_id, $school_id)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$q = $db->query("SELECT distinct user_id FROM school_cce_assign_subject as scas, master_section as ms, school_cce_session as session
							WHERE 
							scas.subject_id=$subject_id
							AND
							ms.group_id=scas.group_id
							AND
							ms.id = $master_section_id
							AND
							ms.class_id=scas.class_id
							AND 
							scas.year= session.session_id
							AND 
							scas.status='Active'
                            AND
                            session.school_auto_id=$school_id
                            AND
                            session.status='Active'
							");
		$row = $q->fetchAll();
		
		return ( count($row)) ? $row : array();
	}
}
