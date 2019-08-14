<?php

/**
 * Projects filter abstract class
 *
 * @author Smartling
 */
abstract class Smartling_Connector_Model_Projects_Filter
    extends Mage_Core_Model_Abstract
{
    abstract public function getAvailableProjects($fiter_id);

    /**
     * 
     * @param Mage_Core_Model_Resource_Db_Collection_Abstract $collection
     * @return array
     */
    protected function getProjectList($collection) {

        $projects = array();
                
        foreach ($collection as $project) {
            
            if(!isset($projects[$project->getRowId()])) {
                $projects[$project->getRowId()] = array(
                                                    'id' => $project->getId(),
                                                    'name' => $project->getName(),
                                                    'website_id' => $project->getWebsiteId()
                                                    );
            }
            
            $localeName = Mage::app()->getStore($project->getStoreId())->getFrontendName()
                                                            . ' (' . Mage::app()->getStore($project->getStoreId())->getName() . ')';
            
            $projects[$project->getRowId()]['locales'][$project->getStoreId()] = 
                                               array(
                                                     'id' => $project->getStoreId(),
                                                     'project_identity' => $project->getId(),
                                                     'name' => $localeName,
                                                    );
            
        }

        
        return $projects;
    }
}
