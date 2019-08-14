<?php

class Smartling_Connector_Model_Content_Attributes_Builder 
    extends Varien_Object
{
    /**
     *
     * @var array
     */
    protected $_params = array();
    
    
    /**
     *
     * @var array
     */
    protected $_defaultStoreId = array();
    
    
            
    public function __construct() {
        $this->_params = array(
            'option_id' => 0,
            'store_id' => $this->_defaultStoreId, 
            'value' => 0
            );
    }

    public function setOptionId($value) {
        $this->_params['option_id'] = $value;
        return $this;
    }
    
    public function setStoreId($value) {
        $this->_params['store_id'] = $value;
        return $this;
    }
    
    public function setValue($value) {
        $this->_params['value'] = $value;
        return $this;
    }
    
   /**
   * Return all parameters
    * 
   * @return array
   */
  public function buildParameters() {
      return $this->_params;
  }
}