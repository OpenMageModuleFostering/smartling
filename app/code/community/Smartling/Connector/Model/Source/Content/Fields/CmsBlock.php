<?php

/**
 * Description of CmsBlock
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Source_Content_Fields_CmsBlock 
{
    
    /**
     * Returns list of all fields in cms/page table
     * 
     * @return array
     */
    public function toOptionArray(){
        $options = array();
        $table = 'cms_block';
        $fields = Mage::helper('connector')->getFields($table);
        
        for ($i = 0; $i < sizeof($fields); $i++){
            $options[] = array(
                'label' => $fields[$i],
                'value' => $fields[$i]
                );
        }
        return $options;
    }
}
