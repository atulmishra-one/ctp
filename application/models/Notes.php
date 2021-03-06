<?php

class Api_Model_Notes extends Zend_Db_Table_Abstract
{
	protected $_name = 'ctp_notes';

	protected static function getDBCache()
	{
		return new DBCache();
	}
	
	public function getNotesStudent( $data )
	{
		$cond = '';

		if( isset($data['date']) && sizeof($data['date'], 1) >=1
		&& sizeof($data['date'], 1) < 3 )
		{
			$cond .= " and n.notes_date_created BETWEEN DATE_SUB(NOW(), INTERVAL $data[date] DAY) AND NOW() ";
		}
		else if( isset($data['date']) && preg_match('#-#', $data['date']) )
		{
			$cond .= " and DATE(n.notes_date_created)='$data[date]'";
		}

		if (isset( $data['subject_id']) )
		{
			$cond .= " and n.subject_id=$data[subject_id]";
		}
		$getGroupAndSharedNotes = $this->getGroupAndSharedNotes();
		$getGroupAndSharedNotes = empty( $getGroupAndSharedNotes )? 0: $getGroupAndSharedNotes;

		/*
		$sqlPublic = "
            select *
            from 
            ctp_notes as n
            where 
            n.public != 0
            and
            n.public = 1 
            and 
			n.mode='TABLET'
			and
            n.notes_shared_with='public'
            $cond 
            group by n.notes_id
            limit 50
        ";
		*/
		$sqlClass = "
            select 
                n . *
            from
                ctp_notes as n,
                ctp_notes_sysn as nsysn
            where 
            nsysn.notes_id=n.notes_id
            and
            nsysn.student_id=$data[student_id]
            and
            nsysn.notes_sysn_status!='ON'
            and
            nsysn.notes_sysn_status='OFF'
            and
            n.notes_id NOT in ( $getGroupAndSharedNotes )
            and n.school_id=$data[school_id]
            and n.class_id=$data[class_id]
            and n.section_id=$data[section_id]
            and n.public=0
            and n.notes_shared=1
            $cond
            group by n.notes_id
            limit 50
        ";

            $sqlSelf = "
          select 
                n . *
            from
                ctp_notes as n,
                ctp_notes_sysn as nsysn
            where 
            nsysn.notes_id=n.notes_id
            and
            nsysn.student_id=$data[student_id]
            and
            nsysn.notes_sysn_status!='ON'
            and
            nsysn.notes_sysn_status='OFF'
            and
            n.notes_id NOT in ( select gn.notes_id from ctp_group_notes as gn)
            
            and n.school_id=$data[school_id]
            and n.class_id=$data[class_id]
            and n.section_id=$data[section_id]
            and n.public=0
            and n.student_id=$data[student_id]
            $cond
            group by n.notes_id
            limit 50
        ";
             
            $sqlgroup = "
            select 
                n . *
            from
                ctp_notes as n,
                ctp_group_notes as gh,
                ctp_group as g,
                ctp_group_member as gm,
                ctp_notes_sysn as nsysn
            where 
                nsysn.notes_id=n.notes_id
                and
                nsysn.student_id=$data[student_id]
                and
                nsysn.notes_sysn_status!='ON'
                and
                nsysn.notes_sysn_status='OFF'
                and
                gh.notes_id=n.notes_id
                and
                gh.group_id=g.ctp_group_id
                and
                g.ctp_group_id=gm.ctp_group_id
                and
                gm.member_id=$data[student_id]
                and
                n.school_id=$data[school_id]
                and
                n.public=0
                $cond
                group by n.notes_id
                limit 50
        ";

                $sqlin = "
            select n.*
            from ctp_notes as n,
            ctp_notes_share as ns,
            ctp_notes_sysn as nsysn
            where
            nsysn.notes_id=n.notes_id
            and
            nsysn.student_id=$data[student_id]
            and
            nsysn.notes_sysn_status!='ON'
            and
            nsysn.notes_sysn_status='OFF'
            and
            n.notes_id=ns.notes_id
            and
            ns.student_id=$data[student_id]
            and
            n.public=0
            $cond
            group by n.notes_id
            limit 50
        ";

        // $out1 = Zend_Db_Table::getDefaultAdapter()->query($sqlPublic)->fetchAll();
         $out2 = Zend_Db_Table::getDefaultAdapter()->query($sqlClass)->fetchAll();
         $out3 = Zend_Db_Table::getDefaultAdapter()->query($sqlgroup)->fetchAll();
         $out4 = Zend_Db_Table::getDefaultAdapter()->query($sqlin)->fetchAll();
         $out5 = Zend_Db_Table::getDefaultAdapter()->query($sqlSelf)->fetchAll();
                

         //       return array_merge($out1, $out2, $out3, $out4, $out5);
		 return array_merge($out2, $out3, $out4, $out5);

	}

	public function getNotesStudentByKeyword( $data )
	{
		$cond = '';

		if (isset( $data['keyword']) )
		{
			$cond .= " and ( n.notes_title like '%$data[keyword]%' or
        	n.notes_text like '%$data[keyword]%' ) ";
		}
		$getGroupAndSharedNotes = $this->getGroupAndSharedNotes();
		$getGroupAndSharedNotes = empty( $getGroupAndSharedNotes ) ? 0 : $getGroupAndSharedNotes;

		$sqlPublic = "
            select *
            from 
            ctp_notes as n
            where 
            n.public != 0
            and
            n.public = 1 
            and 
            n.notes_shared_with='public' 
            $cond
            group by n.notes_id
            limit 50
        ";

		$sqlClass = "
            select 
                n . *
            from
                ctp_notes as n
            where 
            n.notes_id NOT in ( $getGroupAndSharedNotes )
            and n.school_id=$data[school_id]
            and n.class_id=$data[class_id]
            and n.section_id=$data[section_id]
            and n.public=0
            and n.notes_shared=1
            $cond
            group by n.notes_id
            limit 50
        ";

            $sqlSelf = "
          select 
                n . *
            from
                ctp_notes as n
            where 
            n.notes_id NOT in ( select gn.notes_id from ctp_group_notes as gn)
            and n.school_id=$data[school_id]
            and n.class_id=$data[class_id]
            and n.section_id=$data[section_id]
            and n.public=0
            and n.student_id=$data[student_id]
            $cond
            group by n.notes_id
            limit 50
        ";
             
            $sqlgroup = "
            select 
                n . *
            from
                ctp_notes as n,
                ctp_group_notes as gh,
                ctp_group as g,
                ctp_group_member as gm
            where 
                gh.notes_id=n.notes_id
                and
                gh.group_id=g.ctp_group_id
                and
                g.ctp_group_id=gm.ctp_group_id
                and
                gm.member_id=$data[student_id]
                and
                n.school_id=$data[school_id]
                and
                n.public=0
                $cond
                group by n.notes_id
                limit 50
        ";

                $sqlin = "
            select n.*
            from ctp_notes as n,
            ctp_notes_share as ns
            where
            n.notes_id=ns.notes_id
            and
            ns.student_id=$data[student_id]
            and
            n.public=0
            $cond
            group by n.notes_id
            limit 50
        ";

          $out1 = Zend_Db_Table::getDefaultAdapter()->query($sqlPublic)->fetchAll();
          $out2 = Zend_Db_Table::getDefaultAdapter()->query($sqlClass)->fetchAll();
          $out3 = Zend_Db_Table::getDefaultAdapter()->query($sqlgroup)->fetchAll();
          $out4 = Zend_Db_Table::getDefaultAdapter()->query($sqlin)->fetchAll();
          $out5 = Zend_Db_Table::getDefaultAdapter()->query($sqlSelf)->fetchAll();

         return array_merge($out1, $out2, $out3, $out4, $out5);

	}

	public function getGroupAndSharedNotes()
	{
		$res = array_merge_recursive( $this->isBelongToGroup(), $this->isSharedWithClass() );
		$res = array_unique($res);
		return implode(',', $res);
	}

	public function isSharedWithClass()
	{
		$sql = "select notes_id from ctp_notes_share";
		$out = Zend_Db_Table::getDefaultAdapter()->query( $sql )->fetchAll();

		$ids = array();
		if (sizeof($out) )
		{
			foreach ( $out as $o )
			{
				$ids[] = $o['notes_id'];
			}
		}

		return $ids;
	}

	public function isBelongToGroup()
	{
		$sql = "select notes_id from ctp_group_notes";
		$out = Zend_Db_Table::getDefaultAdapter()->query( $sql )->fetchAll();

		$ids = array();
		if (sizeof($out) >=1 )
		{
			foreach ( $out as $o )
			{
				$ids[] = $o['notes_id'];
			}
		}

		return $ids;
	}

	public function getByTeacherN($data)
	{
		$cond = " and n.school_id=$data[school_id]";
		$cond1 = " and n.school_id=$data[school_id]";
		 
		if( isset( $data['class_id']) )
		{
			$cond .= " and n.class_id=$data[class_id]";
		}

		if( isset( $data['section_id']))
		{
			$cond .= " and n.section_id=$data[section_id]";
		}

		if( isset($data['subject_id']) )
		{
			$cond .= " and n.subject_id=$data[subject_id]";
		}

		if( isset($data['date']) && strlen($data['date']) >=1 && strlen($data['date']) < 3 )
		{
			$cond .= " and n.notes_date_created BETWEEN DATE_SUB(NOW(), INTERVAL $data[date] DAY) AND NOW() ";
			$cond1 .= " and n.notes_date_created BETWEEN DATE_SUB(NOW(), INTERVAL $data[date] DAY) AND NOW() ";
		}
		else if( isset($data['date']) && preg_match('#-#', $data['date']) )
		{
			$cond .= " and DATE(n.notes_date_created)='$data[date]'";
			$cond1 .= " and DATE(n.notes_date_created)='$data[date]'";
		}

		if( isset($data['teacher_id']))
		{
			$cond .= " and n.teacher_id=$data[teacher_id]";

		}


		/*
		$sqlpublic = "
            select *
            from 
            ctp_notes as n
            where 
            n.public != 0
            and
            n.public = 1 
			and 
			n.mode='TABLET'
            and 
            n.notes_shared_with='public'
            $cond1
            group by n.notes_id
            order by n.notes_id asc
            limit 50";
	   */
            $sqlm = "
            select n.*
                from 
            ctp_notes as n,
            ctp_notes_sysn as nsysn
            WHERE
            (
             nsysn.notes_id=n.notes_id
			 and 
             nsysn.teacher_id=$data[teacher_id]
             and
             nsysn.notes_sysn_status!='ON'
             and
             nsysn.notes_sysn_status='OFF'
             and
             n.public = 0
             $cond
            )
            group by n.notes_id
            order by n.notes_id asc
        ";

             $sqlin = "
            select n.*
            from ctp_notes as n,
            ctp_notes_share as ns,
            ctp_notes_sysn as nsysn
            where
            nsysn.notes_id=n.notes_id
			and 
            nsysn.teacher_id=$data[teacher_id]
            and
            nsysn.notes_sysn_status!='ON'
            and
            nsysn.notes_sysn_status='OFF'
            and
            n.notes_id=ns.notes_id
            and
            ns.teacher_id=$data[teacher_id]
            and
            n.public=0
            $cond1
            group by n.notes_id
            order by n.notes_id asc
        ";

            #print $sqlin;
           // $out1 = Zend_Db_Table::getDefaultAdapter()->query($sqlpublic)->fetchAll();
            $out2 = Zend_Db_Table::getDefaultAdapter()->query($sqlm)->fetchAll();
            $out3 = Zend_Db_Table::getDefaultAdapter()->query($sqlin)->fetchAll();


           // $res =  array_merge($out1, $out2, $out3);
		   $res =  array_merge($out2, $out3);
            array_multisort($res, SORT_ASC);
            return $res;

	}

	public function getByKeyword($data)
	{
		$cond = " and n.school_id=$data[school_id]";
		 
		if( isset($data['teacher_id']))
		{
			$cond .= " and n.teacher_id=$data[teacher_id]";

		}
		 
		if( isset($data['keyword']) )
		{
			$cond .= " and ( n.notes_title LIKE '%$data[keyword]%'
            or n.notes_text LIKE '%$data[keyword]%' ) ";
		}


		$sqlpublic = "
            select *
            from 
            ctp_notes as n
            where 
            n.public != 0
            and
            n.public = 1 
            and 
            n.notes_shared_with='public'
            and
            n.school_id=$data[school_id]
            and n.notes_title LIKE '%$data[keyword]%' 
            or
            n.notes_text LIKE '%$data[keyword]%'
            group by n.notes_id
            order by n.notes_id asc
            limit 50";

		$sqlm = "
            select n.*
                from 
            ctp_notes as n
            WHERE
            (
             n.public = 0
             $cond
            )
            group by n.notes_id
            order by n.notes_id asc
        ";

             $sqlin = "
            select n.*
            from ctp_notes as n,
            ctp_notes_share as ns
            where
            n.notes_id=ns.notes_id
            and
            ns.teacher_id=$data[teacher_id]
            and
            n.public=0
            $cond
            group by n.notes_id
            order by n.notes_id asc
        ";

            //print $sqlpublic;
            $out1 = Zend_Db_Table::getDefaultAdapter()->query($sqlpublic)->fetchAll();
            $out2 = Zend_Db_Table::getDefaultAdapter()->query($sqlm)->fetchAll();
            $out3 = Zend_Db_Table::getDefaultAdapter()->query($sqlin)->fetchAll();


            $res =  array_merge($out1, $out2, $out3);
            array_multisort($res, SORT_ASC);
            return $res;

	}

	public function create($data)
	{
		try {
			return $this->insert( array(
         'notes_title'      => htmlentities($data['notes_title'], ENT_QUOTES),
         'notes_filename'   => $data['notes_filename'],
         'school_id'        => $data['school_id'],
         'class_id'         => $data['class_id'],
         'section_id'       => $data['section_id'],
         'subject_id'       => $data['subject_id'],
         'teacher_id'       => (int)$data['teacher_id'],
         'student_id'       => (int)$data['student_id'],
         'notes_text'       => htmlentities($data['notes_text'], ENT_QUOTES ),
         'notes_author'     => $data['notes_author'],
         'notes_shared'     => $data['notes_shared'],
         'notes_version'    => 0,
         'notes_date_created' => new Zend_Db_Expr('NOW()'),
         'notes_status'     => $data['notes_status'],
         'public'           => $data['public'],
         'notes_shared_with' => $data['notes_shared_with'],
		 'mode'				  => $data['mode']
		));
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
		}
		
	}

	public function updateNotes($data)
	{
		return $this->update( array(
         'notes_title'      => htmlentities($data['notes_title'], ENT_QUOTES),
         'notes_filename'   => $data['notes_filename'],
         'school_id'        => $data['school_id'],
         'class_id'         => $data['class_id'],
         'section_id'       => $data['section_id'],
         'subject_id'       => $data['subject_id'],
         'teacher_id'       => $data['teacher_id'],
         'student_id'       => $data['student_id'],
         'notes_text'       => htmlentities($data['notes_text'], ENT_QUOTES),
         'notes_author'     => $data['notes_author'],
         'notes_shared'     => $data['notes_shared'],
         'notes_version'    => 0,
         'notes_date_modified' => new Zend_Db_Expr('NOW()'),
         'notes_status'     => $data['notes_status'],
         'public'           => $data['public'],
         'notes_shared_with'=> $data['notes_shared_with']
		), array(
            'notes_id=?' => $data['notes_id']
		));
	}

	public function getByIdClassSection($notes_id, $class_id, $section_id)
	{
		return $this->fetchAll(
		$this->select()
		->where('notes_id=?', $notes_id)
		->where('class_id=?', $class_id)
		->where('section_id=?', $section_id)
		);
	}



	public function sharedStudent($id, $sid)
	{
		$sql = $this->select()
		->where('notes_id=?', $id)
		->where('student_id=?', $sid)
		->where('notes_shared=?', 1);
		return $this->fetchAll( $sql);
		#print $sql;
	}

	public function createdStudent($id, $sid)
	{
		return $this->fetchAll(
		$this->select()
		->where('notes_id=?', $id)
		->where('student_id=?', $sid)
		->where('notes_shared=?', 0)
		);
	}

	public function recievedStudent($id, $sid)
	{
		return $this->fetchAll(
		$this->select()
		->where('notes_id=?', $id)
		->where('student_id!=?', $sid)
		->where('notes_shared=?', 1)
		);
	}


	public function sharedTeacher($id, $sid)
	{
		$sql = $this->select()
		->where('notes_id=?', $id)
		->where('teacher_id=?', $sid)
		->where('notes_shared=?', 1);
		return $this->fetchAll( $sql);
		#print $sql;
	}

	public function createdTeacher($id, $sid)
	{
		return $this->fetchAll(
		$this->select()
		->where('notes_id=?', $id)
		->where('teacher_id=?', $sid)
		->where('notes_shared=?', 0)
		);
	}

	public function recievedTeacher($id, $sid)
	{
		return $this->fetchAll(
		$this->select()
		->where('notes_id=?', $id)
		->where('teacher_id!=?', $sid)
		->where('notes_shared=?', 1)
		);
	}
	
	public function updateNotesShared($nid, $with)
	{
		$sql = "UPDATE $this->_name SET notes_shared_with='$with' WHERE notes_id=$nid ";
		Zend_Db_Table::getDefaultAdapter()->query($sql);
	}
}
