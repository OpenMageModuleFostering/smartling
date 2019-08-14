<?php

class Smartling_Connector_Model_Resource_Log_Collection 
    extends Varien_Data_Collection
{
    /**
     *
     * @var array 
     */
    protected $_include_only = array();
    
    /**
     *
     * @var boolean 
     */
    protected $_dataLoaded = false;
    
    /**
     *
     * @var array 
     */
    protected $_collectionItemsUnsorted = array();

    /**
     * Load data
     *
     * @param boolean $printQuery
     * @param boolean $logQuery
     * @return \Smartling_Connector_Model_Resource_Log_Collection
     */
    public function loadData($printQuery = false, $logQuery = false) {
        
        $_pageSize = $this->getPageSize();
        $_currPage = $this->getCurPage();
        $logFile = Mage::getBaseDir('var') . DS . 'log' . DS . Mage::getStoreConfig('dev/smartling/log_file');
        
        if ($this->_dataLoaded || !is_file($logFile) ) {
            return $this;
        }
        
        $content = file($logFile);
        $_itemsCount = 0;
        
        if($content != false && sizeof($content)) {
        
        $_startIndex = ($_currPage - 1) * $_pageSize;
        
        $_itemsCount = count($content);

        if ($_startIndex > $_itemsCount) {
          $_startIndex = (int)($_itemsCount / $_pageSize) * $_pageSize;
        }
        
        $inc = $rowCount = $_addedCount = $_index = 0;    
        $segments = $row = array();
        
        $collectionIndex = $currentIndex = 0;
        
            foreach ($content as $row) {
                
                $inc++;
                $rowCount++;
                
                preg_match_all('/(^.+) ?(INFO|DEBUG|ERR) \(\d+\)\: (.+)$/mi', $row, $segments);
                if(isset($segments[2][0])) {
                    $const = constant('Zend_log::' . $segments[2][0]);
                }
                
                $levels = Mage::helper('connector')->getLogLevels();
                
                $message = '';
                if(isset($segments[3][0])) {
                    $message = $segments[3][0];
                }
                
                if(isset($segments[2][0]) && strlen($segments[2][0])) { // if log level has found
                    $collectionIndex++;
                }
                
                $time = '';
                if(isset($segments[1][0])) {
                    $time = Mage::helper('core')->formatDate($segments[1][0], 'medium', true);
                }
                
                $item = array('id' => $rowCount,
                             'time' => $time,
                             'level' => $levels[$const],
                             'message' => $message);
                         
                if(sizeof($segments)) {
                    
                    if($currentIndex == $collectionIndex) {
                        $this->_collectionItemsUnsorted[$collectionIndex]['message'] .= $row;
                    } else {
                        $this->_collectionItemsUnsorted[$collectionIndex] = $item;
                        $rowCount++;
                    }
                }
                
                $currentIndex = $collectionIndex;
                $message = '';
                $item = array();
                $segments = array();

            }
            
            if(sizeof($this->_collectionItemsUnsorted)) {
                $finalArray = array_reverse($this->_collectionItemsUnsorted);

                foreach ($finalArray as $item) {
                    
                    if ($_index++ < $_startIndex) {
                        continue;
                    }

                    if (++$_addedCount > $_pageSize) {
                        break;
                    }

                    $varienObject = new Varien_Object();
                    $varienObject->setData($item);
                    $this->addItem($varienObject);
                }
            }
        }
        
        $this->_totalRecords = count($finalArray);
        
        $this->_dataLoaded = true;
        return $this;

    }
    
    public function getCurPage($displacement = 0) {
        return (int)$this->_curPage;
    }
    
}