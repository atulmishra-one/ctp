<?php

class Api_Model_DiaryMaster extends Zend_Db_Table_Abstract
{
	protected $_name='ctp_master_diary';

	public function save($data)
	{
		return $this->insert( array(
         'type'         => $data['type'],
         'user_id'      => $data['user_id'],
         'user_type'    => $data['user_type'],
         'text'         => htmlentities($data['text'], ENT_QUOTES),
         'attachments'  => $data['attachments'],
         'school_id'    => $data['school_id'],
         'class_id'     => $data['class_id'],
         'section_id'   => $data['section_id'],
         'subject_id'   => $data['subject_id'],
         'date_created' => new Zend_Db_Expr('NOW()'),
         'status'       => $data['status'],
         'shared_with'  => $data['shared_with']
		));
	}

	public function updateDiary($data)
	{
		return $this->update( array(
         'type'         => $data['type'],
         'user_id'      => $data['user_id'],
         'user_type'    => $data['user_type'],
         'text'         => htmlentities($data['text'], ENT_QUOTES),
         'attachments'  => $data['attachments'],
         'school_id'    => $data['school_id'],
         'class_id'     => $data['class_id'],
         'section_id'   => $data['section_id'],
         'subject_id'   => $data['subject_id'],
         'status'       => $data['status'],
         'shared_with'  => $data['shared_with']
		), array(
            'id=?' => $data['diary_id']
		));
	}

	/**
	 * Previous name getResults
	 * @param (string) $user_type
	 * @param (array) $fetch
	 * @param (int) $subject_id
	 * @param (int) $teacher_id
	 */
	public function getResultsForTeacher($user_type, $fetch, $teacher_id)
	{
		$cond = '';
		
		if( isset( $fetch['subject_id']) )
		{
			$cond .= " AND (m.subject_id = $subject_id) ";
		}

		if( isset( $fetch['school_id']))
		{
			$school_id = $fetch['school_id'];
			$cond .= " AND (m.school_id=$school_id)";
		}

		if( isset( $fetch['class_id']))
		{
			$class_id = $fetch['class_id'];
			$cond .= " AND (m.class_id=$class_id)";
		}

		if( isset( $fetch['section_id']))
		{
			$section_id = $fetch['section_id'];
			$cond .= " AND (m.section_id=$section_id)";
		}

		if( isset( $fetch['user_id']) )
		{
			$user_id = $fetch['user_id'];
			$cond .= " AND (m.user_id = $user_id)";
		}


		if( isset($fetch['date']) && strlen($fetch['date']) >=1 && strlen($fetch['date']) < 3 )
		{
			$cond .= " and m.date_created BETWEEN DATE_SUB(NOW(), INTERVAL $fetch[date] DAY) AND NOW() ";
		}
		else if( isset($fetch['date']) && preg_match('#-#', $fetch['date']) )
		{
			$cond .= " and DATE(m.date_created)='$fetch[date]'";
		}

		if( isset( $fetch['id']))
		{
			$id = implode(',', $fetch['id']);
			$cond .= " AND m.id IN ($id)";
			$u = "";
		}
		else
		{
			$u = " AND (m.user_type = '$user_type')";
		}

		 
		 

		$sql= " SELECT
            `m`.`id` AS `mid`,
            `m`.`type`,
            `m`.`text`,
            `m`.`attachments`,
            `m`.`school_id`,
            `m`.`class_id`,
            `m`.`section_id`,
            `m`.`subject_id`,
            `m`.`date_created`,
            `m`.`status`,
             m.shared_with,
             m.user_id,
             m.user_type,
            `cl`.`class_name`,
            `g`.`group_name` AS `section_name`,
            `sub`.`subject_name`
                FROM
            `ctp_master_diary` AS `m`
                INNER JOIN
            `salaah_class` AS `cl`
                INNER JOIN
            `school_cce_group` AS `g`
                INNER JOIN
            `school_cce_subject` AS `sub`
            INNER JOIN 
            ctp_diary_sysn as dsyn
	    INNER JOIN
            master_section as ms
                WHERE
            (
                dsyn.diary_id=m.id
                AND
                dsyn.diary_sysn_status='OFF'
                AND
                dsyn.teacher_id=$teacher_id
                AND
                cl.class_auto_id = m.class_id
            )
               
		AND (m.section_id=ms.id)
		AND (g.group_id = ms.group_id)
		
                AND (sub.sub_auto_id = m.subject_id)
                $u
                $cond
                group by m.id
                order by m.id asc
                
                ";
                //print $sql;
                
                if( isset( $fetch['class_section']) )
                {
                	return Zend_Db_Table::getDefaultAdapter()->query($sql)->fetch();
                }
                else
              {
                	return Zend_Db_Table::getDefaultAdapter()->query($sql)->fetchAll();
                }
                 
	}

	public function getResultsByIdTeacher($teacher_id, $ids)
	{
		$id = implode(',', $ids);
		$cond = " AND m.id IN ($id)";

        
		$sql= " SELECT
            `m`.`id` AS `mid`,
            `m`.`type`,
            `m`.`text`,
            `m`.`attachments`,
            `m`.`school_id`,
            `m`.`class_id`,
            `m`.`section_id`,
            `m`.`subject_id`,
            `m`.`date_created`,
            `m`.`status`,
             m.shared_with,
             m.user_id,
             m.user_type,
            `cl`.`class_name`,
            `g`.`group_name` AS `section_name`,
            `sub`.`subject_name`
                FROM
            `ctp_master_diary` AS `m`
                INNER JOIN
            `salaah_class` AS `cl`
                INNER JOIN
            `school_cce_group` AS `g`
                INNER JOIN
            `school_cce_subject` AS `sub`
            INNER JOIN 
            ctp_diary_sysn as dsyn
	    INNER JOIN
            master_section as ms
                WHERE
            (
                dsyn.diary_id=m.id
                AND
                dsyn.diary_sysn_status='OFF'
                AND
                dsyn.teacher_id=$teacher_id
                AND
                cl.class_auto_id = m.class_id
            )
		AND (m.section_id=ms.id)
		AND (g.group_id = ms.group_id)
                AND (sub.sub_auto_id = m.subject_id)
                $cond
                group by m.id
                order by m.id asc
                
                ";

                //print $sql;
                return Zend_Db_Table::getDefaultAdapter()->query($sql)->fetchAll();
                 
	}

	public function getResultsForStudent($school_id, $class_id, $section_id, $user_id, $student_id , $date = false)
	{

		if( isset($date) && strlen($date) >=1 && strlen($date) < 3 )
		{
			$cond = " and m.date_created BETWEEN DATE_SUB(NOW(), INTERVAL $date DAY) AND NOW() ";
		}
		else if( isset($date) && preg_match('#-#', $date) )
		{
			$cond = " and DATE(m.date_created)='$date'";
		}
		else
		{
			$cond = '';
		}
		$sql1 = "SELECT
                m.*,
	           cl.class_name,
	           grp.group_name as section_name,
               subj.subject_name
            FROM
           `ctp_master_diary` m,
            student as stu,
            salaah_class as cl,
            school_cce_group as grp,
	        school_cce_subject as subj,
            ctp_diary_sysn as dsyn,
	    master_section as ms
            WHERE
            (
              dsyn.diary_id=m.id
              and
              dsyn.diary_sysn_status='OFF'
              and
              dsyn.student_id=$student_id
              and
		      m.school_id = $school_id 
		      and m.class_id = $class_id
              and m.section_id = $section_id
              and m.user_id = $user_id
              and m.user_type = 'STUDENT'
              and m.user_id = stu.user_id
		      and m.class_id = cl.class_auto_id
		      and ms.id=m.section_id
		      and ms.group_id = grp.group_id
		      and m.subject_id = subj.sub_auto_id
		      $cond
		      )
              
               group by m.id
               order by m.id asc
              
              " ;  


		      $sql2= "SELECT
                m.*,
	           cl.class_name,
	           grp.group_name as section_name,
               subj.subject_name
            FROM
           `ctp_master_diary` m,
            ctp_diary_share ds,
            student as stu,
            salaah_class as cl,
            school_cce_group as grp,
	        school_cce_subject as subj,
            ctp_diary_sysn as dsyn,
		master_section as ms
            WHERE
		   (
              dsyn.diary_id=m.id
              and
              dsyn.diary_sysn_status='OFF'
              and
              dsyn.student_id=$student_id
              and
		      m.shared_with='student'
		      and ds.master_diary_id = m.id
              and ds.student_id = stu.id
		      and stu.user_id= $user_id
		      and m.class_id = cl.class_auto_id
		      and ms.id=m.section_id
		      and ms.group_id = grp.group_id
		      and m.subject_id = subj.sub_auto_id
		      $cond
		    )
               group by m.id
        order by m.id asc
              ";

		       
		      $sql3= "SELECT m.*,
	           cl.class_name,
	           grp.group_name as section_name,
               subj.subject_name
            FROM
           `ctp_master_diary` m,
            ctp_diary_share ds,
            student as stu,
            salaah_class as cl,
            school_cce_group as grp,
	        school_cce_subject as subj,
            ctp_diary_sysn as dsyn,
            master_section as ms
            WHERE
		  (
              dsyn.diary_id=m.id
              and
              dsyn.diary_sysn_status='OFF'
              and
              dsyn.student_id=$student_id
              and
		      m.id = ds.master_diary_id
		      and m.shared_with='class'
              and ds.class_id = $class_id
              and ds.section_id = $section_id
              and stu.user_id = $user_id
		      and m.class_id = cl.class_auto_id
		      and ms.id=m.section_id
		      and ms.group_id = grp.group_id
		      and m.subject_id = subj.sub_auto_id
		      $cond
		  )
         
          group by m.id
          order by m.id asc
         ";   


		      $sql4 = "
            SELECT 
                m.*,
	           cl.class_name,
	           grp.group_name as section_name,
               subj.subject_name
            FROM
           `ctp_master_diary` m,
            ctp_diary_share ds,
            student as stu,
            ctp_group as gp,
            ctp_group_member as gpm,
	        salaah_class as cl,
            school_cce_group as grp,
	        school_cce_subject as subj,
            ctp_diary_sysn as dsyn,
	    master_section as ms
            WHERE
		  (
            dsyn.diary_id=m.id
            and
            dsyn.diary_sysn_status='OFF'
            and
            dsyn.student_id=$student_id
            and
			m.id=ds.master_diary_id
			and m.shared_with='group'
			and ds.group_id=gp.ctp_group_id
			and gp.ctp_group_id=gpm.ctp_group_id
			and gpm.member_id=stu.id
			and stu.user_id=$user_id
			and m.class_id = cl.class_auto_id
			and ms.id=m.section_id
		    and ms.group_id = grp.group_id
			and m.subject_id = subj.sub_auto_id
			$cond
		  )
        
        group by m.id
        order by m.id asc
        ";

			# echo $sql1, "\n", $sql2, "\n", $sql3, "\n", $sql4;

			$out1 = Zend_Db_Table::getDefaultAdapter()->query($sql1)->fetchAll();
			$out2 = Zend_Db_Table::getDefaultAdapter()->query($sql2)->fetchAll();
			$out3 = Zend_Db_Table::getDefaultAdapter()->query($sql3)->fetchAll();
			$out4 = Zend_Db_Table::getDefaultAdapter()->query($sql4)->fetchAll();

			return array_merge($out1, $out2, $out3, $out4);
	}

	public function isCreated($mid, $uid)
	{
		$row = $this->fetchRow(
		$this->select()
		->where('id=?', $mid)
		->where('user_id=?', $uid)
		);

		return sizeof( $row) ? true: false;
	}

	public function notice($user_id, $date, $user_type)
	{

		if( isset($date) && strlen($date) >=1 && strlen($date) < 3 )
		{
			$cond = " AND a.start_date BETWEEN DATE_SUB(NOW(), INTERVAL $date DAY) AND NOW() ";
		}
		else if( isset($date) && preg_match('#-#', $date) )
		{
			$cond = " AND ( DATE(a.start_date) <= '$date'
                AND '$date' <= DATE(a.end_date) )";
		}

		$sql = "
            SELECT 
            `a` . *, TIMESTAMP(a.start_date) as start_date, `aa` . *
            FROM
            `announcement` AS `a`
                INNER JOIN
            `announcement_assigned` AS `aa` ON a.announcement_id = aa.announcement_id
            WHERE
            (a.type = 'Notice') AND (a.status = '1')
                AND (aa.status = '1')
                $cond
                AND aa.assigned_to_id = $user_id
                AND aa.assigned_to_id_type = '$user_type'
        ";

                $sqlAll = "
            SELECT 
            `a` . *, TIMESTAMP(a.start_date) as start_date, `aa` . *
             FROM
            `announcement` AS `a`
             INNER JOIN
            `announcement_assigned` AS `aa` ON a.announcement_id = aa.announcement_id
                WHERE
            (a.type = 'Notice') AND (a.status = '1')
                AND (aa.status = '1')
                $cond
                AND aa.assigned_to_id_type = 'All'
        ";

                #print $sql;
                $out =  Zend_Db_Table::getDefaultAdapter()->query($sql)->fetchAll();
                $out1 =  Zend_Db_Table::getDefaultAdapter()->query($sqlAll)->fetchAll();

                return array_merge($out, $out1);
	}

	public function circular($user_id, $date, $user_type)
	{


		if( isset($date) && strlen($date) >=1 && strlen($date) < 3 )
		{
			$cond = " AND a.start_date BETWEEN DATE_SUB(NOW(), INTERVAL $date DAY) AND NOW() ";
		}
		else if( isset($date) && preg_match('#-#', $date) )
		{
			$cond = " AND ( DATE(a.start_date) <= '$date'
                AND '$date' <= DATE(a.end_date) )";
		}

		$sql = "
            SELECT 
            `a` . *, TIMESTAMP(a.start_date) as start_date, `aa` . *
            FROM
                `announcement` AS `a`
                INNER JOIN
            `announcement_assigned` AS `aa` ON a.announcement_id = aa.announcement_id
            WHERE
                (a.type = 'Circular')
                    AND (a.status = '1')
                    AND (aa.status = '1')
                    $cond
                    AND aa.assigned_to_id = $user_id
                    AND aa.assigned_to_id_type = '$user_type'
        ";

                    $sqlAll = "
            SELECT 
                `a` . *, TIMESTAMP(a.start_date) as start_date, `aa` . *
            FROM
            `announcement` AS `a`
                INNER JOIN
            `announcement_assigned` AS `aa` ON a.announcement_id = aa.announcement_id
            WHERE
                (a.type = 'Circular')
                AND (a.status = '1')
                AND (aa.status = '1')
                $cond
                AND aa.assigned_to_id_type = 'All'
        ";

                #print $sqlAll;
                $out =  Zend_Db_Table::getDefaultAdapter()->query($sql)->fetchAll();
                $out1 =  Zend_Db_Table::getDefaultAdapter()->query($sqlAll)->fetchAll();

                return array_merge($out, $out1);
	}



}

















