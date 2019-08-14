<?php

/**
 * Description of Status
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Source_Content_Status 
{
   
    /**
     * 
     * @return array
     */
    public function getOptions(){
        return array(
            Smartling_Connector_Model_Content::CONTENT_STATUS_PROCESS   => 'In Progress',
            Smartling_Connector_Model_Content::CONTENT_STATUS_COMPLETED => 'Completed',            
        );
    }
}
