#!/opt/lampp/bin/php
<?php

$data = $argv[1];

$data = json_decode($data, true);

$students = $data['students'];
$type_id  = $data['type_id'];

defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', dirname( dirname(__FILE__) ));
    
$config = parse_ini_file(APPLICATION_PATH.'/configs/application.ini');

$connection = mysqli_connect(
$config['resources.db.params.host'], 
$config['resources.db.params.username'], 
$config['resources.db.params.password'],
$config['resources.db.params.dbname']
) ;

foreach ( $students as $student )
{
	$values[] = "(
	$type_id, 
	$data[school_id], 
	$data[class_id], 
	$data[section_id],
	$student[student_id],
	0, 
	NOW(), 
	'Teacher', 
	$data[teacher_id]
	)";
}

$value = implode(',' , $values);

mysqli_query( $connection,
"INSERT INTO ctp_notification 
(notification_type_id, school_id,class_id,section_id,student_id,version,date_created,notify_by,notify_by_id)
VALUES
$value
") or die( mysqli_error( $connection) );

