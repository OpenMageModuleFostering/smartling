<?php


/**
 * Description of Interface
 *
 * @author Smartling
 */
interface Smartling_Connector_Model_Content_Interface 
{
    
    /**
     * Creates the same content for other locales
     * 
     * @param array $translatedContent
     * @param int $originContentId
     * @param array $storeId
     * @param Smartling_Connector_Model_Content $contentModel
     */
    public function createContent($translatedContent, $originContentId, $storeId);    
    
    /**
     * Creates content data for smartling translation
     * 
     * @param mixed $contentId
     * @param int $project_id Profile ID
     * @param int $sourceStoreId Source locale
     * @return string
     */
    public function createTranslateContent($contentId, $project_id, $sourceStoreId);
    
    /**
     * Return parsed response 
     * 
     * @param string $translatedContent
     */
    public function getTranslatedContent($translatedContent);
    
    /**
     * Return original content model 
     */
    public function getContentTypeEntityModel();
    
    /**
     * 
     * @param string $content
     * @param string $fileUri
     * @param array $item
     */
    public function uploadContent($content, $fileUri, $item);
    
}
