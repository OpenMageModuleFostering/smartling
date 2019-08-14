<?php
/**
 * Product attributes grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Smartling 
 */
class Smartling_Connector_Block_Adminhtml_Catalog_Product_Attributes_Grid 
    extends Smartling_Connector_Block_Adminhtml_Catalog_AbstractEavGrid
{
    
    public function __construct()
    {
        $this->_typeName = 'product';
        parent::__construct();
        $this->setId('productAttributesGrid');
    }
    
    /**
     * Prepare product attributes grid collection object
     *
     * @return Mage_Adminhtml_Block_Catalog_Product_Attribute_Grid
     */
    protected function _prepareCollection()
    {
        $resource = Mage::getModel('core/resource');
        $systemAttributes = array_keys(Mage::getStoreConfig('connector/translate_attributes/catalog_product'));
        $type = Mage::getModel('connector/content_types')->getTypeDetails($this->_typeName);
        $content_types_id = $type->getTypeId();
        
        $collection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addVisibleFilter()
            ->addFieldToFilter('is_global', Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE)
            ->addFieldToFilter('attribute_code', array('nin' => $systemAttributes));
        
        $collection->getSelect()
                ->joinLeft(
                            array('ia' => $resource->getTableName('connector/translate_attributes')), 
                                    'ia.attribute_id = main_table.attribute_id and ia.entity_type_id = ' . (int)$content_types_id, 
                                        array('is_attached' => new Zend_Db_Expr('IF(ia.id > 0, 1, 0)'))
                        );
        
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }
}
