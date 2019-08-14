<?php

/**
 * Description of Grid
 *
 * @author Smartling
 */
class Smartling_Connector_Block_Adminhtml_Content_Localization_Grid 
    extends Mage_Adminhtml_Block_Widget_Grid    
{
    
    protected $_contentTypeId = 6;
    
    /**
     *
     * @var string 
     */
    protected $_contentType = Smartling_Connector_Model_Content_Localization::CONTENT_TYPE;
    
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
        $this->setId('localizationContent');
        $this->setDefaultSort('dir_name');
        $this->setDefaultDir('ASC');
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
            $this->addColumn('id', array(
                'header'    => Mage::helper('connector')->__('Id'),
                'align'     => 'left',
                'index'     => 'id',
                'width'     => '25px',
                'filter'    => false,
                'sortable'  => false,
            ));

            $this->addColumn('file_path', array(
                'header'    => Mage::helper('connector')->__('File path'),
                'align'     => 'left',
                'index'     => 'file_path',
                'filter'    => false,
                'sortable'  => false,
            ));
        } else {        
            $this->addColumn('id', array(
                'header'    => Mage::helper('connector')->__('Id'),
                'align'     => 'left',
                'index'     => 'id',
                'width'     => '25px'
            ));

            $this->addColumn('dir_name', array(
                'header'    => Mage::helper('connector')->__('Localization dirrectory'),
                'align'     => 'left',
                'index'     => 'dir_name',
                'width'     => '100px'
            ));
            
            $this->addColumn('file_path', array(
                'header'    => Mage::helper('connector')->__('Path'),
                'align'     => 'left',
                'index'     => 'file_path',
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
                
        $this->setMassactionIdField('id');
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
        return $this->getUrl('*/adminhtml_translator_localization/grid', array('_current' => true));
    }
    
    /**
     * 
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection() {
        
        /* @var $collection Mage_Cms_Model_Mysql4_Page_Collection */
       $collection = Mage::getModel('connector/localization_files_index')->getCollection();           

       $resource = Mage::getSingleton('core/resource');
       $storeViewTableName = $resource->getTableName('core_store');
       $storeGroupTableName = $resource->getTableName('core_store_group');

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
               ->joinInner(array('cs' => $storeViewTableName),
                                       'cs.store_localization_dir = main_table.dir_name',
                                       array())
               ->joinInner(array('csg' => $storeGroupTableName),
                                       'cs.group_id = csg.default_store_id',
                                       array())
               ->joinInner(array('cs2' => $storeViewTableName),
                                       'csg.group_id = cs2.group_id',
                                       array())
               ->where('cs2.store_id in (?)', array('in' => $stores_ids))
               ->group('main_table.id');

       if ($this->_controller == "adminhtml_instance" && $this->_action == 'confirm') { // only for confirmation action
           $pageIds = $this->getRequest()->getParam('content');
           $collection->getSelect()
             ->where('main_page.id in (?)', array('in' => $pageIds));
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
