<?php

/**
 * Description of Category
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Source_Content_Attributes_Category 
{
    
    /**
     * get category attributes collection
     * 
     * @return array
     */
    public function toOptionArray(){
        $options = array();       
        $attributes = $this->_getAttributesCollection();
        
        foreach ($attributes as $_attribute){
            $options[] = array(
                'label' => $_attribute->getFrontendLabel(),
                'value' => $_attribute->getId(),
                );
        }
        return $options;
    }
    
    /**
     * Returns category attributes collection
     * 
     * @return \Mage_Eav_Resource_Entity_Attribute_Collection
     */
    protected function _getAttributesCollection(){
        $allowedFields = array('text', 'textarea');
        $entityType = Mage::getModel('connector/content_category')->getEntityTypeId();
        $attributesCollection = Mage::getResourceModel('eav/entity_attribute_collection')
                                        ->setEntityTypeFilter($entityType)
                                        ->addFieldToFilter('frontend_input', array('in' => $allowedFields));                                        
        return $attributesCollection;
    }
}