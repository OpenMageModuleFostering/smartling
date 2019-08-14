<?php
/**
 * Product attributes grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Smartling 
 */
class Smartling_Connector_Block_Adminhtml_Cms_Page_Fields_Grid 
    extends Smartling_Connector_Block_Adminhtml_Cms_AbstractFlatGrid
{
        
    public function __construct()
    {
        $this->_typeName = 'cmsPage';
        
        parent::__construct();
        $this->setId('cmsPageGrid');
    }
    
    /**
     * Prepare product attributes grid collection object
     *
     * @return Mage_Adminhtml_Block_Catalog_Product_Attribute_Grid
     */
    protected function _prepareCollection()
    {
        
        $collection = Mage::getResourceModel('connector/translate_fields_list_page_collection');
        
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }
    
}
