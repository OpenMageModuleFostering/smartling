<?php

/**
 * Description of Grid
 *
 * @author Smartling
 */
class Smartling_Connector_Block_Adminhtml_Content_CmsBlock_Grid 
    extends Mage_Adminhtml_Block_Cms_Block_Grid
{
    
    protected $_contentType = Smartling_Connector_Model_Content_CmsBlock::CONTENT_TYPE;
    
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
    protected $_contentTypeId = 2;
    
    /**
     * Massaction block name
     *
     * @var string
     */
    protected $_massactionBlockName = 'connector/adminhtml_grid_massaction';
    
    public function __construct() {        
        parent::__construct();
        $this->setId('cmsBlockContent');    
        $this->setDefaultSort('block_id');
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
     * @return type
     */
    protected function _prepareColumns()
    {
        $baseUrl = $this->getUrl();
        
        // if confirm action - shows only title and id button
        if (($this->_controller == "adminhtml_instance") && ($this->_action == 'confirm')){
            
             $this->addColumn('block_id', array(
                'header'   => Mage::helper('cms')->__('Id'),
                'align'    => 'left',
                'index'    => 'block_id',
                'width'    => '10px',
                'filter'   => false,
                'sortable' => false, 
            ));

            $this->addColumn('title', array(
                'header'   => Mage::helper('cms')->__('Title'),
                'align'    => 'left',
                'index'    => 'title', 
                'filter'   => false,
                'sortable' => false,
            ));            
            
        } else {        
            $this->addColumn('block_id', array(
                'header'    => Mage::helper('cms')->__('Id'),
                'align'     => 'left',
                'index'     => 'block_id',
                'width'     => '10px',                
            ));

            $this->addColumn('title', array(
                'header'    => Mage::helper('cms')->__('Title'),
                'align'     => 'left',
                'index'     => 'title',                
            ));
            
            $this->addColumn('identifier', array(
                'header'    => Mage::helper('cms')->__('Identifier'),
                'align'     => 'left',
                'index'     => 'identifier'
            ));

            if (!Mage::app()->isSingleStoreMode()) {
                $this->addColumn('store_id', array(
                    'header'        => Mage::helper('cms')->__('Store View'),
                    'index'         => 'store_id',
                    'type'          => 'store',
                    'store_all'     => true,
                    'store_view'    => true,
                    'sortable'      => false,
                    'filter_condition_callback'
                                    => array($this, '_filterStoreCondition'),
                ));
            }

            $this->addColumn('is_active', array(
                'header'    => Mage::helper('cms')->__('Status'),
                'index'     => 'is_active',
                'type'      => 'options',
                'options'   => array(
                    0 => Mage::helper('cms')->__('Disabled'),
                    1 => Mage::helper('cms')->__('Enabled')
                ),
            ));
        }

        return Mage_Adminhtml_Block_Widget_Grid::_prepareColumns();
    }
    
    /**
     * 
     * @return string
     */
    public function getGridUrl()
    {        
        return $this->getUrl('*/adminhtml_translator_cmsBlock/grid', array('_current' => true));
    }
    
    /**
     * 
     * @return \Smartling_Connector_Block_Adminhtml_Content_Grid
     */
    protected function _prepareMassaction() {
        //if confirm action don't show massaction
        if ($this->_controller == "adminhtml_instance" && $this->_action == 'confirm'){
            return;
        }
        
        $this->setMassactionIdField('block_id');
        $this->getMassactionBlock()->setFormFieldName('content');
        
        $this->getMassactionBlock()->addItem('update', array(
            'label' => Mage::helper('connector')->__('Add To Smartling Translations'),
            'url'   => $this->getUrl('*/adminhtml_instance/confirm', array('_current' => true)),
            'selected' => 'selected',
        ));
        
        Mage_Adminhtml_Block_Widget_Grid::_prepareMassaction();
        return $this;
    }
    
    /**
     * 
     * @return \Mage_Adminhtml_Block_Cms_Block_Grid
     */
    protected function _prepareCollection() {
            
       /* @var $collection Mage_Cms_Model_Mysql4_Block_Collection */ 
       $collection = Mage::getModel('cms/block')->getCollection();
            
       $resource = Mage::getSingleton('core/resource');
       $storeGroupTableName = $resource->getTableName('core_store_group');
       $smartlingProjectsLocales = $resource->getTableName('smartling_projects_locales');

       $locales = $this->getRequest()->getParam('locales');

       $stores_ids = array();
       
       if(is_array($locales)) {
            foreach($locales as $projects) {
                foreach($projects as $store_id) {
                    $stores_ids[] = (int)$store_id;
                }
            }
       }

       $collection->getSelect()
               ->joinInner(
                   array('cbs' => $resource->getTableName('cms_block_store')),
                   "cbs.block_id = main_table.block_id", 
                   array('store_id'))
               ->joinInner(
                   array('csg' => $storeGroupTableName),
                   "csg.default_store_id = cbs.store_id",
                   array())
               ->joinLeft(
                   array('spl' => $smartlingProjectsLocales),
                   "csg.default_store_id = cbs.store_id",
                   array())
               ->where('cbs.store_id = 0 or (cbs.store_id = spl.store_id and spl.store_id in (?))', array('in' => $stores_ids))
               ->group('block_id');

       if ($this->_controller == "adminhtml_instance" && $this->_action == 'confirm') { // only for confirmation action
           $blocksIds = $this->getRequest()->getParam('content');
           $collection->getSelect()
             ->where('main_block.block_id in (?)', array('in' => $blocksIds));
       }
            
       $this->setCollection($collection);
        
       return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();     
    }
    
    /**
     * Row click url
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return false;       
    }
}
