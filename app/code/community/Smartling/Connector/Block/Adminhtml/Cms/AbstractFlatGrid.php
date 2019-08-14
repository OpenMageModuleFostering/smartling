<?php
/**
 * Product attributes grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Smartling 
 */
abstract class Smartling_Connector_Block_Adminhtml_Cms_AbstractFlatGrid 
    extends Mage_Adminhtml_Block_Widget_Grid
{
    
    /**
     *
     * @var string - Smartling type name 
     */
    protected $_typeName;


    public function __construct()
    {
        parent::__construct();
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
    }
    
    /**
     * Prepare product attributes grid columns
     *
     * @return Mage_Adminhtml_Block_Catalog_Product_Attribute_Grid
     */
    protected function _prepareColumns()
    {

        $this->addColumn('is_attached', array(
            'header'=>Mage::helper('connector')->__('Translate'),
            'sortable'=>true,
            'index'=>'is_attached',
            'width' => '20px',
            'align' => 'center',
            'renderer' => 'Smartling_Connector_Block_Adminhtml_Content_Grid_Column_Status',
            'filter' => false,
            'options' => array(
                '1' => Mage::helper('connector')->__('Yes'),
                '0' => Mage::helper('connector')->__('No'),
            ),
        ));
        
        $this->addColumn('id', array(
            'header'=>Mage::helper('eav')->__('Field Name'),
            'sortable'=>true,
            'index'=>'id',
             'width' => '250px',
        ));

        $this->addColumn('text', array(
            'header'=>Mage::helper('eav')->__('Field Description'),
            'sortable'=>true,
            'index'=>'text'
        ));
        
        return $this;
    }
    
    /**
     * Return url of given row
     *
     * @return string
     */
    public function getRowUrl($model)
    {
        return false;
    }
    
     /**
     * 
     * @return int
     */
    public function getContentTypeId() {
        $type = Mage::getModel('connector/content_types')->getTypeDetails($this->_typeName);
        return $type->getTypeId();
    }
}
