<?php

class Zend_Controller_Action_Helper_Date extends Zend_Controller_Action_Helper_Abstract
{
    public function targetDate($date)
    {
        if( is_null($date) or empty($date) )
        {
            return '0000-00-00 00:00:00';
        }
        else
        {
            return date('Y-m-d h:i:s', strtotime($date, time()));
        }
    }
}