<?php

/**
 * Description of Types
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Source_Content_Types 
{
    
    /**
     * 
     * @return array
     */
    public function getOptions(){
        $typesCollection = $this->_getTypesCollection();
        $options = array();
        if ($typesCollection->getSize()){
            foreach ($typesCollection as $_type){
                $options[$_type->getTypeId()] = $_type->getTypeName();
            }
        }
        return $options;
    }
    
    /**
     * 
     * @return array
     */
    public function getList(){
        $typesCollection = $this->_getTypesCollection();
        $options = array();
        if ($typesCollection->getSize()){
            foreach ($typesCollection as $_type){
                $options[$_type->getTypeId()] = $_type->getData();
            }
        }
        return $options;
    }
    
    /**
     * 
     * @return array
     */
    public function toOptionArray(){
        $typesCollection = $this->_getTypesCollection();
        $options = array();
        if ($typesCollection->getSize() > 0){
            foreach ($typesCollection as $_type){
                $options[] = array(
                    'value' => $_type->getTypeId(),
                    'label' => $_type->getTitle(),
                );
            }
        }
        return $options;
    }
    
    /**
     * 
     * @return \Smartling_Connector_Model_Resource_Types_Collection
     */
    protected function _getTypesCollection(){
        return Mage::getModel('connector/content_types')->getCollection();
    }
    
}