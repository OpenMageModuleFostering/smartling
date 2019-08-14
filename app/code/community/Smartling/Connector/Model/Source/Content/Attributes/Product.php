<?php

/**
 * Description of Product
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Source_Content_Attributes_Product 
{
    
    /**
     * List of product attributes 
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
     * Returns product attributes collection
     * 
     * @return Mage_Catalog_Resource_Product_Attribute_Collection
     */
    protected function _getAttributesCollection(){
        $allowedFields = array('text', 'textarea');
        $collection = Mage::getResourceModel('catalog/product_attribute_collection')                                    
                                    ->addFieldToFilter('frontend_label', array('neq' => ''))
                                    ->addFieldToFilter('frontend_input', array('in' => $allowedFields));
        return $collection;
    }
}