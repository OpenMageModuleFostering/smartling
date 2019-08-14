<?php

/**
 * Simple content node
 *
 * @author Smartling
 */
abstract class  Smartling_Connector_Model_Types_Abstract
{
    /**
     *
     * @var \Smartling_Connector_Model_Types_General
     */
    protected $_contentInstance;
    
    /**
     * 
     * @param array $params
     */
    public function __construct($params = array()) {
        $this->_contentInstance = $params['contentInstance'];
    }
    
    /**
     * 
     * @param string $value
     * @param array $attributes
     */
    abstract public function setContent($value, $attributes);
    
    /**
     * 
     * @return \Smartling_Connector_Model_Types_General
     */
    public function getInstance() {
        return $this->_contentInstance;
    }
}