<?php

/**
 * Description of Website
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Source_Projects 
{
    
     /**
     * 
     * @var int
     */
    protected $_website_id;
    
    /**
     * 
     * @param array $data
     */
    public function setData($data) {
        $this->_projectsLocales = $data;
        
        return $this;
    }

    /**
     * 
     * @return array
     */
    public function toOptionArray(){
        
        if(!is_array($this->_projectsLocales) || !sizeof($this->_projectsLocales)) {
            return array();
        }
        
        $list = array();
        
        foreach ($this->_projectsLocales as $locale) {
            
            if($this->_website_id 
                    && $locale['website_id'] != $this->_website_id
               || !sizeof($locale['locales'])) {
                continue;
            }
            
            $listValues = array();
            foreach ($locale['locales'] as $localeValues) {
                $listValues[] = array(
                    'label' =>  $localeValues['name'],
                    'value' => $localeValues['project_identity']
                );
            }
            
            $list[] = array(
                'label' => $locale['name'],
                'value' => $listValues
            );
        }

        return $list;
    }
    
    /**
     * Set website ID to filter list
     * @param int $website_id
     */
    public function addWebsiteIdToFilter($website_id) {
        $this->_website_id = $website_id;
        return $this;
    }
}
