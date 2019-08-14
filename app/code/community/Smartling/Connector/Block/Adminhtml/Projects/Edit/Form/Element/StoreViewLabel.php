<?php 

class Smartling_Connector_Block_Adminhtml_Projects_Edit_Form_Element_StoreViewLabel extends Varien_Data_Form_Element_Abstract
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
        
        $checkboxId = 'is_enabled_' . $this->getStoreId();
        $inputTextId = 'locale_code_' . $this->getStoreId();
        $hiddenInputId = 'project_locale_id_' . $this->getStoreId();
        $values = $this->getValues();
                
        $checkbox = $this->addField($checkboxId, 'checkbox', array(
            'name'      => 'is_enabled[' . $this->getStoreId() . ']',
            'no_span' => true,
            'class' => 'sl-required-entry-multy'
        ));
        
        $textFieldValue = '';
        $hiddenField = '';
        
        if($values) {
            $checkbox->setIsChecked(strlen($values->getLocaleCode()) > 0);
            $textFieldValue = $values->getLocaleCode();
            
            $hiddenField = $this->addField($hiddenInputId, 'hidden', array(
                'name' => 'project_locale_identity[' . $this->getStoreId() . ']',
                'value' => $values->getData('id')
            ))->toHtml();
        }

        $inputText =  $this->addField($inputTextId, 'text', array(
            'name' => 'locale_code[' . $this->getStoreId() . ']',
            'title' => Mage::helper('connector')->__('Smartling Locale Code'),
            'no_span' => true,
            'value' => $textFieldValue
        ));
        
        $html = $checkbox->toHtml() 
              . $inputText->toHtml()
              . $hiddenField
              . $this->getAfterElementHtml();
        
        return $html;
    }

    /**
     * 
     * @param string $idSuffix
     * @return string
     */
    public function getLabelHtml($idSuffix = ''){
        
        if (!is_null($this->getLabel())) {
            $html = '<label for="'.$this->getHtmlId() . $idSuffix . '" style="'.$this->getLabelStyle().'">'.$this->getLabel()
                . ( $this->getRequired() ? ' <span class="required">*</span>' : '' ).'</label>'."\n";
        }
        else {
            $html = '';
        }
        return $html;
    }
}