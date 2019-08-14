<?php

class Smartling_Connector_Block_Adminhtml_Instance_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('entity_list_tabs');
        $this->setDestElementId('edit_form_container');
        $this->setTitle(Mage::helper('connector')->__('Smartling Entities'));
    }

    protected function _beforeToHtml() {
        $entityTypes = Mage::getModel('connector/source_content_types')->getList();

        foreach ($entityTypes as $entityType) {

            $entityTypeModel = Mage::getModel($entityType['model']);

            $typeCode = $entityTypeModel->getContentTypeCode();

            $blockOptions = array(
                'content_type_id' => $entityType['type_id']
            );
            
            if(!mageFindClassFile('Smartling_Connector_Block_Adminhtml_Instance_Tab_' . ucfirst($typeCode))) {
                continue;
            }
            
            $block = $this->getLayout()
                    ->createBlock('connector/adminhtml_instance_tab_' . $typeCode, 
                                  'content_name_' . $entityType['type_id'], 
                                  $blockOptions
            );

            if (!is_object($block)) {
                continue;
            }
            
            $this->addTab('type_' . $entityType['type_id'], array(
                'label' => Mage::helper('connector')->__($entityType['title']),
                'title' => Mage::helper('connector')->__($entityType['title']),
                'content' => $block->toHtml() . $block->getScript()
            ));
        }

        $this->_updateActiveTab();
        return parent::_beforeToHtml();
    }

    /**
     * Translate html content
     *
     * @param string $html
     * @return string
     */
    protected function _translateHtml($html) {
        Mage::getSingleton('core/translate_inline')->processResponseBody($html);
        return $html;
    }

    protected function _updateActiveTab() {
        $tabId = $this->getRequest()->getParam('tab');
        if ($tabId) {
            $tabId = preg_replace("#{$this->getId()}_#", '', $tabId);
            if ($tabId) {
                $this->setActiveTab($tabId);
            }
        }
    }

}
