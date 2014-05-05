<?php

class Api_Model_ClassLms extends Zend_Db_Table_Abstract {

    protected $_name = 'ctp_class_lms_url';

    public function save(array $data) {
        if ($this->getIdCheck($data['class_session_id'])) {
            $this->update(array(
                'current_url' => $data['current_url'],
                'mode' => $data['mode']
                    ), array('class_session_id=?' => $data['class_session_id']));

            return true;
        } else {
            return $this->insert(array(
                        'current_url' => $data['current_url'],
                        'class_session_id' => $data['class_session_id'],
                        'mode' => $data['mode']
            ));
        }
    }

    public function getIdCheck($id) {
        $row = $this->fetchRow(
                $this->select()
                        ->where('class_session_id=?', $id)
        );

        return ( sizeof($row)) ? true : false;
    }

    public function getId($id) {
        $row = $this->fetchRow(
                $this->select()
                        ->where('class_session_id=?', $id)
        );

        return ( sizeof($row)) ? $row : false;
    }

    public function getCurrentUrl($class_session_id) {
       /* $row = $this->fetchRow(
                $this->select()
                        ->where('class_session_id=?', $class_session_id)
        );

        return ( sizeof($row)) ? $row->current_url : '';
        */
    	$sql = "
    	SELECT current_url FROM $this->_name WHERE
    	class_session_id=$class_session_id
    	LIMIT 1
    	";
    	
    	$row = Zend_Db_Table::getDefaultAdapter()->query($sql)->fetch();
    	return sizeof($row) ? $row['current_url'] : null;
    }

}
