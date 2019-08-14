<?php
/**
 * Product attributes grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Smartling 
 */
abstract class Smartling_Connector_Block_Adminhtml_Catalog_AbstractEavGrid 
    extends Mage_Eav_Block_Adminhtml_Attribute_Grid_Abstract
{
     /**
     *
     * @var string - Smartling type name 
     */
    protected $_typeName;
    
    public function __construct()
    {
        parent::__construct();
        $this->setId('attributeGrid');
        $this->setDefaultSort('attribute_code');
        $this->setDefaultDir('ASC');
    }
    
    /**
     * Prepare product attributes grid columns
     *
     * @return Smartling_Connector_Block_Adminhtml_Catalog_AbstractEavGrid
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
        
        $this->addColumn('attribute_code', array(
            'header'=>Mage::helper('eav')->__('Attribute Code'),
            'sortable'=>true,
            'index'=>'attribute_code',
            'width' => '250px',
        ));

        $this->addColumn('frontend_label', array(
            'header'=>Mage::helper('eav')->__('Attribute Label'),
            'sortable'=>true,
            'index'=>'frontend_label'
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
