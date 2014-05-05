<?php

class Api_Model_Attendence extends Zend_Db_Table_Abstract {

    protected $_name = 'ctp_e_attendence';

    public function save(array $data) {
        return $this->insert(array(
                    'student_id' => $data['student_id'],
                    'ctp_attendence_master_id' => $data['attendence_master_id'],
                    'attend' => $data['attend'],
                    'version' => 0,
                    'date_created' => new Zend_Db_Expr('NOW()'),
                    'join_status' => $data['join_status']
                ));
    }

    public function updateRowsAttend(array $data) {

       /* return $this->update(array(
                    'attend' => $data['attend'],
                    'date_created' => new Zend_Db_Expr('NOW()'),
                    'date_modified' => new Zend_Db_Expr('NOW()'),
                    'join_status' => $data['join_status']
                        ), array(
                    'ctp_attendence_master_id=?' => $data['attendence_master_id'],
                    'student_id=?' => $data['student_id']
                        )
        );
        */
    	$sql = "UPDATE $this->_name SET 
    	attend = $data[attend], 
    	date_created = NOW(), 
    	date_modified= NOW(), 
    	join_status=$data[join_status]
    	WHERE
    	ctp_attendence_master_id=$data[attendence_master_id]
    	AND
    	student_id=$data[student_id]
    	";
    	
    	return Zend_Db_Table::getDefaultAdapter()->query($sql);
    }

    public function updateRows(array $data) {

        return $this->update(array(
                    'date_created' => new Zend_Db_Expr('NOW()'),
                    'date_modified' => new Zend_Db_Expr('NOW()'),
                    'join_status' => $data['join_status']
                        ), array(
                    'ctp_attendence_master_id=?' => $data['attendence_master_id'],
                    'student_id=?' => $data['student_id']
                        )
        );
    }

    public function updateRowsPut(array $data) {

        return $this->update(array(
                    'date_created' => new Zend_Db_Expr('NOW()'),
                    'date_modified' => new Zend_Db_Expr('NOW()'),
                    'attend' => $data['attend']
                        ), array(
                    'ctp_attendence_master_id=?' => $data['attendence_master_id'],
                    'student_id=?' => $data['student_id']
                        )
        );
    }

    public function isStudentForToday($id, $attendence_master_id) {

        $row = $this->fetchRow(
                $this->select()
                        ->where('student_id=?', $id)
                        ->where('ctp_attendence_master_id=?', $attendence_master_id)
                        ->where('DATE(date_created)=CURDATE()')
        );

        return ( count($row) ) ? true : false;
    }

    public function isStudentThere($id, $attendence_master_id) {

        /*$row = $this->fetchRow(
                $this->select()
                        ->where('student_id=?', $id)
                        ->where('ctp_attendence_master_id=?', $attendence_master_id)
        );

        return ( count($row) ) ? true : false;
        */
    	$sql = "
    	SELECT e_attendence_id FROM $this->_name WHERE
    	student_id=$id
    	AND
    	ctp_attendence_master_id=$attendence_master_id
    	LIMIT 1
    	";
    	$row = Zend_Db_Table::getDefaultAdapter()->query($sql)->fetch();
    	return sizeof($row) ? true : false;
    }

    public function isToday($attendence_master_id) {
        
        $sql = "SELECT e_attendence_id 
        FROM ctp_e_attendence 
        WHERE 
        ctp_attendence_master_id= $attendence_master_id
        AND
        DATE(date_created) = CURDATE()
        LIMIT 1
        ";

        $row = Zend_Db_Table::getDefaultAdapter()->query($sql)->fetch();

        //echo $sql->__toString();

        return ( sizeof($row) ) ? true : false;
    }

    public function XtimeDiFF($attendence_master_id, $x_time) {

        $sql = $this->select()
                ->where('ctp_attendence_master_id=?', $attendence_master_id)
                ->where('TIMESTAMPDIFF(MINUTE, date_created, NOW() ) < (?)', $x_time);

        $row = $this->fetchRow($sql);
        //echo $sql->__toString();
        return ( count($row) ) ? true : false;
    }

    public function getByAttendenceMasterId($attendence_master_id) {
        $row = $this->fetchAll(
                        $this->select()
                                ->where('ctp_attendence_master_id=?', $attendence_master_id)
                                ->order('date_created DESC')
                )->toArray();

        return ( count($row) ) ? $row : array();
    }
    
    public function getAllAttendenceMasterId($attendence_master_id) {
        $sql = "
        SELECT student_id, attend, join_status FROM $this->_name
        WHERE
        ctp_attendence_master_id=$attendence_master_id
        ORDER BY date_created DESC
        ";
        
        return Zend_Db_Table::getDefaultAdapter()->query($sql)->fetchAll();
    }

    public function getByAttendenceMasterIdAndStudentId($attendence_master_id, $student_id) {
       /* $row = $this->fetchAll(
                        $this->select()
                                ->where('ctp_attendence_master_id=?', $attendence_master_id)
                                ->where('student_id=?', $student_id)
                                ->order('date_created DESC')
                )->toArray();

        return ( count($row) ) ? $row : array();
        */
    	$sql = "SELECT attend FROM $this->_name WHERE 
    	ctp_attendence_master_id=$attendence_master_id
    	AND
    	student_id=$student_id
    	ORDER BY date_created DESC
    	LIMIT 1
    	";
    	$row = Zend_Db_Table::getDefaultAdapter()->query($sql)->fetch();
    	return sizeof( $row )? $row['attend'] : 0;
    }
    
	public function getByAttendenceMasterIdAndStudentIdArray($attendence_master_id, $student_id) {
       
		$student_id = implode(',', $student_id);
		
    	$sql = "SELECT attend, student_id FROM $this->_name WHERE 
    	ctp_attendence_master_id=$attendence_master_id
    	AND
    	student_id IN($student_id)
    	ORDER BY date_created DESC
    	";
    	//print $sql;
    	return Zend_Db_Table::getDefaultAdapter()->query($sql)->fetchAll();
    }
    
	public function getAttendStudent($attendence_master_id, $student_id) {
       /* $row = $this->fetchAll(
                        $this->select()
                                ->where('ctp_attendence_master_id=?', $attendence_master_id)
                                ->where('student_id=?', $student_id)
                                ->order('date_created DESC')
                )->toArray();

        return ( count($row) ) ? $row : array();
        */
    	$sql = "SELECT attend FROM $this->_name WHERE 
    	ctp_attendence_master_id=$attendence_master_id
    	AND
    	student_id=$student_id
    	ORDER BY date_created DESC
    	";
    	$row = Zend_Db_Table::getDefaultAdapter()->query($sql)->fetchAll();
    	return sizeof( $row )? $row : array();
    }

    public function getByAttendenceMasterIdAndStudentIdAndDate($attendence_master_id, $student_id, $date) {
        if (strlen(trim($date)) >= 1 && strlen(trim($date)) < 3) {
            $row = $this->fetchAll(
                            $this->select()
                                    ->where('ctp_attendence_master_id=?', $attendence_master_id)
                                    ->where('student_id=?', $student_id)
                                    ->where('date_created BETWEEN DATE_SUB(NOW(), INTERVAL ' . $date . ' DAY) AND NOW() ')
                                    ->order('date_created DESC')
                    )->toArray();
        } else {
            $row = $this->fetchAll(
                            $this->select()
                                    ->where('ctp_attendence_master_id=?', $attendence_master_id)
                                    ->where('student_id=?', $student_id)
                                    ->where('DATE(date_created)=?', $date)
                                    ->order('date_created DESC')
                    )->toArray();
        }

        return ( count($row) ) ? $row : array();
    }

    public function getByAttendenceMasterIdAndDate($attendence_master_id, $date) {
        if (strlen(trim($date)) >= 1 && strlen(trim($date)) < 3) {
            $row = $this->fetchAll(
                            $this->select()
                                    ->where('ctp_attendence_master_id=?', $attendence_master_id)
                                    ->where('date_created BETWEEN DATE_SUB(NOW(), INTERVAL ' . $date . ' DAY) AND NOW() ')
                                    ->order('date_created DESC')
                    )->toArray();
        } else {
            $row = $this->fetchAll(
                            $this->select()
                                    ->where('ctp_attendence_master_id=?', $attendence_master_id)
                                    ->where('DATE(date_created)=?', $date)
                                    ->order('date_created DESC')
                    )->toArray();
        }

        return ( count($row) ) ? $row : array();
    }

    public function getByAttendanceMasterIdAndAttend($id) {
    	
    	$sql = "SELECT student_id FROM $this->_name WHERE
    	ctp_attendence_master_id=$id
    	AND
    	attend=1
    	GROUP BY student_id
    	";
    	return Zend_Db_Table::getDefaultAdapter()->query($sql)->fetchAll(false,'student_id');
    }
    
	public function getByAttendanceMasterIdAndAttendArray($id) {
    	
    	$sql = "SELECT student_id FROM $this->_name WHERE
    	ctp_attendence_master_id=$id
    	AND
    	attend=1
    	GROUP BY student_id
    	";
    	return Zend_Db_Table::getDefaultAdapter()->query($sql)->fetchAll();
    }

}
