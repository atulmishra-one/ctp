<?php

class Api_Model_ClassSession extends Zend_Db_Table_Abstract
{
	protected $_name = 'ctp_class_session';

	public function save(array $data)
	{
		$id = $this->insert( array(
			'teacher_id'            => $data['teacher_id'],
			'school_id' 		=> $data['school_id'],
			'class_id'		=> $data['class_id'],
			'section_id'		=> $data['section_id'],
			'subject_id'		=> $data['subject_id'],
			'start_time'		=> new Zend_Db_Expr('NOW()')
		) );

		return $id;
	}

	public function hasStarted( array $data)
	{
		/*$row= $this->fetchRow(
			$this->select()
			->where('teacher_id=?', $data['teacher_id'])
			->where('school_id=?',  $data['school_id'])
			->where('class_id=?',   $data['class_id'])
			->where('section_id=?', $data['section_id'])
			->where('subject_id=?', $data['subject_id'])
			->where('start_time > ?', '0000-00-00 00:00:00')
			->where('end_time= ?', '0000-00-00 00:00:00')
				
			);

			return ( count($row) )? $row->id: 0;
			*/
		$sql = "
		SELECT id FROM $this->_name WHERE
		teacher_id=$data[teacher_id]
		AND
		school_id=$data[school_id]
		AND
		class_id=$data[class_id]
		AND
		section_id=$data[section_id]
		AND
		subject_id=$data[subject_id]
		AND
		start_time > '0000-00-00 00:00:00'
		AND
		end_time = '0000-00-00 00:00:00'
		LIMIT 1
		";
		//print $sql;
		$row = Zend_Db_Table::getDefaultAdapter()->query($sql)->fetch();
		return sizeof($row) ? $row['id'] : 0;
	}

	public function isCurrentTeacher(array $data)
	{
		/*$row= $this->fetchRow(
			$this->select()
			->where('school_id=?',  $data['school_id'])
			->where('teacher_id=?', $data['teacher_id'])
			->where('start_time > ?', '0000-00-00 00:00:00')
			->where('end_time= ?', '0000-00-00 00:00:00')
				
			);

			return ( count($row) )? $row->id: 0;
			*/
		$sql = "
		SELECT id FROM $this->_name WHERE
		school_id=$data[school_id]
		AND
		teacher_id=$data[teacher_id]
		AND
		start_time > '0000-00-00 00:00:00'
		AND
		end_time = '0000-00-00 00:00:00'
		LIMIT 1
		";
		$row = Zend_Db_Table::getDefaultAdapter()->query($sql)->fetch();
		return sizeof($row) ? $row['id'] : 0;
	}

	public function isCurrentClassSection(array $data)
	{
		$row= $this->fetchRow(
		$this->select()
		->where('school_id=?',  $data['school_id'])
		->where('class_id=?',   $data['class_id'])
		->where('section_id=?', $data['section_id'])
		->where('start_time > ?', '0000-00-00 00:00:00')
		->where('end_time= ?', '0000-00-00 00:00:00')
			
		);

		return ( count($row) )? $row->id: 0;
	}

	public function getCurrentClassSession(array $data)
	{
		/*$row= $this->fetchRow(
			$this->select()
			->where('school_id=?',  $data['school_id'])
			->where('class_id=?',   $data['class_id'])
			->where('section_id=?', $data['section_id'])
			->where('start_time > ?', '0000-00-00 00:00:00')
			->where('end_time= ?', '0000-00-00 00:00:00')
				
			);

			return ( count($row) )? $row: array();
			*/
		$sql = "
		SELECT id, teacher_id, subject_id FROM $this->_name
		WHERE
		school_id=$data[school_id]
		AND
		class_id=$data[class_id]
		AND
		section_id=$data[section_id]
		AND
		start_time > '0000-00-00 00:00:00'
		and
		end_time = '0000-00-00 00:00:00'
		LIMIT 1
		";
		//print $sql;

		return Zend_Db_Table::getDefaultAdapter()->query($sql)->fetch();
	}
	
	public function getCurrentSession(array $data)
	{
		$row= $this->fetchRow(
			$this->select()
			->where('school_id=?',  $data['school_id'])
			->where('class_id=?',   $data['class_id'])
			->where('section_id=?', $data['section_id'])
			->where('start_time > ?', '0000-00-00 00:00:00')
			->where('end_time= ?', '0000-00-00 00:00:00')
				
			);

			return ( count($row) )? $row: array();
	}

	public function isCurrentClassSectionSubject(array $data)
	{
		/*$row= $this->fetchRow(
			$this->select()
			->where('school_id=?',  $data['school_id'])
			->where('class_id=?',   $data['class_id'])
			->where('section_id=?', $data['section_id'])
			->where('subject_id=?', $data['subject_id'])
			->where('start_time > ?', '0000-00-00 00:00:00')
			->where('end_time= ?', '0000-00-00 00:00:00')
				
			);

			return ( count($row) )? $row->id: 0;
			*/
		$sql = "
		SELECT id FROM $this->_name WHERE
		school_id=$data[school_id]
		AND
		class_id=$data[class_id]
		AND
		section_id=$data[section_id]
		AND
		subject_id=$data[subject_id]
		AND
		start_time > '0000-00-00 00:00:00'
		AND
		end_time = '0000-00-00 00:00:00'
		LIMIT 1
		";
		$row = Zend_Db_Table::getDefaultAdapter()->query($sql)->fetch();
		return sizeof($row) ? $row['id'] : 0;
	}

	public function getCurrentClassSectionTeacher(array $data)
	{
		$row= $this->fetchRow(
		$this->select()
		->where('school_id=?',  $data['school_id'])
		->where('class_id=?',   $data['class_id'])
		->where('section_id=?', $data['section_id'])
		->where('teacher_id=?', $data['teacher_id'])
		->where('start_time > ?', '0000-00-00 00:00:00')
		->where('end_time= ?', '0000-00-00 00:00:00')
		->order('start_time desc')
			
		);

		return ( sizeof($row) )? $row: array();
	}

	public function getById($id)
	{
		$row= $this->fetchRow(
		$this->select()
		->where('id=?',   $id)
		->where('start_time > ?', '0000-00-00 00:00:00')
		->where('end_time>= ?', '0000-00-00 00:00:00')
		);

		return ( count($row) )? $row: array();
	}
	
	public function getClassSessionValuesById( $id)
	{
		$sql = "
		SELECT school_id, class_id, section_id, teacher_id FROM $this->_name WHERE
		id=$id
		AND
		start_time > '0000-00-00 00:00:00'
		AND
		end_time = '0000-00-00 00:00:00'
		LIMIT 1
		";
		
		return Zend_Db_Table::getDefaultAdapter()->query($sql)->fetch();
	}

	public function updateEndTime($id)
	{
		return $this->update( array(
			'end_time' => new Zend_Db_Expr('NOW()')
		) , array(
			'id=?' => $id
		));
	}

	public function isRunning($id)
	{
		/*$row= $this->fetchRow(
		$this->select()
		->where('id=?',   $id)
		->where('start_time > ?', '0000-00-00 00:00:00')
		->where('end_time= ?', '0000-00-00 00:00:00')
			
		);

		return ( count($row) )? true : false;
		*/
		$sql= "
		SELECT id FROM $this->_name WHERE
		id=$id
		AND
		start_time > '0000-00-00 00:00:00'
		AND
		end_time = '0000-00-00 00:00:00'
		LIMIT 1
		";
		$row = Zend_Db_Table::getDefaultAdapter()->query($sql)->fetch();
		
		//return sizeof($row) ? true : false;
		return sizeof($row) ? true : false;
	}
	
	public function isRunningExtra($id)
	{
		/*$row= $this->fetchRow(
		$this->select()
		->where('id=?',   $id)
		->where('start_time > ?', '0000-00-00 00:00:00')
		->where('end_time= ?', '0000-00-00 00:00:00')
			
		);

		return ( count($row) )? true : false;
		*/
		$sql= "
		SELECT id FROM $this->_name WHERE
		id=$id
		AND
		start_time > '0000-00-00 00:00:00'
		AND
		end_time = '0000-00-00 00:00:00'
		LIMIT 1
		";
		$row = Zend_Db_Table::getDefaultAdapter()->query($sql)->fetch();
		
		//return sizeof($row) ? true : false;
		return $row;
	}

}