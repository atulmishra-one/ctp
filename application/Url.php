<?php

class Url
{
	private $url;
	private $uri;
	private $controller;
	private $action;
	public  $controllerPath;
	private $object;

	public function __construct()
	{
		$this->url = isset( $_GET['route'] ) ? filter_var($_GET['route'], FILTER_SANITIZE_URL) : null;

		$this->url = rtrim($this->url, '/');
		$this->uri = explode('/', $this->url);
	}

	public function parse()
	{
		if ( !empty($this->uri[0]) )
		{
			$controller_name = ucwords( strtolower($this->uri[0])).'Controller';
			$this->controller = $controller_name;
		}
		else
		{
			$this->controller = 'IndexController';
		}

		if ( !empty( $this->uri[1]) )
		{
			$this->action = $this->uri[1].'Action';
		}
		else
		{
			$this->action = 'indexAction';
		}

		$this->controllerPath = $this->controllerPath.$this->controller.'.php';

		if (file_exists( $this->controllerPath) )
		{
			require_once $this->controllerPath;
			
			$class_name = str_replace('Controller', '', $this->controller );
			
			if ( class_exists( $class_name) ) {
				$this->object = new $class_name;

				if ( !method_exists($this->object, $this->action) )
				{
 					exit('Method Not found : ' . $this->action);
				}
			}
			else 
			{
				exit('Class Not found : ' . $this->controller);
			}
		}
		else {
			exit('Not Found : '. $this->controllerPath );
		}

		if ( is_array( $this->uri ) )
		{
			if ( sizeof( $this->uri) > 0 )
			{
				array_shift($this->uri);
			}
		}
	}

	public function run()
	{
		$this->parse();

		if (is_array( $this->uri) )
		{
			if (count( $this->uri) > 0)
			{
				$parms = $this->uri;
			}
			else 
			{
				$parms = array();
			}
		}
		else 
		{
			$parms = array();
		}
		

		if( is_object( $this->object ) ) {
			
			if( method_exists($this->object , $this->action ) )
			call_user_func_array( array( $this->object , $this->action), $parms);
		}
	}
}
