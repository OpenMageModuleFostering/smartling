<?php

/**
 * Description of Grid
 *
 * @author Smartling
 */
class Smartling_Connector_Block_Adminhtml_Content_Category_Grid 
    extends Mage_Adminhtml_Block_Widget_Grid
{
    
    /**
     *
     * @var string
     */
    protected $_contentType = Smartling_Connector_Model_Content_Category::CONTENT_TYPE;
    
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
     *
     * @var int
     */
    protected $_contentTypeId = 4;
    
    /**
     * Massaction block name
     *
     * @var string
     */
    protected $_massactionBlockName = 'connector/adminhtml_grid_massaction';
    
    public function __construct() {        
        parent::__construct();
        $this->setId('categoryContent');       
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
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
     * @return \Smartling_Connector_Block_Adminhtml_Content_Category_Grid
     */
    protected function _prepareCollection(){        
        $rootCategories = array(1);
        
        $collection = Mage::getModel('catalog/category')->getCollection()                                                
                                                ->addAttributeToSelect('name');
        
         if ($this->_action == 'confirm'){
             $categoriesId = $this->getRequest()->getParam('content');
             $collection->addAttributeToFilter('entity_id', array('in' => $categoriesId));
         } else {
             $collection->addAttributeToFilter('entity_id', array('nin' => $rootCategories))
                         ->addAttributeToSelect('title')
                         ->addAttributeToSelect('is_active')
                         ->addAttributeToSelect('include_in_menu')
                         ->addAttributeToSelect('display_mode')
                         ->setStoreId($this->getRequest()->getParam('store_group_id'));
         }
         
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    /**
     * set grid columns 
     */
    protected function _prepareColumns() {
        // if in confirm action show other fields
        if ( $this->_action == 'confirm' ){
            $this->addColumn('entity_id', array(
                'header'   => Mage::helper('connector')->__('Id'),
                'align'    => 'left',
                'index'    => 'entity_id',
                'width'    => '10px',
                'filter'   => false,
                'sortable' => false,
             ));

            $this->addColumn('name', array(
                'header'   => Mage::helper('connector')->__('Category Name'),
                'align'    => 'left',
                'index'    => 'name',
                'filter'   => false,
                'sortable' => false,
            ));
        } else {        
            $this->addColumn('entity_id', array(
                'header' => Mage::helper('connector')->__('Id'),
                'align'  => 'left',
                'index'  => 'entity_id',
                'width'  => '10px'
            ));

            $this->addColumn('name', array(
                'header' => Mage::helper('connector')->__('Category Name'),
                'align'  => 'left',
                'index'  => 'name',
            ));
            
            $this->addColumn('display_mode', array(
                'header' => Mage::helper('connector')->__('Display Mode'),
                'align'  => 'left',
                'index'  => 'display_mode',
            ));
        }
        parent::_prepareColumns();
    }
    
    /**
     * 
     * @return string
     */
    public function getGridUrl()
    {
        return parent::getGridUrl();
        //return $this->getUrl('*/adminhtml_translator_category/grid', array('_current' => true));
    }
    
    /**
     * 
     * @return \Smartling_Connector_Block_Adminhtml_Content_Grid
     */
    protected function _prepareMassaction() {
        // in confirm action don't show massaction block
        if ( $this->_action == 'confirm' ){
            return;
        }
        $action = $this->getUrl('*/*/confirm', array('_current' => true));
        
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('content');
        
        $this->getMassactionBlock()->setAction($action);
        $this->getMassactionBlock()->addItem('update', array(
            'label'    => Mage::helper('connector')->__('Add To Smartling Translations'),
            'url'      => $this->getUrl('*/adminhtml_instance/confirm', array('_current' => true)),
            'selected' => 'selected',
        ));
        
        //parent::_prepareMassaction();
        return $this;
    }
}
