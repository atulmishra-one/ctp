<?php

include_once APPLICATION_PATH.'/models/ConfigurationTable.php';

class Configuration extends Zend_Controller_Request_Http
{ 
	
	protected static function getConfigTable()
	{
		return new Api_Model_ConfigurationTable();
	}
	
	public function getAction()
	{
		$res = self::getConfigTable()->fetchAll()->toArray();
		
		$xml = '<?xml version=\'1.0\' encoding=\'utf-8\'?>
		<root>';
		
		foreach ( $res as $r )
		{
			$xml .= "
			<list>
				<name>$r[tag]</name>
				<flag>$r[status]</flag>
				<value>$r[value]</value>
			</list>
			";
		}
		
		$output = $xml.'</root>';
		
		$response = new Response();
		$response->getResponse()
		->setHttpResponseCode(200)
		->setHeader( 'Content-Type', 'text/xml' )
		->appendBody( $output);
	}
}