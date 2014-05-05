<?php

class Api_Model_Notification extends Zend_Db_Table_Abstract
{
	protected $_name = 'ctp_notification';
	
    
	public function saveForStudent(array $data)
	{
		 $this->insert( array(
			'notification_type_id' => $data['type_id'],
			'school_id'	   		   => $data['school_id'],
			'class_id'			   => $data['class_id'],
			'section_id'		   => $data['section_id'],
			'student_id'		   => $data['student_id'],
			'version'			   => 0,
			'date_created'		   => new Zend_Db_Expr('NOW()'),
            'notify_by'            => (string)$data['notify_by'],
            'notify_by_id'         => (int)$data['notify_by_id']
		) );
	}
	
	public function batchSaveForStudent($datas)
	{
		$value = implode(',', $datas);
		
		$sql = "INSERT INTO $this->_name 
		(notification_type_id, school_id,class_id,section_id,student_id,version,date_created,notify_by,notify_by_id)
		VALUES
		$value
		";
		
		return Zend_Db_Table::getDefaultAdapter()->query($sql);
	}
    
	public function saveForTeacher(array $data)
	{
		 $this->insert( array(
			'notification_type_id' => $data['type_id'],
			'school_id'	   		   => $data['school_id'],
			'class_id'			   => $data['class_id'],
			'section_id'		   => $data['section_id'],
			'teacher_id'		   => $data['teacher_id'],
			'version'			   => 0,
			'date_created'		   => new Zend_Db_Expr('NOW()'),
            'notify_by'            => (string)$data['notify_by'],
            'notify_by_id'         => (int)$data['notify_by_id']
		) );
	}
	
	public function saveForTeacherBatch($data)
	{
		$data = implode(',' , $data);
		 $sql = "
		 INSERT INTO $this->_name
		 (notification_type_id, school_id, class_id, section_id, teacher_id, date_created, notify_by, notify_by_id)
		 VALUES
		 $data
		 ";
		 
		 Zend_Db_Table::getDefaultAdapter()->query($sql);
	}
	
	public function getByStudentId($school_id, $student_id)
	{
		
		$sql = "
		SELECT n.*, n.date_created as date_recieved, nt.notification_type_type, nt.notification_type_msg,
		 nt.priority FROM $this->_name as n, ctp_notification_type nt WHERE
		nt.notification_type_id=n.notification_type_id
		AND
		n.school_id=$school_id
		AND
		n.student_id=$student_id
		AND
		n.read !=1
		AND
		n.read = 0
		AND 
		n.date_created >= date_sub(NOW(), interval 30 second )
		AND
		nt.priority='HIGH'
		ORDER BY n.date_created DESC
		";
		
		return Zend_Db_Table::getDefaultAdapter()->query($sql)->fetchAll();
	}
	
	public function getByStudentIdLow($school_id, $student_id)
	{
		
		$sql = "
		SELECT n.*, n.date_created as date_recieved, nt.notification_type_type, nt.notification_type_msg,
		 nt.priority FROM $this->_name as n, ctp_notification_type nt WHERE
		nt.notification_type_id=n.notification_type_id
		AND
		n.school_id=$school_id
		AND
		n.student_id=$student_id
		AND
		n.read !=1
		AND
		n.read = 0
		AND
		nt.priority='LOW'
		ORDER BY n.date_created DESC
		";
		
		return Zend_Db_Table::getDefaultAdapter()->query($sql)->fetchAll();
	}
	
	public function getByStudentIdDate($school_id, $student_id)
	{
			 $select = $this->select()->setIntegrityCheck(false);
			 $select->from($this, array('date_created as date_recieved',
			 							 'notification_id as notification_id',
										 'student_id as student_id',
                                         'notify_by',
                                         'notify_by_id',
                                         'school_id',
                                         'class_id',
                                         'section_id',
										 'read as read') )
					->join('ctp_notification_type', 
					'ctp_notification_type.notification_type_id = ctp_notification.notification_type_id')
					->where('ctp_notification.school_id=?', $school_id)
					->where('ctp_notification.student_id=?', $student_id)
					->where('DATE(ctp_notification.date_created)= DATE( NOW() )')
                    
					->order('ctp_notification.date_created DESC');
					//print $select;
		return $this->fetchAll($select)->toArray();
	}
	
	public function getByTeacherId($school_id, $teacher_id)
	{
		$sql = "
		SELECT n.*, n.date_created as date_recieved, nt.notification_type_type, nt.notification_type_msg, nt.priority FROM $this->_name as n , ctp_notification_type nt WHERE
		nt.notification_type_id=n.notification_type_id
		AND
		n.school_id=$school_id
		AND
		n.teacher_id=$teacher_id
		AND
		n.read !=1
		AND
		n.read = 0
		ORDER BY n.date_created DESC
		";
		//print $sql;
		return Zend_Db_Table::getDefaultAdapter()->query($sql)->fetchAll();
	}
    
    public function markReadDate()
    {
        $this->update( array(
            'read' => 1
        ), array(
            'DATE(date_created) < (?)' => new Zend_Db_Expr('CURDATE()')
        ));
    }
	
	public function getByTeacherIdDate($school_id, $teacher_id)
	{
			 $select = $this->select()->setIntegrityCheck(false);
			 $select->from($this, array('date_created as date_recieved',
			 							 'notification_id as notification_id',
										 'teacher_id as teacher_id',
                                         'notify_by',
                                         'notify_by_id',
                                         'school_id',
                                         'class_id',
                                         'section_id',
										 'read as read') )
					->join('ctp_notification_type', 
					'ctp_notification_type.notification_type_id = ctp_notification.notification_type_id')
					->where('ctp_notification.school_id=?', $school_id)
					->where('ctp_notification.teacher_id=?', $teacher_id)
					->where('DATE(ctp_notification.date_created)= DATE( NOW() )')
					->order('ctp_notification.date_created DESC');
					
		return $this->fetchAll($select)->toArray();
	}
	
	public function markAsReadByStudentId($id)
	{
		$this->update( array('read' => 1), array('student_id=?' => $id) );
	}
	
	public function markAsReadById($id)
	{
		$this->update( array('read' => 1), array('notification_id=?' => $id) );
	}
	
	public function markAsReadByTeacherId($id)
	{
		$this->update( array('read' => 1), array('teacher_id=?' => $id) );
	}
	
	public function markAsReadByNotificationType($id)
	{
		#$this->update( array('read' => 1), array('notification_type_id=?' => $id) );
	}
    
    public function markAsReadByNotificationPriority($user_type, $uid)
	{
             
       /* $sql = "
            UPDATE  `ctp_notification` SET  `read` =1 WHERE $user_type = $uid AND notification_type_id IN (
            SELECT notification_type_id
            FROM ctp_notification_type
            WHERE priority =  'HIGH'
            )
       ";*/

        $sql = "
	 UPDATE  `ctp_notification` ctp, ctp_notification_type ctpt SET  ctp.`read` =1 
	 WHERE ctp.$user_type = $uid AND ctp.notification_type_id = ctpt.notification_type_id and ctpt.priority =  'HIGH' and ctp.`read` != 1
	";
       
       return Zend_Db_Table::getAdapter()->query($sql);
	}

	
    public function markAsReadByNotificationPriorityBeforeOneMInute($school_id, $student_id)
    {
             
      /* $sql = "
            UPDATE  `ctp_notification` SET  `read` =1 WHERE student_id = $student_id AND school_id=$school_id
            AND TIMESTAMPDIFF(MINUTE, date_created, NOW() ) > 1
            AND notification_type_id IN (
            SELECT notification_type_id
            FROM ctp_notification_type
            WHERE priority =  'HIGH'
            )
       ";
       */

      	/*$sql = "UPDATE  `ctp_notification` ctp, ctp_notification_type ctpt 
	SET  ctp.`read` =1 WHERE ctp.student_id = $student_id AND ctp.school_id=$school_id AND 
	TIMESTAMPDIFF(MINUTE, ctp.date_created, NOW() ) > 1 AND ctp.notification_type_id = ctpt.notification_type_id and ctpt.priority =  'HIGH'";
        */

	$sql = "UPDATE  `ctp_notification` ctp, ctp_notification_type ctpt SET  ctp.`read` =1
		 WHERE ctp.student_id = $student_id AND ctp.school_id=$school_id AND ctp.date_created < date_sub(NOW(), interval 1 minute ) AND 				ctp.notification_type_id = ctpt.notification_type_id and ctpt.priority =  'HIGH' and ctp.`read` != 1 ";

       return Zend_Db_Table::getAdapter()->query($sql);
    }
 
	public function deleteR($id)
	{
		$this->delete( array( 'notification_id=?' => $id ));
	}
	
	

}
