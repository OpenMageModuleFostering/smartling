<?php

/**
 * Html content node
 *
 * @author Smartling
 */
class  Smartling_Connector_Model_Types_Htmlcontent extends Smartling_Connector_Model_Types_Abstract
{
    /**
     * 
     * @param string $value
     * @param array $attributes
     */
    public function setContent($value, $attributes) {
        
        $this->_contentInstance->setHtmlContent(
                                        'htmlcontent',
                                        $value, 
                                        array(
                                            'attribute' => $attributes['attribute'],
                                            'type' => $attributes['type']
                                        ));
    }
}