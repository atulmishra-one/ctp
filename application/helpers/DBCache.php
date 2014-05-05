<?php

class DBCache
{ 
	public $oPEnabled 	= false;
	public $oPcache 	= null;
	
	public function __construct()
	{
		if ( class_exists('Memcache') )
		{
			$this->oPcache = new Memcache();
			$this->oPEnabled = true;
			
			if (! $this->oPcache->connect('127.0.0.1', 11211) ) {
				$this->oPcache = null;
				$this->oPEnabled = false;
			}
		}
	}
	
	public function setData($key, $value)
	{
		return $this->oPcache->set($key, $value, MEMCACHE_COMPRESSED, 1200);
	}
	
	public function getData($key)
	{
		$vData = $this->oPcache->get($key);
		return false === $vData ? null : $vData;
	}
	
	public function deleteData( $key)
	{
		return $this->oPcache->delete($key);
	}
}