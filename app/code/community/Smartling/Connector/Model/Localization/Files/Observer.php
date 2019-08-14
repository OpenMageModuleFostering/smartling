<?php

/**
 * Description of Observer
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Localization_Files_Observer
{
    
    /**
     * Reset has_changed flag for files which was put to queue
     * 
     * @param Varien_Event_Observer $object
     */
    public function resetСhangedFlag(Varien_Event_Observer $object){
        
        $contentModel = $object->getEvent()->getData('model_instance');
        $content = $object->getEvent()->getData('bulk_submit_content');
        
        if( ($contentModel instanceof Smartling_Connector_Model_Content_Localization) === false 
                || !is_array($content) ) {
           return false;
        }
        
        foreach ($content as $id) {
            $model = Mage::getModel('connector/localization_files_index')->load($id);
            $model->setHasChanged(0);
            $model->save();
        }
        
    }
    
    /**
     * Reinxed localization files
     * 
     * @param Varien_Event_Observer $event
     */
    public function checkFiles(Varien_Event_Observer $event) {
        
        $files = Mage::getModel('connector/localization_files');

        $files->resetFileExistsFlag();
        $files->initIndexedFileList();
        $collection = $files->getAvailableLocales();

        foreach ($collection as $locale) {

            $localeCode = $locale->getStoreLocalizationDir();

            $files->joinFiles('app/locale/' . $localeCode . '/*.csv', 
                              'app/design/*/*/default/locale/' . $localeCode . '/*.csv');
            
            $files->saveFiles($localeCode);
        }

        $files->cleanUp();
        
    }
    
    /**
     * Join files path to content collection
     * 
     * @param Varien_Event_Observer $event
     */
    public function joinFilesPath(Varien_Event_Observer $event) {
        
        $collection = $event->getEvent()->getData('collection');
        $resource = Mage::getSingleton('core/resource');
        
        $collection->getSelect()
                ->joinLeft(array('f' => $resource->getTableName('connector/localization_files_index')),
                            'main_table.origin_content_id = f.id', array('file_path'));
        
    }
}
