<?php

class Zend_Controller_Action_Helper_Rest extends Zend_Controller_Action_Helper_Abstract
{
    public function acceptGet()
    {
        if( $this->getRequest()->isGet() )
        {
            return true;
        }
    }
    
    public function acceptPost()
    {
        if( $this->getRequest()->isPost() )
        {
            return true;
        }
    }
}