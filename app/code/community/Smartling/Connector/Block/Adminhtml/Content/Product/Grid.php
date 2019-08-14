<?php

/**
 * Description of Grid
 *
 * @author Smartling
 */
class Smartling_Connector_Block_Adminhtml_Content_Product_Grid 
    extends Mage_Adminhtml_Block_Catalog_Product_Grid
{
   
    protected $_contentTypeId = 3;
    
    protected $_contentType = Smartling_Connector_Model_Content_Product::CONTENT_TYPE;
    
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
        $store = $this->_getStore();
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')            
            ->addAttributeToSelect('type_id');
        
        //for confirm action filter products by request
        if ( $this->_action == 'confirm' ){
            $productIds = $this->getRequest()->getParam('content');
            $collection->addAttributeToFilter('entity_id', array('in' => $productIds));
        } else {
            
            $website_id = $this->getRequest()->getParam('website_id');
            $collection->addWebsiteFilter($website_id);
        
            if ($store->getId()) {
                //$collection->setStoreId($store->getId());
                $adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
                                                
                $collection->addStoreFilter($store);
                $collection->joinAttribute(
                    'name',
                    'catalog_product/name',
                    'entity_id',
                    null,
                    'inner',
                    $adminStore
                );
                $collection->joinAttribute(
                    'custom_name',
                    'catalog_product/name',
                    'entity_id',
                    null,
                    'inner',
                    $store->getId()
                );
                $collection->joinAttribute(
                    'status',
                    'catalog_product/status',
                    'entity_id',
                    null,
                    'inner',
                    $store->getId()
                );
                $collection->joinAttribute(
                    'visibility',
                    'catalog_product/visibility',
                    'entity_id',
                    null,
                    'inner',
                    $store->getId()
                );           
            }
            else {
                $collection->addAttributeToSelect('price');
                $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
                $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
            }
        }
        
        $this->setCollection($collection);

        Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
        $this->getCollection()->addWebsiteNamesToResult();
        
        return $this;
    }
    
    /**
     * 
     * @return \Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns(){
        // if not confirm action show other fields
        if (($this->_controller == "adminhtml_instance") && ($this->_action == 'confirm') ){
            $this->addColumn('entity_id',
                array(
                    'header'   => Mage::helper('catalog')->__('ID'),
                    'width'    => '50px',
                    'type'     => 'number',
                    'index'    => 'entity_id',
                    'filter'   => false,
                    'sortable' => false,
            ));
            $this->addColumn('name',
                array(
                    'header'=> Mage::helper('catalog')->__('Name'),
                    'index' => 'name',
                    'filter'   => false,
                    'sortable' => false,
            ));           
        } else {
            $this->addColumn('entity_id',
                array(
                    'header'=> Mage::helper('catalog')->__('ID'),
                    'width' => '50px',
                    'type'  => 'number',
                    'index' => 'entity_id',
            ));
            $this->addColumn('name',
                array(
                    'header'=> Mage::helper('catalog')->__('Name'),
                    'index' => 'name',
            ));
        
            $store = $this->_getStore();
            if ($store->getId()) {
                $this->addColumn('custom_name',
                    array(
                        'header'=> Mage::helper('catalog')->__('Name in %s', $store->getName()),
                        'index' => 'custom_name',
                ));
            }

            $this->addColumn('type',
                array(
                    'header'=> Mage::helper('catalog')->__('Type'),
                    'width' => '60px',
                    'index' => 'type_id',
                    'type'  => 'options',
                    'options' => Mage::getSingleton('catalog/product_type')->getOptionArray(),
            ));

            $this->addColumn('sku',
                array(
                    'header'=> Mage::helper('catalog')->__('SKU'),
                    'width' => '80px',
                    'index' => 'sku',
            ));

            $store = $this->_getStore();

            $this->addColumn('visibility',
                array(
                    'header'=> Mage::helper('catalog')->__('Visibility'),
                    'width' => '70px',
                    'index' => 'visibility',
                    'type'  => 'options',
                    'options' => Mage::getModel('catalog/product_visibility')->getOptionArray(),
            ));

            $this->addColumn('status',
                array(
                    'header'=> Mage::helper('catalog')->__('Status'),
                    'width' => '70px',
                    'index' => 'status',
                    'type'  => 'options',
                    'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
            ));

            if (!Mage::app()->isSingleStoreMode()) {
                $this->addColumn('websites',
                    array(
                        'header'=> Mage::helper('catalog')->__('Websites'),
                        'width' => '100px',
                        'sortable'  => false,
                        'index'     => 'websites',
                        'type'      => 'options',
                        'options'   => Mage::getModel('core/website')->getCollection()->toOptionHash(),
                ));
            }
        }
        return Mage_Adminhtml_Block_Widget_Grid::_prepareColumns();
    }
    
    /**
     * 
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return string
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            if ($column->getId() == 'websites') {
                $this->getCollection()->joinField('websites',
                    'catalog/product_website',
                    'website_id',
                    'product_id=entity_id',
                    null,
                    'left');
            }
        }
        return Mage_Adminhtml_Block_Widget_Grid::_addColumnFilterToCollection($column);
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
        return $this->getUrl('adminhtml/catalog_product/edit', array(
            'store'=>$this->getRequest()->getParam('store'),
            'id'=>$row->getId())
        );
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