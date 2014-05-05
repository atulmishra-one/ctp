<?php

class Response
{

	public $httpCode;
	public $setHeader;
	public $appendBody;
	public $getResponse;

	public function getResponse( $response = false)
	{
		$this->getResponse = $response;
		return $this;
	}

	public function setHttpResponseCode( $code)
	{
		//$this->httpCode = header("HTTP/1.1 $code");
		return $this;
	}

	public function setHeader( $type = 'Content-Type', $header)
	{
		$this->setHeader = header("Content-Type: $header");
		return $this;
	}

	public function appendBody( $body )
	{
		
		$this->appendBody = $body;
		echo $body;
		ob_flush();
		return $this;
	}


}
