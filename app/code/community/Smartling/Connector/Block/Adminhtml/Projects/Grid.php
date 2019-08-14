<?php
/**
 * Product attributes grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Smartling 
 */
class Smartling_Connector_Block_Adminhtml_Projects_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    
    public function __construct()
    {
        parent::__construct();
        $this->setId('projectsGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
    }
    
    /**
     * Prepare projects collection
     *
     * @return \Smartling_Connector_Block_Adminhtml_Projects_Grid
     */
    protected function _prepareCollection()
    {
        $resource = Mage::getSingleton('core/resource');
        $websiteTable = $resource->getTableName('core_website');
        
        $collection = Mage::getResourceModel('connector/projects_collection');
        
        $collection->getSelect()
                ->joinLeft(array("t1" => $websiteTable), 
                           "main_table.website_id = t1.website_id", 
                                array("website_name" => "t1.name"));
                
        
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare project grid columns
     *
     * @return \Smartling_Connector_Block_Adminhtml_Projects_Grid
     */
    protected function _prepareColumns()
    {
        

        $this->addColumn('name', array(
            'header'=>Mage::helper('connector')->__('Project Name'),
            'index'=>'name',
             'width' => '250px',
        ));
        
        $this->addColumn('project_id', array(
            'header'=>Mage::helper('connector')->__('Project Id'),
            'index'=>'project_id',
             'width' => '250px',
        ));

        $this->addColumn('key', array(
            'header'=>Mage::helper('connector')->__('API Key'),
            'index'=>'key',
            'width' => '400px'
        ));
        
        $this->addColumn('website_name', array(
            'header'=>Mage::helper('connector')->__('Website'),
            'index'=>'website_name',
            'width' => '70px',
            'filter' => false,
            'sortable' => true,
        ));
        
        $this->addColumn('active', array(
            'header'=>Mage::helper('connector')->__('Active'),
            'index'=>'active',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('connector')->__('Yes'),
                '0' => Mage::helper('connector')->__('No'),
            ),
            'width' => '50px'
        ));
        
        $this->addColumn('action', array(
            'header'    => Mage::helper('connector')->__('Action'),
            'width'     => '50px',
            'type'      => 'action',
            'getter'     => 'getId',
            'actions'   => array(
                array(
                    'caption' => Mage::helper('connector')->__('Edit'),
                    'url'     => array(
                        'base'=>'*/*/edit',
                    ),
                    'field'   => 'id'
                ),
            ),
            'filter'    => false,
            'sortable'  => false,
            'index'     => 'id',
        ));
        
        $this->addColumn('delete',
            array(
                'header' => $this->__('Delete'),
                'index'  => 'id',
                'width' => '50px',
                'type' => 'action',
            	'actions' => array(array(
                	'caption' => $this->__('Delete'),
            		'url' => array(
            			'base' => '*/*/delete',
                    ),
                    'confirm' => $this->__('Are you sure?'),
                    'field' => 'id'
                    )),
                'filter' => false,
                'sortable' => false,
        ));
        
        return $this;
    }
    
    /**
     * Return url of given row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return false;
    }
    
}
