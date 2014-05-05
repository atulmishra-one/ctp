<?php

class Zend_Controller_Action_Helper_Socket extends Zend_Controller_Action_Helper_Abstract
{
    
    
    public function write( $msg, $priority ){
        
       try{
            $config = new Api_Model_ConfigurationTable();
            
            if( $priority == 'high'){
               $port = $config->getHighPort();
            }
            elseif( $priority == 'low'){
                $port = $config->getLowPort();
            }
            
            $msg = Zend_Json::encode( $msg );
            
            $fp = @fsockopen( $config->getIpAddress() , $port, $errno, $errstr, 15);
            if( !$fp )
            {
                
            }
            else{
                if (!stream_set_timeout($fp, 1)){}
                #echo fgets($fp);
                fwrite($fp, "$msg\r\n");
            }
             
        }catch(exception $e){
            
        }
    }
    
    public function direct( $msg, $p ){
        
        return $this->write($msg, $p);
    }
}