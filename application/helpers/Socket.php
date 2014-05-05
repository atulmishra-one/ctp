<?php

class Socket_Helper
{

    public static function write( $msg ){
        
       try{
            $msg = json_encode( $msg);
	   		$msg = str_replace(' ', '',$msg);
           if( false === exec("/opt/lampp/bin/php /opt/lampp/htdocs/schoolerp/soap/ctp/php-amqplib/run/amqp_publisher.php '$msg'") ) 
           {
		      	throw new exception('System call error');
           }  
          
        }catch(exception $e){
        }
    }
    
}
