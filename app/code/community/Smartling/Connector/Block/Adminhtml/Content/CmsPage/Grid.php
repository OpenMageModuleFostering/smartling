<?php

/**
 * Description of Grid
 *
 * @author Smartling
 */
class Smartling_Connector_Block_Adminhtml_Content_CmsPage_Grid 
    extends Mage_Adminhtml_Block_Cms_Page_Grid    
{
    
    protected $_contentTypeId = 1;
    
    /**
     *
     * @var string 
     */
    protected $_contentType = Smartling_Connector_Model_Content_CmsPage::CONTENT_TYPE;
    
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
        $this->setId('cmsPageContent');
        $this->setDefaultSort('page_id');
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
    protected function _prepareColumns() {
        $baseUrl = $this->getUrl();
        
        //if confirm action shows only two fields
        if ( ($this->_controller == "adminhtml_instance") && ($this->_action == 'confirm') ){
            $this->addColumn('page_id', array(
                'header'    => Mage::helper('cms')->__('Id'),
                'align'     => 'left',
                'index'     => 'page_id',
                'width'     => '25px',
                'filter'    => false,
                'sortable'  => false,
            ));

            $this->addColumn('title', array(
                'header'    => Mage::helper('cms')->__('Title'),
                'align'     => 'left',
                'index'     => 'title',
                'filter'    => false,
                'sortable'  => false,
            ));
        } else {        
            $this->addColumn('page_id', array(
                'header'    => Mage::helper('cms')->__('Id'),
                'align'     => 'left',
                'index'     => 'page_id',
                'width'     => '25px'
            ));

            $this->addColumn('title', array(
                'header'    => Mage::helper('cms')->__('Title'),
                'align'     => 'left',
                'index'     => 'title',
            ));
            $this->addColumn('identifier', array(
                'header'    => Mage::helper('cms')->__('URL Key'),
                'align'     => 'left',
                'index'     => 'identifier'
            ));
            
            $this->addColumn('root_template', array(
                'header'    => Mage::helper('cms')->__('Layout'),
                'index'     => 'root_template',
                'type'      => 'options',
                'options'   => Mage::getSingleton('page/source_layout')->getOptions(),
            ));

            /**
             * Check is single store mode
             */
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
                'options'   => Mage::getSingleton('cms/page')->getAvailableStatuses()
            ));  
        }
        
        return Mage_Adminhtml_Block_Widget_Grid::_prepareColumns();
    }
    
    /**
     * 
     * @return \Smartling_Connector_Block_Adminhtml_Content_Grid
     */
    protected function _prepareMassaction() {
        //if confirm action don't show massaction
        if ($this->_action == 'confirm'){
            return;
        }
                
        $this->setMassactionIdField('page_id');
        $this->getMassactionBlock()->setFormFieldName('content');
        
        $this->getMassactionBlock()->addItem('update', array(
            'label' => Mage::helper('connector')->__('Add To Smartling Translations'),
            'url'   => $this->getUrl('*/adminhtml_instance/confirm', array ('_current' => true) ),
            'selected' => 'selected',
        ));
        
        Mage_Adminhtml_Block_Widget_Grid::_prepareMassaction();
        return $this;
    }
    
    /**
     * 
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_translator_cmsPage/grid', array('_current' => true));
    }
    
    /**
     * 
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection() {
        
        /* @var $collection Mage_Cms_Model_Mysql4_Page_Collection */
       $collection = Mage::getModel('cms/page')->getCollection();           

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
                   array('cps' => $resource->getTableName('cms_page_store')),
                   "cps.page_id = main_table.page_id", 
                   array('store_id'))
               ->joinInner(
                   array('csg' => $storeGroupTableName),
                   "csg.default_store_id = cps.store_id",
                   array())
               ->joinLeft(
                   array('spl' => $smartlingProjectsLocales),
                   "csg.default_store_id = cps.store_id",
                   array())
               ->where('cps.store_id = 0 or (cps.store_id = spl.store_id and spl.store_id in (?))', array('in' => $stores_ids))
               ->group('page_id');

       if ($this->_controller == "adminhtml_instance" && $this->_action == 'confirm') { // only for confirmation action
           $pageIds = $this->getRequest()->getParam('content');
           $collection->getSelect()
             ->where('main_page.page_id in (?)', array('in' => $pageIds));
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
        return parent::getGridUrl();
    }
    
}
