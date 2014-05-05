<?php

class Socket_Helper
{

    const CLASS_START                   = 1;
    const CLASS_STARTED                 = 2;
    const LOCK                          = 3;
    const UNLOCK                        = 4;
    const BLACKIN                       = 5;
    const BLACKOUT                      = 6;
    const MUTE                          = 7;
    const UNMUTE                        = 8;
    const ASSIGNMENT                    = 9;
    const NOTES                         = 10;
    const REMARK                        = 11;
    const ASSIGNMENTSUBMITTED           = 12;
    const ASSESSMENT                    = 13;
    const ASSESSMENT_STOPED             = 14;
    const CLASS_STOP                    = 15;
    const STUDENT_LOGOUT                = 16;
    const EXITFROMCLASS                 = 17;
    const STUDENT_JOINED_CLASS          = 18;
    const DIARY                         = 19;
    const FULLMODEIN                    = 20;
    const FULLMODEOUT                   = 21;
    const ASSESSMENT_FULLMODEIN         = 22;
    const ASSESSMENT_FULLMODEOUT        = 23;
    const ASSESSMENT_SAVED              = 24;
    
    
    
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
