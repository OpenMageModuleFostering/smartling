<?php

/**
 * Description of Content
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Resource_Content 
    extends Mage_Core_Model_Resource_Db_Abstract
{
    
    /**
     * define table and 
     */
    protected function _construct() {
        $this->_init('connector/translate_content', 'content_id');
    }
    
    /**
     * 
     * @param array $data
     */
    public function createNewTranslationQueue($data){
        $table = $this->getMainTable();
        $result = $this->_getWriteAdapter()->insertMultiple($table, $data);
        return $result;
    }
    
    /**
     * 
     * @param array $data
     * @param string $locales
     * @return array
     */
    public function findContent($data, $locales) {
        $select = $this->_getReadAdapter()->select()
            ->from(array('ct' => $this->getMainTable()))            
            ->where('ct.origin_content_id = ?', $data['origin_content_id'])
            ->where('ct.locale = ?', $locales)
            ->where('ct.type = ?', $data['type']);
        $select->reset(Zend_Db_Select::COLUMNS)
            ->columns(array('ct.filename', 'content_id'))
            ->order('ct.content_id DESC')
            ->limit(1);        
        return $this->_getReadAdapter()->fetchRow($select);
    }    
    
    /**
     * 
     * @param string $locale
     * @param array $data
     * @return int
     */
    public function addSingleItem($store_id, $data, $force = false) {
        
        if(!$force) {
            $locale = Mage::getSingleton('connector/projects_locales')->getLocaleCodeByStoreId($store_id);

            if(!$locale) {
                return 0;
            }
        }
            
        $additionalData = array (
            'store_id'  => $store_id,
            'submitter' => Mage::helper('connector')->getSubmitter(),
            'percent'   => floatval(0.00),
            'status'    => Smartling_Connector_Model_Content::CONTENT_STATUS_NEW,
        );
        
        $data = array_merge($additionalData, $data);

        $fields = array ('status', 'percent', 'submitter');
        $table = $this->getMainTable();
        $result = $this->_getWriteAdapter()->insertOnDuplicate($table, $data, $fields);        
        return $result;
    }
    
    /**
     * Save object object data
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    public function save(Mage_Core_Model_Abstract $object)
    {
        if ($object->isDeleted()) {
            return $this->delete($object);
        }
        
        $this->_serializeFields($object);
        $this->_beforeSave($object);
        $this->_checkUnique($object);
        if (!is_null($object->getId()) && (!$this->_useIsObjectNew || !$object->isObjectNew())) {
            
        $originContentId = $object->getOriginContentId();
        $projectId = $object->getProjectId();
            
        if(strstr($object->getLocales(), ',') && isset($originContentId)) {
            $condition[] = $this->_getWriteAdapter()->quoteInto('origin_content_id=?', $object->getOriginContentId());
            if($projectId)  {
                $condition[] = $this->_getWriteAdapter()->quoteInto('project_id=?', $object->getProjectId());
            }
        } else {
            $condition = $this->_getWriteAdapter()->quoteInto($this->getIdFieldName().'=?', $object->getId());
        }
            
            /**
             * Not auto increment primary key support
             */
            if ($this->_isPkAutoIncrement) {
                $data = $this->_prepareDataForSave($object);
                unset($data[$this->getIdFieldName()]);
                $this->_getWriteAdapter()->update($this->getMainTable(), $data, $condition);
            } else {
                $select = $this->_getWriteAdapter()->select()
                    ->from($this->getMainTable(), array($this->getIdFieldName()))
                    ->where($condition);
                
                if ($this->_getWriteAdapter()->fetchOne($select) !== false) {
                    $data = $this->_prepareDataForSave($object);
                    unset($data[$this->getIdFieldName()]);
                    if (!empty($data)) {
                        $this->_getWriteAdapter()->update($this->getMainTable(), $data, $condition);
                    }
                } else {
                    $this->_getWriteAdapter()->insert($this->getMainTable(), $this->_prepareDataForSave($object));
                }
            }
        } else {
            $bind = $this->_prepareDataForSave($object);
            if ($this->_isPkAutoIncrement) {
                unset($bind[$this->getIdFieldName()]);
            }
            $this->_getWriteAdapter()->insert($this->getMainTable(), $bind);

            $object->setId($this->_getWriteAdapter()->lastInsertId($this->getMainTable()));

            if ($this->_useIsObjectNew) {
                $object->isObjectNew(false);
            }
        }

        $this->unserializeFields($object);
        $this->_afterSave($object);

        return $this;
    }
    
}
