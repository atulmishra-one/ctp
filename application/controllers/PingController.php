<?php

class Ping
{
	public function getAction()
	{
		echo json_encode( array('live'=>1));
	}
}
