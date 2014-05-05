<?php

class Api_Model_Notelinks extends Zend_Db_Table_Abstract
{
	protected $_name = 'ctp_notes_link';
    
    public function save($notes_id, $notes_word, $notes_link)
    {
        return $this->insert( array(
            'notes_id'      => $notes_id,
            'notes_word'    => $notes_word,
            'notes_link'    => $notes_link
        ));
    }
    
    public function saveBatch($notes_id, $data)
    {
    	if ( sizeof($data) )
    	{
    		foreach ( $data as $d )
    		{
    			$values[] = "($notes_id, '$d[word]', '$d[link]')";
    		}
    	
    		$value = implode(',', $values);
    	
    		$sql = "INSERT INTO $this->_name (notes_id, notes_word, notes_link)
    		VALUES
    		$value
    		";
    		//print $sql;
    		Zend_Db_Table::getDefaultAdapter()->query($sql);
    	}
    	
    }
    
    public function updateNotesLink($notes_id, $notes_word, $notes_link)
    {
        return $this->update( array(
            'notes_word'    => $notes_word,
            'notes_link'    => $notes_link
        ), array(
            'notes_id=?' => $notes_id
        ));
    }
    
    public function getByNotesId($nid)
    {
        return $this->fetchAll(
            $this->select()
            ->where('notes_id=?', $nid)
        );
    }
}