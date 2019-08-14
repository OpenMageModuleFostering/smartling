<?php 

class Smartling_Connector_Block_Adminhtml_Projects_Edit_Form_Element_Profiles extends Varien_Data_Form_Element_Abstract
{
    public function __construct($attributes=array())
    {
        parent::__construct($attributes);
        $this->setType('label');
    }

    /**
     * 
     * @return string
     */
    public function getElementHtml()
    {
        return '';
    }

    /**
     * 
     * @param string $idSuffix
     * @return string
     */
    public function getLabelHtml($idSuffix = ''){
        $website_id = $this->getFilterWebsiteId();
                
        $localesData = array(
                'entity_type'     => $this->getEntityType(),
                'filter_id'     => $this->getFilterId()
            );

        $multiselectElement = 
        $this->getLayout()->createBlock('connector/adminhtml_projects_locales_form',
                                    'locales', 
                                    $localesData);

        $html = $multiselectElement->toHtml();

        if(strstr($html, 'input')) {
            $continue_button = 
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('catalog')->__('Continue'),
                    'onclick'   => 'website_form_' . strtolower($this->getEntityType()) . "Form.submit()",
                    'class'     => 'save'
                    ));

            $html .= $continue_button->toHtml();
        }
        
        return '<p id="' . $this->getEntityType() . '_profile_selection">' . $html . '</p>';
        
    }
}