<?php
/**
 * Product attributes grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Smartling 
 */
class Smartling_Connector_Block_Adminhtml_Logs_View_Grid 
    extends Mage_Adminhtml_Block_Widget_Grid
{
    
    public function __construct()
    {
        $this->_typeName = 'smLog';
        $this->_filename = Mage::getStoreConfig('dev/smartling/log_file');
        
        parent::__construct();
        $this->setId('smLogGrid');
        $this->setSaveParametersInSession(true);
        $this->setFilterVisibility(false);
    }
    
    /**
     * Prepare product attributes grid collection object
     *
     * @return Mage_Adminhtml_Block_Catalog_Product_Attribute_Grid
     */
    protected function _prepareCollection()
    {
        
        $_pageSize = (int) $this->getParam($this->getVarNameLimit(), $this->_defaultLimit);
        $_currPage = (int) $this->getParam($this->getVarNamePage(), $this->_defaultPage);
        
        $collection = Mage::getResourceModel('connector/log_collection');
        
        $collection->setPageSize($_pageSize);
        $collection->setCurPage($_currPage);
        
        $this->setCollection($collection);

        return parent::_prepareCollection();
        
    }
    
    /**
     * Prepare product attributes grid columns
     *
     * @return Mage_Adminhtml_Block_Catalog_Product_Attribute_Grid
     */
    protected function _prepareColumns()
    {

        $this->addColumn('id', array(
            'header'=> Mage::helper('connector')->__('id'),
            'sortable'=> true,
            'filter'=> false,
            'index' => 'id',
             'width' => '50px',
        ));

        $this->addColumn('time', array(
            'header'=> Mage::helper('connector')->__('Time'),
            'sortable'=> true,
            'filter'=> false,
            'index' => 'time',
            'width' => '150px',
            'align' => 'center'
        ));
        
        $this->addColumn('level', array(
            'header'=>Mage::helper('connector')->__('Level'),
            'index'=>'level',
            'sortable'=> true,
            'filter'=> false,
            'width' => '100px'
        ));
        
        $this->addColumn('message', array(
            'header' => Mage::helper('connector')->__('Message'),
            'sortable' => true,
            'filter'=> false,
            'index'=>'message'
        ));
        
        return $this;
    }
}
