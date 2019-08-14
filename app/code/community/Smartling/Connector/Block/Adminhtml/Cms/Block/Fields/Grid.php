<?php
/**
 * Product attributes grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Smartling 
 */
class Smartling_Connector_Block_Adminhtml_Cms_Block_Fields_Grid 
    extends Smartling_Connector_Block_Adminhtml_Cms_AbstractFlatGrid
{
    
    public function __construct()
    {
        $this->_typeName = 'cmsBlock';
        
        parent::__construct();
        $this->setId('cmsBlockGrid');
    }
    
    /**
     * Prepare product attributes grid collection object
     *
     * @return Mage_Adminhtml_Block_Catalog_Product_Attribute_Grid
     */
    protected function _prepareCollection()
    {
        
        $collection = Mage::getResourceModel('connector/translate_fields_list_block_collection');
        
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }
}
