<?php

class Api_Model_DiaryShare extends Zend_Db_Table_Abstract
{
    protected $_name='ctp_diary_share';
    
    public function saveForStudent($mid, $sid)
    {
        $this->insert( array(
            'master_diary_id' => $mid,
            'student_id'      => $sid
        ));
    }
    
	public function saveForStudentBatch($mid, $sid)
    {
    	foreach ( $sid as $s)
    	{
    		$values[] = "($mid, $s)";
    	}
    	$value = implode(',', $values);
        $sql = "
        INSERT INTO $this->_name (master_diary_id,student_id)
        VALUES
        $value
        ";
        //print $sql;
        Zend_Db_Table::getDefaultAdapter()->query($sql);
    }
    
    public function saveForTeacher($mid, $tid)
    {
        $this->insert( array(
            'master_diary_id' => $mid,
            'teacher_id'      => $tid
        ));
    }
    
    public function saveForGroup($mid, $gid)
    {
        $this->insert( array(
            'master_diary_id' => $mid,
            'group_id'      => $gid
        ));
    }
    
	public function saveForGroupBatch($mid, $gid)
    {
    	foreach ( $gid as  $g )
    	{
    		$values[] = "($mid, $g)";
    	}
    	$value = implode(',' , $values);
    	$sql = "INSERT INTO $this->_name (master_diary_id,group_id) 
    	VALUES 
    	$value
    	";
    	Zend_Db_Table::getDefaultAdapter()->query($sql);
    }
    
    public function saveForClassSection($mid, $cid, $sid)
    {
        $this->insert( array(
            'master_diary_id' => $mid,
            'class_id'        => $cid,
            'section_id'      => $sid
        ));
    }
    
    public function saveForClassSectionBatch( $mid, $classSection)
    {
    	foreach ( $classSection as $cs )
    	{
    		$values[] = "($mid, $cs[class_id], $cs[section_id])";
    	}
    	
    	$value = implode(',', $values);
    	$sql = "
    	INSERT INTO $this->_name (master_diary_id,class_id,section_id)
    	VALUES
    	$value
    	";
    	Zend_Db_Table::getDefaultAdapter()->query($sql);
    }
    
    public function getMasterIdsByTeacher($id)
    {
      /*   return $this->fetchAll(
             $this->select()->setIntegrityCheck(false)
            ->from('ctp_diary_share as ds', array('master_diary_id'))
            ->from('staff as s', array('user_id'))
            ->where('ds.teacher_id=s.id')
            ->where('ds.teacher_id!=?', 0)
            ->where('s.user_id=?', $uid)
       );
       */
       $sql = "
       SELECT master_diary_id FROM $this->_name WHERE
       teacher_id=$id
       AND
       teacher_id != ''
       ";
       
       return Zend_Db_Table::getDefaultAdapter()->query($sql)->fetchAll(false, 'master_diary_id');
    }
    
    public function getStudent($mid, $sid)
    {
         return $this->fetchAll(
            $this->select()
            ->where('master_diary_id=?', $mid)
            ->where('student_id=?', $sid)
        );
    }
    
    public function getClassSection($cid, $sid, $user_id)
    {
        return $this->fetchAll(
           $this->select()->setIntegrityCheck(false)
           ->from('ctp_diary_share as ds', array('master_diary_id'))
            ->from('ctp_master_diary as m')
            ->where('ds.class_id=?', $cid)
            ->where('ds.section_id=?', $sid)
            ->where('ds.class_id!=?', 0)
            ->where('ds.section_id!=?', 0)
            ->where('m.user_id!=?', $user_id)
            ->where('ds.master_diary_id=m.id')
            ->group('ds.master_diary_id')
       );
    }
    
    public function getGroup($gid)
    {
         return $this->fetchAll(
            $this->select()
            ->where('group_id=?', $gid)
        );
    }
    
    public function getByMid($mid)
    {
        return $this->fetchAll(
            $this->select()
            ->where('master_diary_id=?', $mid)
        );
    }
    
    public function getPostedByClass($mid)
    {
        return $this->fetchAll(
            $this->select()
            ->where('master_diary_id=?', $mid)
            ->where('class_id!=?', 0)
            ->where('section_id!=?', 0)
        );
    }
    
    public function getPostedByGroup($mid)
    {
        return $this->fetchAll(
            $this->select()
            ->where('master_diary_id=?', $mid)
            ->where('group_id!=?', 0)
        );
    }
    
    public function getPostedByTeacher($mid)
    {
        return $this->fetchAll(
            $this->select()
            ->where('master_diary_id=?', $mid)
            ->where('teacher_id!=?', 0)
        );
    }
    
    public function getPostedByStudent($mid)
    {
        return $this->fetchAll(
            $this->select()
            ->where('master_diary_id=?', $mid)
            ->where('student_id!=?', 0)
        );
    }
}







