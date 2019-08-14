<?php
/**
 * Grid select input column renderer
 *
  * @author Smartling
 */

class Smartling_Connector_Block_Adminhtml_Content_Grid_Column_Status
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Checkbox
{

    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function renderOld(Varien_Object $row)
    {
        $name = $this->getColumn()->getName() ? $this->getColumn()->getName() : $this->getColumn()->getId();
        $html = '<select name="' . $this->escapeHtml($name) . '" ' . $this->getColumn()->getValidateClass() . ' onchange="translateStatus(this, \'' . $row->getAttributeCode() . '\')">';
        $value = $row->getData($this->getColumn()->getIndex());
        
        foreach ($this->getColumn()->getOptions() as $val => $label){
            $selected = ( ($val == $value && (!is_null($value))) ? ' selected="selected"' : '' );
            $html .= '<option value="' . $this->escapeHtml($val) . '"' . $selected . '>';
            $html .= $this->escapeHtml($label) . '</option>';
        }
        $html.='</select>';
        return $html;
    }
    
    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        $values = $this->getColumn()->getValues();
        $value  = $row->getData('is_attached');
        $checked = '';
        
        if ($value == 1) {
            $checked .= ' checked="checked"';
        }

        $disabledValues = $this->getColumn()->getDisabledValues();
        if (is_array($disabledValues)) {
            $disabled = in_array($value, $disabledValues) ? ' disabled="disabled"' : '';
        }
        else {
            $disabled = ($value === $this->getColumn()->getDisabledValue()) ? ' disabled="disabled"' : '';
        }

        $this->setDisabled($disabled);

        if ($this->getNoObjectId() || $this->getColumn()->getUseIndex()){
            $v = $value;
        } else {
            $v = ($row->getId() != "") ? $row->getId():$value;
        }

        $checked .= ' onchange="translateStatus(this, \'' . $row->getAttributeCode() . '\')"';
        return $this->_getCheckboxHtml($v, $checked);
    }    

}
