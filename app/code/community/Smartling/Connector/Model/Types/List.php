<?php

/**
 * List content node
 *
 * @author Smartling
 */
class  Smartling_Connector_Model_Types_List extends Smartling_Connector_Model_Types_Abstract
{
    /**
     * 
     * @param array $value
     * @param array $attributes
     * @return false|void
     */
    public function setContent($values, $attributes) {
        
        if(!sizeof($values)) return false;
        
        $nodeName = 'select';
        $this->_contentInstance->pushHtmlContentNodeName('select/item');
        
        $contentNode = $this->_contentInstance->getXmlInstance()->createElement($nodeName);

        if (!empty($attributes)) {
            foreach ($attributes as $name => $value) {
                $contentNode->setAttribute($name, $value);
            }
        }
        
        // Set attibute values list
        foreach ($values as $optionId => $content) {
            $itemNode = $this->_contentInstance->getXmlInstance()->createElement('item');
            
            $contentTextNode = $this->_contentInstance->getXmlInstance()->createCDATASection($content);
            
            $itemNode->setAttribute('option_id', $optionId);
            
            $itemNode->appendChild($contentTextNode);
            $contentNode->appendChild($itemNode);
        }
        
        $this->_contentInstance->getСontentGroupNode()->appendChild($contentNode);

    }
}