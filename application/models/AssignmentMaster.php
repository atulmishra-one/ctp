<?php

class Api_Model_AssignmentMaster extends Zend_Db_Table_Abstract
{
	protected $_name = 'assignment_master';

	public function getAssignmentList( $data )
	{
		$cond = '';

		if (isset( $data['subject_id'])  )
		{
			$cond .= " and am.subject_id = $data[subject_id]";
		}

		if( isset( $data['date']) && sizeof( $data['date'], 1) >= 1
		&&
		sizeof($data['date'], 1) < 3  )
		{
			$cond .= " and am.added_date BETWEEN DATE_SUB(NOW(), INTERVAL $data[date] DAY) AND NOW() ";
		}
		elseif ( isset($data['date']) && preg_match('#-#', $data['date']) )
		{
			$cond .= " and DATE(am.added_date)='$data[date]' ";
		}

		$sqlSync = "
			select am.id, am.upload_file, am.class_auto_id, am.section_id,
			am.added_by, am.added_date, am.status, am.content,
			am.assignment_title, am.staff_id, am.subject_id, am.school_auto_id, am.submission_date
			from
			assignment_master as am,
			ctp_assignment_sysn as async
			where
			am.id NOT in (select cgh.assignment_master_id from ctp_group_homework as cgh)
			and
			async.assignment_master_id=am.id
			and
			async.student_id = $data[member_id]
			and
			async.assignment_sysn_status!='ON'
			and
			async.assignment_sysn_status='OFF'
			and
			am.status!='Inactive'
			and
			am.status='Active'
			and
			am.school_auto_id= $data[school_id]
			and
			am.class_auto_id = $data[class_id]
			and
			am.section_id = $data[section_id]
			$cond
			group by am.id
            order by am.id asc
            limit 50
		";

			$sqlGroup = "
	 	   select am.id, am.upload_file, am.class_auto_id, am.section_id,
			am.added_by, am.added_date, am.status, am.content,
			am.assignment_title, am.staff_id, am.subject_id, am.school_auto_id, am.submission_date
			from
			assignment_master as am,
			ctp_assignment_sysn as async,
			ctp_group_homework as gh,
            ctp_group_member as gm,
            ctp_group as g
            where
            (
            	async.assignment_master_id=am.id
				and
				async.student_id = $data[member_id]
				and
				async.assignment_sysn_status!='ON'
				and
				async.assignment_sysn_status='OFF'
				and
				am.status!='Inactive'
				and
				am.status='Active'
				and gh.assignment_master_id = am.id
             	and gh.group_id = g.ctp_group_id
             	and g.ctp_group_id = gm.ctp_group_id
             	and gm.member_id = $data[member_id]
             	and am.school_auto_id = $data[school_id]
             	$cond
            )
            group by am.id
            order by am.id asc
            limit 50
	 ";
	 
	// print $sqlGroup;
             	$out1 = Zend_Db_Table::getDefaultAdapter()->query( $sqlSync )->fetchAll();
             	$out2 = Zend_Db_Table::getDefaultAdapter()->query( $sqlGroup )->fetchAll();
             	 
             	$res =  array_merge($out1, $out2);
             	array_multisort($res, SORT_ASC);
             	return $res;
             	 
	}

	public function getAssignmentListBySearch( $data )
	{
		$cond = '';

		if ( isset( $data['keyword']) )
		{
			$cond .= " and ( am.assignment_title like '%$data[keyword]%'
			or am.content like '%$data[keyword]%' ) ";
		}

		$sqlSync = "
			select am.id, am.upload_file, am.class_auto_id, am.section_id,
			am.added_by, am.added_date, am.status, am.content,
			am.assignment_title, am.staff_id, am.subject_id, am.school_auto_id, am.submission_date
			from
			assignment_master as am,
			where
			am.id NOT in (select cgh.assignment_master_id from ctp_group_homework as cgh)
			and
			am.status!='Inactive'
			and
			am.status='Active'
			and
			am.school_auto_id= $data[school_id]
			and
			am.class_auto_id = $data[class_id]
			and
			am.section_id = $data[section_id]
			$cond
			group by am.id
            order by am.id asc
            limit 50
		";

			$sqlGroup = "
	 	   select am.id, am.upload_file, am.class_auto_id, am.section_id,
			am.added_by, am.added_date, am.status, am.content,
			am.assignment_title, am.staff_id, am.subject_id, am.school_auto_id, am.submission_date
			from
			assignment_master as am,
			ctp_group_homework as gh,
            ctp_group_member as gm,
            ctp_group as g
            where
            (
				am.status!='Inactive'
				and
				am.status='Active'
				and gh.assignment_master_id = am.id
             	and gh.group_id = g.ctp_group_id
             	and g.ctp_group_id = gm.ctp_group_id
             	and gm.member_id = $data[member_id]
             	and am.school_auto_id = $data[school_id]
             	$cond
            )
            group by am.id
            order by am.id asc
            limit 50
	 ";

             	$out1 = Zend_Db_Table::getDefaultAdapter()->query( $sqlSync )->fetchAll();
             	$out2 = Zend_Db_Table::getDefaultAdapter()->query( $sqlGroup )->fetchAll();
             	 
             	$res =  array_merge($out1, $out2);
             	array_multisort($res, SORT_ASC);
             	return $res;
             	 
	}

	public function saveAssignment($data)
	{
		return $this->insert( array(
		        'upload_file'     => $data['attachments'],
                'class_auto_id'   => (int)$data['class_id'],
                'section_id'      => (int)$data['section_id'],
                'added_by'        => (int)$data['teacher_id'],
                'added_date'      => new Zend_Db_Expr('NOW()'),
                'status'          => (string)$data['status'],
                'content'         => (string)$data['content'],
                'assignment_title'=> (string)$data['title'],
                'staff_id'        => (int)$data['teacher_id'],
                'subject_id'      => (int)$data['subject_id'],
                'school_auto_id'  => (int)$data['school_id'],
				'submission_date' => (string)$data['target_date'],
				'mode'			  => (string)$data['mode'],
		) );

	}

	public function updateAssignment($data)
	{
		return $this->update( array(
		        'upload_file'     => $data['attachments'],
                'class_auto_id'   => (int)$data['class_id'],
                'section_id'      => (int)$data['section_id'],
                'added_by'        => (int)$data['teacher_id'],
                'modified_date'   => new Zend_Db_Expr('NOW()'),
                'status'          => (string)$data['status'],
                'content'         => (string)$data['content'],
                'assignment_title'=> (string)$data['title'],
                'staff_id'        => (int)$data['teacher_id'],
                'subject_id'      => (int)$data['subject_id'],
                'school_auto_id'  => (int)$data['school_id'],
				'submission_date' => (string)$data['target_date'],
				'mode'			  => (string)$data['mode'],
		) , array(
				'id=?'=> $data['assignment_id']
		) );

	}
	
	public function getSubmissionDate( $id )
	{
	  $row = $this->fetchRow(
		$this->select()
			->where('id=?', $id)
		);
		
	  return date('Y-m-d', strtotime( $row->submission_date));
	}

	public function getListAssignmentTeacher( $data )
	{
		$cond = '';

		if( isset( $data['date']) && sizeof( $data['date'], 1) >= 1
		&&
		sizeof($data['date'], 1) < 3  )
		{
			$cond .= " and am.added_date BETWEEN DATE_SUB(NOW(), INTERVAL $data[date] DAY) AND NOW() ";
		}
		elseif ( isset($data['date']) && preg_match('#-#', $data['date']) )
		{
			$cond .= " and DATE(am.added_date)='$data[date]' ";
		}

		if (isset( $data['subject_id']) )
		{
			$cond .= " and am.subject_id= $data[subject_id] ";
		}

		if (isset( $data['section_id']) )
		{
			$cond .= " and am.section_id= $data[section_id]";
		}

		if (isset( $data['class_id']) )
		{
			$cond .= " and am.class_auto_id= $data[class_id]";
		}

		$sql = "
		 select am.id, am.upload_file, am.class_auto_id, am.section_id,
			am.added_by, am.added_date, am.status, am.content,
			am.assignment_title, am.staff_id, am.subject_id, am.school_auto_id, am.submission_date
			from
			assignment_master as am,
			ctp_assignment_sysn as async
			where
			async.assignment_master_id=am.id
			and
			async.teacher_id = $data[teacher_id]
			and
			async.assignment_sysn_status!='ON'
			and
			async.assignment_sysn_status='OFF'
			and
			am.school_auto_id= $data[school_id]
			and
			am.staff_id = $data[teacher_id]
			$cond
			group by am.id
            order by am.id asc
            limit 50
		";
			return Zend_Db_Table::getDefaultAdapter()->query( $sql)->fetchAll();
			 
	}

	public function getListAssignmentTeacherBySearch( $data )
	{
		$cond = '';

		if ( isset( $data['keyword']) )
		{
			$cond .= " and ( am.assignment_title like '%$data[keyword]%'
			or am.content like '%$data[keyword]%' ) ";
		}

		$sql = "
		 select am.id, am.upload_file, am.class_auto_id, am.section_id,
			am.added_by, am.added_date, am.status, am.content,
			am.assignment_title, am.staff_id, am.subject_id, am.school_auto_id, am.submission_date
			from
			assignment_master as am
			where
			am.school_auto_id= $data[school_id]
			and
			am.staff_id = $data[teacher_id]
			$cond
			group by am.id
            order by am.id asc
            limit 50
		";
			return Zend_Db_Table::getDefaultAdapter()->query( $sql)->fetchAll();
	}
	 
	public function getByIdClassSection($assignment_master_id, $class_id, $section_id)
	{
		return $this->fetchAll(
		$this->select()
		->where('id=?', $assignment_master_id)
		->where('class_auto_id=?', $class_id)
		->where('section_id=?', $section_id)
		->limit(1, 0)
		);
	}

	public function getByIdInfo($assignment_master_id, $str)
	{
		$row = $this->fetchAll(
		$this->select()
		->where('id=?', $assignment_master_id)
		->where('status=?', 'Active')
		->limit(1, 0)
		);

		return ( sizeof($row) )? $row[0]->$str:false;
	}

	public function getInfo($assignment_master_id)
	{
		$sql = "
		SELECT school_auto_id, class_auto_id, section_id, subject_id FROM $this->_name
		WHERE
		id=$assignment_master_id
		AND
		status='Active'
		limit 1
		";
		
		return Zend_Db_Table::getDefaultAdapter()->query($sql)->fetch();
	}
	public function remove($id)
	{
		return $this->update( array('status' => 'Inactive'), array( 'id=?' => $id ));
	}
	 
	public function getTotal($id)
	{
		$row = $this->fetchAll(
		$this->select()
		->where('id=?', $id)
		);

		return sizeof($row);
	}

	public function getAll($id)
	{
		$row = $this->fetchAll(
		$this->select()
		->where('id=?', $id)
		);

		return $row;
	}

	public function getIdOne($sid, $cid, $secid, $subid, $staff_id)
	{
		$row = $this->fetchRow(
		$this->select()->setIntegrityCheck(false)
		->where('school_auto_id=?', $sid)
		->where('class_auto_id=?', $cid)
		->where('section_id=?', $secid)
		->where('subject_id=?', $subid)
		->where('staff_id=?', $staff_id)
		->limit(1, 0)
		);

		return sizeof($row)? $row->id: 0;
	}
	public function getInfoById($id, $str)
	{
		$row = $this->fetchRow(
		$this->select()
		->where('id=?', $id)
		->where('status=?', 'Active')
		->limit(1, 0)
		);

		return ( sizeof($row) )? $row->$str:'';
	}

	public function getInfoNotNULL($id, $str)
	{
		$row = $this->fetchRow(
		$this->select()->setIntegrityCheck(false)
		->where('status=?', 'Active')
		->where('id=?', $id)
		->where('class_auto_id!=?', 0)
		->where('section_id!=?', 0)
		->limit(1, 0)
		);

		return sizeof($row)? $row->$str: '';
	}

	public function getWhereClassSectionIsNull($id, $school_id)
	{
		return $this->fetchAll(
		$this->select()->setIntegrityCheck(false)
		->where('status=?', 'Active')
		->where('id=?', $id)
		->where('class_auto_id!=?', 0)
		->where('section_id!=?', 0)
		->where('school_auto_id=?', $school_id)
		->limit(1, 0)
		)->toArray();
	}

	public function isForGroup($id)
	{
		$row = $this->fetchRow(
		$this->select()->setIntegrityCheck(false)
		->where('status=?', 'Active')
		->where('id=?', $id)
		->where('class_auto_id=?', 0)
		->where('section_id=?', 0)
		->limit(1, 0)
		);

		return sizeof($row)? true: false;
	}

	public function getById($id, $school_id = false)
	{
		return $this->fetchRow(
		$this->select()
		->where('status=?', 'Active')
		->where('id=?', $id)
		->where('school_auto_id=?', $school_id)
		);
	}
	
	public function getByIdInfoDetails($id)
	{
		$sql = "SELECT school_auto_id, class_auto_id, section_id FROM $this->_name WHERE status='Active' and id=$id ";
		return (object)Zend_Db_Table::getDefaultAdapter()->query($sql)->fetch();
	}
	
	public function updateTopicStatus( $id)
	{
		$this->update( array('status' =>'Active'), array('id=?' => $id) );
	}



}