<?php

class Logfile extends Zend_Controller_Request_Http
{ 
	public function postAction()
	{
		$data = $this->getParam('data');
		
		$handler = fopen('logfile.txt', 'a+');
		
		fwrite( $handler, $data);
		
		fclose( $handler);
	}
}