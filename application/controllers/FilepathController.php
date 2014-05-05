<?php
/**
 * Not in use
 */

include_once APPLICATION_PATH.'/models/Student.php';

class Filepath extends Zend_Controller_Request_Http
{ 
	private $output = array(
		'status' => 0
	);
	
	const MEDIA_URL = '/temp/public/media/';
	
	private $root;
	
	protected static function getStudentTable()
	{
		return new Api_Model_Student();
	}
	public function postAction()
	{
		try {
		$school_id 	= $this->getParam('school_id');
		$user_type 	= $this->getParam('user_type');
		$user_id   	= $this->getParam('user_id');
		$doc_type   = $this->getParam('doc_type');
		$this->root	= $_SERVER['DOCUMENT_ROOT'];
		
		$curDate = self::getStudentTable()->fetchRow(
		self::getStudentTable()->select()->setIntegrityCheck(false)
		->from('student', array('CURDATE() as ct') )
		->limit(1, 0) 
		);
		
		
		$curDate = date('Y-m-d', strtotime( $curDate->ct));
		
		if( file_exists( $this->root.self::MEDIA_URL.$school_id) )
		{
			if( file_exists( $this->root.self::MEDIA_URL.$school_id.'/'.$user_type) )
			{
				if( file_exists( $this->root.self::MEDIA_URL.$school_id.'/'.$user_type.'/'.$doc_type) )
				{
					if( file_exists( $this->root.self::MEDIA_URL.$school_id.'/'.$user_type.'/'.$doc_type.'/'.$curDate) )
					{
						if( file_exists( $this->root.self::MEDIA_URL.$school_id.'/'.$user_type.'/'.$doc_type.'/'.$curDate.'/'.$user_id) )
						{
						}
						else
						{
							mkdir($this->root.self::MEDIA_URL.$school_id.'/'.$user_type.'/'.$doc_type.'/'.$curDate.'/'.$user_id, 0777, true);
						}
					}
					else
					{
						mkdir($this->root.self::MEDIA_URL.$school_id.'/'.$user_type.'/'.$doc_type.'/'.$curDate, 0777, true);
						mkdir($this->root.self::MEDIA_URL.$school_id.'/'.$user_type.'/'.$doc_type.'/'.$curDate.'/'.$user_id, 0777, true);
					}
				}
				else
				{
					mkdir($this->root.self::MEDIA_URL.$school_id.'/'.$user_type.'/'.$doc_type, 0777, true);
					mkdir($this->root.self::MEDIA_URL.$school_id.'/'.$user_type.'/'.$doc_type.'/'.$curDate, 0777, true);
					mkdir($this->root.self::MEDIA_URL.$school_id.'/'.$user_type.'/'.$doc_type.'/'.$curDate.'/'.$user_id, 0777, true);
				}
			}
			else
			{
				mkdir($this->root.self::MEDIA_URL.$school_id.'/'.$user_type, 0777, true);
				mkdir($this->root.self::MEDIA_URL.$school_id.'/'.$user_type.'/'.$doc_type, 0777, true);
				mkdir($this->root.self::MEDIA_URL.$school_id.'/'.$user_type.'/'.$doc_type.'/'.$curDate, 0777, true);
				mkdir($this->root.self::MEDIA_URL.$school_id.'/'.$user_type.'/'.$doc_type.'/'.$curDate.'/'.$user_id, 0777, true);
			}
		}
		else 
		{
			mkdir($this->root.self::MEDIA_URL.$school_id, 0777, true);
			mkdir($this->root.self::MEDIA_URL.$school_id.'/'.$user_type, 0777, true);
			mkdir($this->root.self::MEDIA_URL.$school_id.'/'.$user_type.'/'.$doc_type, 0777, true);
			mkdir($this->root.self::MEDIA_URL.$school_id.'/'.$user_type.'/'.$doc_type.'/'.$curDate, 0777, true);
			mkdir($this->root.self::MEDIA_URL.$school_id.'/'.$user_type.'/'.$doc_type.'/'.$curDate.'/'.$user_id, 0777, true);
		}
		
		
		$this->output['status'] = 1;
		$this->output['contents'] = $this->root.self::MEDIA_URL.$school_id.'/'.$user_type.'/'.$doc_type.'/'.$curDate.'/'.$user_id;
		
		
		}
		catch (Exception $e)
		{
			$this->output['status'] = 0;
			$this->output['message'] = $e->getMessage();
		}
		
		$response = new Response();
		$response->getResponse()
		->setHttpResponseCode(200)
		->setHeader( 'Content-Type', 'application/json' )
		->appendBody( json_encode( $this->output ) );
	}
}