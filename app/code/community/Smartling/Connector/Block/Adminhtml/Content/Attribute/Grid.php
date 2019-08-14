<?php

/**
 * Description of Grid
 *
 * @author Smartling
 */
class Smartling_Connector_Block_Adminhtml_Content_Attribute_Grid 
    extends Mage_Adminhtml_Block_Catalog_Product_Attribute_Grid 
{
   
    protected $_contentTypeId = 5;
    
    protected $_contentType = Smartling_Connector_Model_Content_Attribute::CONTENT_TYPE;
    
    /**
     *
     * @var string 
     */
    protected $_controller;
    
    /**
     *
     * @var string 
     */
    protected $_action;
    
    /**
     * Massaction block name
     *
     * @var string
     */
    protected $_massactionBlockName = 'connector/adminhtml_grid_massaction';
    
    public function __construct() {
        parent::__construct();
        $this->setId('productContent'); 
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(false);
        $this->setSaveParametersInSession(false);
        $this->setVarNameFilter('content_filter');
        
        $this->_controller = $this->getRequest()->getControllerName();
        $this->_action = $this->getRequest()->getActionName();
        
        //if confirm action - disable pagination and limits
        if ($this->_action == 'confirm'){
            $this->setPagerVisibility(false);
            $this->setFilterVisibility(false);
        }
    } 
    
    /**
     * 
     * @return \Smartling_Connector_Block_Adminhtml_Content_Product_Grid
     */
    protected function _prepareCollection() {
        

        //for confirm action filter attributes by request
        if ( $this->_action == 'confirm' ) {
             $collection = Mage::getResourceModel('catalog/product_attribute_collection')
                    ->addVisibleFilter();
            $contentIds = $this->getRequest()->getParam('content');
            $collection->addAttributeToFilter('entity_id', array('in' => $contentIds));
            $this->setCollection($collection);
        } else {
            parent::_prepareCollection();
        }
        
        Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
        
        return $this;
    }
    
    /**
     * 
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_instance/view', array('_current'=>true));
    }
    
    /**
     * 
     * @param object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return false;
    }
    
    /**
     * 
     * @return \Smartling_Connector_Block_Adminhtml_Content_Grid
     */
    protected function _prepareMassaction() {
        // in confirm action don't show massaction block
         if ( $this->_controller == "adminhtml_instance" && $this->_action == 'confirm' ){
            return;
        }
        
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('content');
        
        $this->getMassactionBlock()->addItem('update', array(
            'label' => Mage::helper('connector')->__('Add To Smartling Translations'),
            'url'   => $this->getUrl('*/adminhtml_instance/confirm', array( '_current' => true )),
            'selected' => 'selected',
        ));
        
        Mage_Adminhtml_Block_Widget_Grid::_prepareMassaction();
        return $this;
    }
}