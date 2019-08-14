<?php

/**
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Localization_Files 
{
    
    /**
     * List of scanned files
     * @var array 
     */
    protected $_fileList = array();
    
    /**
     * List of indexed files in storage
     * @var array 
     */
    protected $_indexedFiles = array();
    
    protected $_resource;
    protected $_adapter;
    
    public function __construct() {
        $this->_resource = Mage::getSingleton('core/resource');
        $this->_adapter = $this->_resource->getConnection('write');
        $this->_fileList = array();
    }

    /**
     * Join list of files to $_fileList
     * @param string $path
     */
    public function joinFiles() {
        
        $params = func_get_args();
        
        if(!sizeof($params)) return false;
        
        foreach ($params as $path) {
            
            if(strpos($path, 'app/design/frontend/base') !== false) {
                continue;
            }
            
            $fileList = glob($path);
            if($fileList) {
                $this->_fileList = array_merge($this->_fileList, $fileList);
            }
        }
    } 
    
    /**
     * 
     * @return array
     */
    public function getFileList() {
        return $this->_fileList;
    }
    
    /**
     * Get files list with actual files modification time
     * @return array
     */
    public function getFileListFormated() {
        
        $fileList = array();
        if(sizeof($this->_fileList)) {
            foreach ($this->_fileList as $key => $file_path) {
                $updated_at = date('Y-m-d H:i:s', filemtime($file_path));
                $fileList[$key] = array('file_path' => $file_path, 
                                        'updated_at' => $updated_at);
            }
        }
            
        return $fileList;
    }
    
    /**
     * Return cross data collection of exist locales 
     * in store view and smarling profiles.
     * 
     * @return \Smartling_Connector_Model_Resource_Projects_Locales_Collection
     */
    public function getAvailableLocales() {
        $resource = Mage::getSingleton('core/resource');
        
        $collection = Mage::getModel('connector/projects_locales')->getCollection();
        
        $storeViewTableName = $resource->getTableName('core_store');
        $storeGroupTableName = $resource->getTableName('core_store_group');

        $collection->getSelect()
                   ->reset(Zend_Db_Select::COLUMNS)
                   ->joinInner(array('cs' => $storeViewTableName),
                                           'main_table.store_id = cs.store_id',
                                           array())
                   ->joinInner(array('csg' => $storeGroupTableName),
                                           'cs.group_id = csg.default_store_id',
                                           array())
                   ->joinInner(array('cs2' => $storeViewTableName),
                                           'csg.default_store_id = cs2.store_id',
                                           array('store_localization_dir'))
                   ->group('cs2.store_id');

        return $collection;
    }
    
    
    /**
     * 
     * @param string $dir_name
     * @param string $file_path
     * @return bool
     */
    public function isFileInIndex($dir_name, $file_path) {
        
        $collection = Mage::getModel('connector/localization_files_index')
                ->getCollection()
                ->addFieldToFilter('dir_name', array('like' => $dir_name))
                ->addFieldToFilter('file_path', array('like' => $file_path));
        
        return ($collection->getSize() > 0);
    }
    
    public function initIndexedFileList() {
        $collection = Mage::getModel('connector/localization_files_index')
                ->getCollection();
        
        foreach ($collection as $fileInfo) {
            $this->_indexedFiles[$fileInfo['file_path']] = $fileInfo;
        }
    }
    
    /**
     * Return info of indexed file from storage
     * @param string $path
     * @return array
     */
    public function getFileInfo($path) {
        if(array_key_exists($path, $this->_indexedFiles)) {
            return $this->_indexedFiles[$path];
        }
    }
    
    /**
     * Save scanned file list to storage
     * @param string $localeCode
     * @param array $fileList
     * @return boolean
     */
    public function saveFiles($localeCode, $fileList = array()) {
    
        if(!sizeof($fileList)) {
            $fileList = $this->getFileListFormated();
        }
        
        if(!sizeof($fileList)) {
            return false;
        }
    
        foreach ($fileList as $file) {

            $storagedFileInfo = $this->getFileInfo($file['file_path']);
            
            if(!array_key_exists('updated_at', $storagedFileInfo) 
                    || $storagedFileInfo['updated_at'] < $file['updated_at']) {
                $has_changed = 1;
            } else {
                $has_changed = 0;
            }
            
            $data = array(
                'dir_name' => $localeCode,
                'file_path' => $file['file_path'],
                'updated_at' => $file['updated_at'],
                'has_changed' => $has_changed,
                'file_exists' => 1
            );

            $this->_adapter->insertOnDuplicate(
                         $this->_resource->getTableName('connector/localization_files_index'), 
                         $data
                     );
        }
    }
    
    /**
     * Reset file_exists flag 
     */
    public function resetFileExistsFlag() {
        $filesIndexTable = $this->_resource->getTableName('connector/localization_files_index');
        $this->_adapter->query("update {$filesIndexTable} set file_exists = 0");
    }
    
    /**
     * Cleanup index stoage
     */
    public function cleanUp() {
        $filesIndexTable = $this->_resource->getTableName('connector/localization_files_index');
        $this->_adapter->query("delete from {$filesIndexTable} where file_exists = 0");
    }
   
}
