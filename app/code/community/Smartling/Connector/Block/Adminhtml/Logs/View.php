<?php

/**
 * Description of View
 *
 * @author Smartling
 */
class Smartling_Connector_Block_Adminhtml_Logs_View 
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    
    public function __construct() {
        
        $this->_controller = "adminhtml_logs_view";
        $this->_blockGroup = "connector";
        $this->_headerText = Mage::helper('connector')->__('Smartling Logs');
                
        
        
        $filename = Mage::getBaseDir('var') . DS . 'log' . DS . Mage::getStoreConfig('dev/smartling/log_file');
        
        if(file_exists($filename)) {
            $buttons = array();
            
            $buttons[] = 
                array(
                    'label' => Mage::helper('connector')->__('Download Log File'),
                    'onclick' => "setLocation('" . $this->getUrl('smartling/adminhtml_translator/downloadlog') . "')"
                );
        
            $buttons[] = 
                array(
                    'label' => Mage::helper('connector')->__('Remove Log File'),
                    'onclick' => "setLocation('" . $this->getUrl('smartling/adminhtml_translator/removelog') . "')"
                );

            for($i=0; $i < count($buttons); $i++) {
                Mage_Adminhtml_Block_Widget_Container::addButton('button_' . $i, $buttons[$i], $i, 100,  'header', 'header');
            }
        }
        
        parent::__construct();
        $this->_removeButton('add');
        
    }
    
    public function getCreateUrl()
    {
        return false;
    }
}
