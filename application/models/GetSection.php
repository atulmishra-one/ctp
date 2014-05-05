<?php

class Api_Model_GetSection extends Zend_Db_Table_Abstract
{
 protected $_name = 'school_cce_group';
	
 public function getName($school_id, $group_id)
 {
  $sql = "
	select group_name FROM school_cce_group, master_section, year_section
	where
	year_section.section_id=$group_id
	and
	school_auto_id=$school_id
	and
	master_section.group_id=school_cce_group.group_id
	and
	master_section.id=year_section.section_id
	and
	school_cce_group.status='Active'
	limit 1
	";
   $row = Zend_Db_Table::getAdapter()->query($sql)->fetch();
   return ( sizeof( $row) ) ? $row['group_name'] : '';
 }
}
