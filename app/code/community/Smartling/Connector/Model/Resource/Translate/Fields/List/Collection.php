<?php
class Smartling_Connector_Model_Resource_Translate_Fields_List_Collection 
    extends Varien_Data_Collection
{
    /**
     *
     * @var array - field list
     */
    protected $_fields = array();
    
    /**
     *
     * @var array - field description mapping
     */
    protected $_fieldsDesctriptionMapping = array();
    
    /**
     *
     * @var string - module code
     */
    protected $_code;
    
     /**
     *
     * @var string - Smartling type name 
     */
    protected $_typeName;
    
    /**
     * List of translatable fileds
     * @var array 
     */
    protected $_translatableFields = array();
    
    /**
     * 
     * @param string $table_name
     */
    function __construct($table_name = '') {

        if($table_name) {
            $this->_fields = Mage::helper('connector')->getFields($table_name);
        }
        
        $this->_fieldsDesctriptionMapping = array(
                        'title'     => Mage::helper('cms')->__('Page Title'),
                        'content_heading' => Mage::helper('cms')->__('Content Heading'),
                        'content' => Mage::helper('cms')->__('Content'),
                        'meta_keywords' => Mage::helper('cms')->__('Meta Keywords'),
                        'meta_description' => Mage::helper('cms')->__('Meta Description')
                );
        
        $entityTypeId = $this->getContentTypeId();
        $collection = Mage::getModel('connector/translate_fields')->getCollection()
                        ->addFieldToFilter('entity_type_id', array('eq' => $entityTypeId));
        
        foreach ($collection as $item) {
            $this->_translatableFields[] = $item->getFieldName();
        }
            
        
        $this->setItems();                
    }
    
    /**
     * Fill collection
     */
    protected function setItems() {
        
        $ignoreFields = Mage::getStoreConfig('connector/translate_attributes/cms_' . $this->_code);
        if($ignoreFields) {
            $systemFields = array_keys($ignoreFields);
        } else {
            $systemFields = array();
        }
        
        if(sizeof($this->_fields)) {
            foreach ($this->_fields as $item) {
              
              if(in_array($item, $systemFields)) {
                  continue;
              }
                
              $varienObject = new Varien_Object();
              $row = array('id' => $item, 
                           'text' => $this->getDescription($item),
                           'is_attached' => in_array($item, $this->_translatableFields),
                           'is_html_allowed_on_front' => in_array($item, $this->_translatableFields),
                  );
              
              $varienObject->setData($row);
              $this->addItem($varienObject);
            }
        }
    }
    
    /**
     * 
     * @param string $key
     * @return string
     */
    public function getDescription($key) {
        return $this->_fieldsDesctriptionMapping[$key];
    }
    
     /**
     * 
     * @return int
     */
    public function getContentTypeId() {
        $type = Mage::getModel('connector/content_types')->getTypeDetails($this->_typeName);
        return $type->getTypeId();
    }
    
    /**
     * 
     * @param string $field
     * @param string|int $value
     */
    public function includeWithValue($field, $value) {
        foreach ($this->getItems() as $id=>$item) {
            if($item->getData($field) != $value) {
                $this->removeItemByKey($id);
            }
        }
    }
}