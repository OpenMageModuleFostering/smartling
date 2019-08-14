<?php

/**
 * Description of Grid
 *
 * @author Smartling
 */
class Smartling_Connector_Block_Adminhtml_Content_Grid 
    extends Mage_Adminhtml_Block_Widget_Grid
{
    
    /**
     *
     * @var int | null
     */
    protected $_contentTypeId = null;
    
    /**
     *
     * @var string | null
     */
    protected $_contentType = null;
    
    public function __construct() {        
        parent::__construct();
        $this->setId('content'); 
        $this->setDefaultSort('content_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        $this->setVarNameFilter('content_filter');
        if (!is_null($this->_contentType)){
            $this->_contentTypeId = Mage::helper('connector')
                                            ->findTypeIdByTypeName($this->_contentType);
        }
    }
    
    /**
     * 
     * prepare collection for grid
     */
    protected function _prepareCollection() {
        
        $resource = Mage::getSingleton('core/resource');
        
        $collection = Mage::getModel('connector/content')->getCollection();
                               
        if (!is_null($this->_contentTypeId) && $this->_contentTypeId !== 0){
            $collection->addFieldToFilter('type', array('eq' => $this->_contentTypeId));
        } else {
            $collection->getSelect()->joinLeft(
                    array('content_types' => 'smartling_content_types'),
                    'type = content_types.type_id',
                    array('type_name', 'type_id')
                    );
        }  
        $collection->getSelect()->joinLeft(
                                    array('user' => 'admin_user'),
                                    'submitter = user.user_id',
                                    array('username')
                                    );
        
        $collection->getSelect()
                   ->joinInner(array('p' => $resource->getTableName('connector/projects')),
                                           'main_table.project_id = p.id',
                                           array('project_name' => 'name')
                                               )
                   ->joinInner(array('pl' => $resource->getTableName('connector/projects_locales')),
                                           'main_table.project_id = pl.parent_id and main_table.store_id = pl.store_id',
                                           array('locale' => 'locale_code'))
                   ->order('main_table.origin_content_id')
                   ->order('main_table.status');
        
        $this->setCollection($collection);
        parent::_prepareCollection();
    }
    
    /**
     * 
     * prepare columns
     */
    protected function _prepareColumns() {
        $this->addColumn('content_title', array(
            'header' => Mage::helper('connector')->__('Content Title'),
            'align'  => 'left',
            'index'  => 'content_title',
            'renderer'  => 'connector/adminhtml_grid_column_renderer_link',
        ));
        
        $this->addColumn('filename', array(
            'header' => Mage::helper('connector')->__('File Uri'),
            'align'  => 'left',
            'index'  => 'filename',
        ));
        
        if (is_null($this->_contentTypeId)){
            $this->addColumn('type_name', array(
                'header' => Mage::helper('connector')->__('Content Type'),
                'align'  => 'left',
                'index'  => 'type_name',
                'filter'  => 'adminhtml/widget_grid_column_filter_select',
                'options' => Mage::getModel('connector/source_content_types')->getOptions(),
            ));
        }
        
        $this->addColumn('source_store_id', array(
            'header' => Mage::helper('connector')->__('Source Locale'),
            'align'  => 'left',
            'index'         => 'source_store_id',
            'type'          => 'store',
            'store_all'     => false,
            'store_view'    => false,
            'filter'    => false,
            'sortable'      => true
            
        ));
        
        $this->addColumn('store_id', array(
            'header' => Mage::helper('connector')->__('Destination Locale'),
            'align'  => 'left',
            'index'         => 'store_id',
            'type'          => 'store',
            'store_all'     => false,
            'store_view'    => false,
            'filter'    => false,
            'sortable'      => true
            
        ));
        
        $this->addColumn('username', array(
            'header' => Mage::helper('connector')->__('Submitter'),
            'align'  => 'left',
            'index'  => 'username',
            'width' => '100px'
        ));
        
        $this->addColumn('project_name', array(
            'header' => Mage::helper('connector')->__('Project Name'),
            'align'  => 'left',
            'index'  => 'project_name',
            'width' => '150px'
        ));
        
        $this->addColumn('submitted_time', array(
            'header' => Mage::helper('connector')->__('Submitted time'),
            'align'  => 'left',
            'index'  => 'submitted_time',
        ));
        
        $this->addColumn('percent', array(
            'header' => Mage::helper('connector')->__('Completed, %'),
            'align'  => 'left',
            'index'  => 'percent',
            'type'   => 'text',
            'width'   => '190px',
            'renderer' => 'connector/adminhtml_grid_column_renderer_progress',
        ));
        
        $this->addColumn('status', array(
            'header'  => Mage::helper('connector')->__('Status'),
            'align'   => 'left',
            'index'   => 'status',
            'width'   => '80px',
            'filter'  => 'adminhtml/widget_grid_column_filter_select',
            'options' => Mage::getModel('connector/source_content_status')->getOptions(),
        ));
        
         $this->addColumn('action', array(
            'header' => Mage::helper('connector')->__('Action'),
            'width'  => '80px',
            'filter'   => false,
            'sortable' => false,
            'renderer' => 'connector/adminhtml_grid_column_renderer_download',
         ));
         
        Mage_Adminhtml_Block_Widget_Grid::_prepareColumns();
    }  
    
    /**
     * 
     * @return \Smartling_Connector_Block_Adminhtml_Content_Grid
     */
    protected function _prepareMassaction() {
        $this->setMassactionIdField('content_id');
        $this->getMassactionBlock()->setFormFieldName('content');
        
        $this->getMassactionBlock()->addItem('update', array(
            'label' => Mage::helper('connector')->__('Update'),
            'url'   => $this->getUrl('*/adminhtml_translator/update'),
        ));
        
        parent::_prepareMassaction();
        return $this;
        
    }
    
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }
    
    /**
     * Add custom filter by type_id from joined table
     * 
     * @param string $column
     * @return \Smartling_Connector_Block_Adminhtml_Content_Grid
     */
    public function _addColumnFilterToCollection($column) {
        if ($column->getId() == 'type_name'){
            if ($value = $column->getFilter()->getValue()){
                $this->getCollection()->addFieldToFilter('type_id', array('eq' => $value));
            } 
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }
}
