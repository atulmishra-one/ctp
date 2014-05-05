<?php

class Attachment_Helper
{
    public static function makeAttachment($attachments, $path)
    {
        if( is_null($path) or empty($path) )
        {
            $path = '';
        }
        else
       {
            $path = $path;
        }
        $attachmentFile = array();
        
        if( is_array($attachments) )
        {
            foreach( $attachments as $attachment)
            {
                if ( preg_match('#/ctp/public/media/#i', $attachment) )
                {
                	$attachmentFile[] = $attachment;
                }
                else 
              {
                	$attachmentFile[] = $path.$attachment;
                }
            }
        }
        
        return serialize($attachmentFile);
    }
    
    public static function getAttachment($attachments)
    {
        $files = array();
        
        if( unserialize( $attachments) !== false && is_array( unserialize( $attachments)) )
	    {
	       foreach( unserialize( $attachments) as $file )
	       {
			 $files[] = $file;
	       }
        }
        
        return $files;
    }
}









