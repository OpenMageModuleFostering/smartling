<?php

/**
 * Description of ContentFields
 * @deprecated since version 0.1.7
 * @author Smartling
 */
class Smartling_Connector_Block_Adminhtml_System_Config_ContentFields 
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    
    const CONFIG_PATH = 'smartling/';
    
    /**
     * stores config data
     * 
     * @var array 
     */
    protected $_values = null;
    
    /**
     * stores option values data from source model 
     * 
     * @var array 
     */
    protected $_options = array();
    
    /**
     * stores second part of path for config
     * 
     * @var string 
     */
    protected $_path = '';
    
    /**
     * 
     * Define template
     */
    protected function _construct() {
        $this->setTemplate('connector/adminhtml/system/config/contentfields.phtml');
        parent::_construct();
    }
    
    /**
     * 
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {       
        $this->_path = $this->_getConfigPath($element->getId());
        $this->_options = $element->getValues();  
        
        //get selected values from config data
        $this->_getCheckedValues();        
        
        $this->setNamePrefix($element->getName())
             ->setHtmlId($element->getHtmlId());
        return $this->_toHtml();
    }
    
    /**
     * 
     * @return array
     */
    public function getValues(){        
        return $this->_options;
    }
    
    /**
     * 
     * @param string $value
     * @return bool
     */
    public function isChecked($value){
        return in_array($value, $this->_values);
    }
    
    /**
     * Get checked values from config
     * 
     * @return string | null
     */
    protected function _getCheckedValues(){        
        $data = Mage::getStoreConfig(self::CONFIG_PATH . $this->_path); 
        if (is_null($data) || empty($data)){
            $data = '';
        }            
        $this->_values = explode(",", $data);   
        return $this->_values;        
    }
    
    /**
     * define config path
     * 
     * @return string
     */
    protected function _getConfigPath($elementId){
       $pathMap = array(
           'smartling_cms_page_settings_fields_to_translate'     => 'cms_page_settings/fields_to_translate',
           'smartling_cms_block_settings_fields_to_translate'    => 'cms_block_settings/fields_to_translate',
           'smartling_product_settings_attributes_to_translate'  => 'product_settings/attributes_to_translate',
           'smartling_category_settings_attributes_to_translate' => 'category_settings/attributes_to_translate'
       );
       if (isset($pathMap[$elementId])){
           return $pathMap[$elementId];
       }
       return '';
    }
}