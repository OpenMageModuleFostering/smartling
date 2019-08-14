<?php

/**
 * Description of CmsPage
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Source_Content_Fields_CmsPage 
{
    
    /**
     * Returns list of all fields in cms/page table
     * 
     * @return array
     */
    public function toOptionArray(){
        $options = array();
        $table = 'cms_page';
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
