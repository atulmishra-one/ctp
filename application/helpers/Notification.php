<?php

class Zend_Controller_Action_Helper_Notification extends Zend_Controller_Action_Helper_Abstract{
    
    var $assessmentNotificationTable;
    var $staffTable;
    var $studentTable;
    var $classTable;
    var $sectionTable;
    
    const CLASS_START          = 1;
    const CLASS_STOP           = 15;
    const CLASS_STARTED        = 2;
    const HOMEWORK             = 9;
    const DIARY                = 19;
    const NOTES                = 10;
    const ASSIGNMENT_SUBMITTED = 12;
    const STUDENT_LOGOUT       = 16;
    const EXIT_FROM_CLASS      = 17;
    const STUDENT_JOINED_CLASS = 18;
    const REMARK 	       = 11;
    

    public function __construct(){
        $this->assessmentNotificationTable = new Api_Model_AssessmentNotification();
        $this->staffTable = new Api_Model_Staff();
        $this->studentTable = new Api_Model_Student();
        $this->classTable = new Api_Model_GetClass();
        $this->sectionTable = new Api_Model_GetSection();
    }
    public function getPaperId( $type_id, $notify_id ){
        return ($type_id == 0xD)? (int)$this->assessmentNotificationTable->getById($notify_id)->paper_set_id : 0;
    }
    public function getAssessmentTeacherId($type_id, $notify_id ){
        return ( $type_id == 0xD )? (int)$this->assessmentNotificationTable->getById($notify_id)->teacher_id : 0;
    }
    
    public function getAssessmentSubjectId($type_id, $notify_id ){
        return ( $type_id == 0xD ) ? (int)$this->assessmentNotificationTable->getById($notify_id)->subject_id : 0;
    }
    
    public function getMessages( $type_id, $by, $by_id, $msgs, $school_id, $class_id, $section_id ) {
        //$msgs = $default;
        
        switch( $type_id ) {
            
            case self::DIARY: //DIARY
                if( $by == 'Teacher')
                $msgs = $this->getTeacherName($by_id). ' has sent you a message';
                elseif( $by == 'Student')
                $msgs = $this->getStudentName($by_id). ' of '.$this->getClassSection($school_id, $class_id, $section_id). ' has sent you a message';
            break;
            
            case 10:
                if( $by == 'Teacher')
                $msgs = $this->getTeacherName($by_id). ' has shared a note with you';
                elseif( $by == 'Student')
                 $msgs = $this->getStudentName($by_id).' of '.$this->getClassSection($school_id, $class_id, $section_id).' has shared a note with you';
            break;
            
            case 12:
                if( $by == 'Student')
                $msgs = $this->getStudentName($by_id).' of '.$this->getClassSection($school_id, $class_id, $section_id). ' has submitted homework';
            break;
            
            case 15:
                if( $by == 'Teacher')
                 $msgs = $this->getTeacherName($by_id). ' has stopped the class';
            break;
            
            case 16:
                if( $by == 'Student')
                $msgs = $this->getStudentName($by_id).' of '.$this->getClassSection($school_id, $class_id, $section_id). ' has logged out';
            break;
            
            case 17:
                if( $by == 'Student')
                $msgs = $this->getStudentName($by_id).' of '.$this->getClassSection($school_id, $class_id, $section_id). ' has left the class';
            break;
            
            case 18:
                if( $by == 'Student')
                $msgs = $this->getStudentName($by_id).' of '.$this->getClassSection($school_id, $class_id, $section_id). ' has joined the class';
            break;
            
            case self::CLASS_START:
                if( $by == 'Teacher')
                $msgs = $this->getTeacherName($by_id).' is about to start the class';
            break;
            
            case self::CLASS_STARTED:
                if( $by == 'Teacher')
                $msgs = $this->getTeacherName($by_id).' has started the class';
            break;
            
            case 11:
                if( $by == 'Teacher')
                $msgs = $this->getTeacherName($by_id). ' has given you remark on assignment';
            break;
            
            case 9:
                if( $by == 'Teacher')
                $msgs = $this->getTeacherName( $by_id).' has given you new homework';
            break;
            
            default:
             $msgs = $msgs;
            break;
            
        }// close switch;
        
        return $msgs;
    }
    
    public function getTeacherName($id){
        return (string)$this->staffTable->getInfoById($id, 'initial_name').(string)$this->staffTable->getInfoById($id, 'fname').' '.(string)$this->staffTable->getInfoById($id, 'lname');
    }
    
    public function getStudentName($id){
        return (string)$this->studentTable->getInfoById($id, 'fname').' '.(string)$this->studentTable->getInfoById($id, 'lname');
    }
    
    public function getClassSection($school_id, $class_id, $section_id){
        return (string)$this->classTable->getName($school_id, $class_id).'-'.(string)$this->sectionTable->getName($school_id, $section_id);
    }
    
    //public
    
}/** END OF CLASS **/



