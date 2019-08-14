<?php

class Smartling_Connector_Block_Adminhtml_Instance_Widget extends Mage_Adminhtml_Block_Widget_Form
{
    protected $_formName = 'website_form';
    
    protected $_formType = '';
    
    protected $_scripts = array();
    
    protected $_connectionErrorText = '';
    
    public function _construct() {
        parent::_construct();
        $this->_connectionErrorText = addslashes(Mage::helper('connector')->__('Sorry. Connection error.'));
    }

    /**
     * 
     * @return string
     */
    public function getScript() {
        
        $this->_scripts[] = $this->_formName . "Form = new varienForm('" . $this->_formName . "', '');";
        
        $this->_scripts[] = "function getProfilesFor" . ucfirst($this->_formType) . "(selectElement){"
        .  " var reloadurl = '" . $this->getUrl('smartling/adminhtml_instance/profiles/type/' . $this->_formType . '/') . "filter_id/' + selectElement.value;"
        .  " new Ajax.Request(reloadurl, {"
        .  "    method: 'get', "
        .  "    onComplete: function(transport){ "
        .  "        if(transport.status == 200) { "
        .  "            var response = transport.responseText; "
        .  "            $('" . $this->_formType . "_profile_selection').update(response); "
        .  "        } else { "
        .  "            $('" . $this->_formType . "_profile_selection').update('" . $this->_connectionErrorText . "'); "
        .  "        }" 
        .  "    } "
        . "   });"
        . " }";
                                    
        
        if (is_array($this->_scripts) && sizeof($this->_scripts)) {
            return '<script type="text/javascript">' . "\n" . implode("\n", $this->_scripts) . "\n" . '</script>';
        }
        
    }
    
}