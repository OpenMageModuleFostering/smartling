<?php

/**
 * Description of Translator
 *
 * @author Smartling
 */
require_once(Mage::getBaseDir('lib') . '/SmartlingApi/lib/SmartlingAPI.php');

class Smartling_Connector_Model_SmartlingAPI 
    extends SmartlingAPI
{
    /**
     * Flag for mock response data
     * @var int 
     */
    protected $_mock_data; 
    
    /**
     * Flag for mock response data
     * @var int 
     */
    protected $_mock_response_file = 'unit_test_file.xml'; 
    
    /**
     * Flag for mock response data
     * @var int 
     */
    protected $_mock_files_dir = 'var/smartling/'; 

    /**
     * The chapter which will be added to translated content in "mock response" mode
     * @var string 
     */
    protected $_test_char = '~'; 
    
    /**
     *
     * @var array 
     */
    protected $_testText = array('Smartling test product', 'test text', 'Title');


    public function __construct($options = array()) {
       
       $this->_mock_files_dir = Mage::getBaseDir('var') . DS . 'smartling' . DS;
       
       $this->_mock_data = Mage::getStoreConfig('dev/smartling/mock_data');
       
       if($this->_mock_data == 1) {
          if(!is_dir($this->_mock_files_dir)) {
              try {
                mkdir($this->_mock_files_dir, 0777, true);
              } catch (Exception $ex) {
                Mage::logException($ex);
                Mage::helper('connector')->log($ex->getMessage(), Zend_log::ERR);
              }
          }
       }
       
       parent::__construct(@$options[0], @$options[1], @$options[2], self::PRODUCTION_MODE);
   }
    
  /**
   * upload content to Smartling service
   *
   * @param string $content
   * @param string $fileType
   * @param string $fileUri
   * @param array $params
   * @return string
   */
  public function uploadContent($content, $params = array()) {
           
    $response = false;
    
    if($this->_mock_data == 1) {
    
       $filePath = $this->getTestFilePath(); 
       
       try {
           
           if (is_file($filePath) && !is_writable($filePath)) { 
               Mage::throwException("Can not write to file {$filePath}");
           } elseif(!is_writable($this->_mock_files_dir)) {
               Mage::throwException("Can not write to dir {$this->_mock_files_dir}");
           }
           
           $fp = fopen($filePath, 'w+');
           fwrite($fp, $content);
           fclose($fp);

           $response = '{"response":{"data":{"wordCount":16,"stringCount":5,"overWritten":false},"code":"SUCCESS","messages":[]}}';
           
       } catch (Mage_Core_Exception $ex) {
           Mage::logException($ex);
           Mage::helper('connector')->log($ex->getMessage(), Zend_log::ERR);
       }
        
    } else {
        $response = parent::uploadContent($content, $params);
    }
    
    Mage::helper('connector')->log($response, Zend_log::DEBUG);
    
    return $response;
  }
  
   /**
   * Download translated content from Smartling Service
   *
   * @param string $fileUri
   * @param string $locale
   * @return string|false
   */
  public function downloadFile($fileUri, $locale, $params = array()) {
    
    $response = false;  
    
    if($this->_mock_data == 1) {
        
        $filePath = $this->getTestFilePath();
        
        try {
            if(!file_exists($filePath)) {
                Mage::throwException("Download file {$filePath} not exists");
            }
            
            $xmlContent = simplexml_load_file($filePath);
            
            $nodesQueries = array(
                '//data/translate/htmlcontent',
                '//data/translate/content',
                '//data/translate/select/item'
            );
            $nodes = $xmlContent->xpath( implode('|', $nodesQueries) );
            
            foreach ($nodes as $node) {
                
                $nodeValue = $node->__toString();
                if(!$nodeValue) continue;
                
                $node->{0} = $this->makeTestContent($nodeValue);
            }
            
            $response = $xmlContent->asXML();
            
        } catch (Mage_Core_Exception $ex) {
           Mage::logException($ex);
           Mage::helper('connector')->log($ex->getMessage(), Zend_log::ERR);
        }
        
    } else {
        
        Mage::helper('connector')->log('Download file request. '
                                . ' fileUri: ' . $fileUri . '; ' 
                                . ' Params: ' . serialize($params) . '; ' 
                                . ' Locale: ' . $locale . '; ' 
                                . ' projectId: sendRequest' . $this->_projectId
                                . ' Credential: apiKey: ' . $this->protectPasw($this->_apiKey) . ';' 
                                . ' baseUrl: ' . $this->_baseUrl
                            ,Zend_log::DEBUG);
        
        $response = parent::downloadFile($fileUri, $locale, $params);
    }
    
    Mage::helper('connector')->log('Download file response. ' . $response, Zend_log::DEBUG);
    
    return $response;
  }
  
  /**
   * 
   * @param string $string
   * @return string
   */
  public function makeTestContent($string) {

    $string_len = strlen($string);

    for($i = 1; $i < round($string_len / 2); $i++) {
        $position = rand(1,$string_len);
        $string = substr($string,0,$position) . $this->_test_char . substr($string,$position);
    }
        
    return $string;
  }
  
  public function getTestFilePath() {
      return $this->_mock_files_dir . $this->_mock_response_file;
  }
  
  /**
   * retrieve status about file translation progress
   *
   * @param string $fileUri
   * @param string $locale
   * @return string
   */
  public function getStatus($fileUri, $locale, $params = array()) {

    $sendData = array_replace_recursive(array(
          'fileUri' => $fileUri,
          'locale' => $locale
                ), $params);
    
    $uri = 'file/status';
            
    if($this->_mock_data == 1) {
        return '{"response":{"data":{"fileUri":"' . $fileUri . '","wordCount":108,"fileType":"xml","callbackUrl":"","lastUploaded":"2014-12-16T00:23:33","stringCount":2,"approvedStringCount":2,"completedStringCount":0},"code":"SUCCESS","messages":[]}}';
    }
    
    Mage::helper('connector')->log('getStatus request: ' . serialize($sendData) . '; ' 
                                . ' projectId: sendRequest' . $this->_projectId
                                . ' Credential: apiKey: ' . $this->protectPasw($this->_apiKey) . ';' 
                                . ' API Url: ' . $this->_baseUrl . "/" . $uri
                            ,Zend_log::DEBUG);
    
    
    $response = $this->sendRequest($uri, $sendData, HttpClient::REQUEST_TYPE_GET);
    
    Mage::helper('connector')->log('getStatus response. ' . $response, Zend_log::DEBUG);
    
    return $response;
  }
  
  private function protectPasw($string) {
      $length = strlen($string);
      
      $visibleChaptersLength = round($length * 0.2);
      $preplaceSrtinglength = $length - ($visibleChaptersLength*2);
      
      $string = substr($string, 0, $visibleChaptersLength) 
              . str_repeat('X', $preplaceSrtinglength)
              . substr($string, -$visibleChaptersLength);
      
      return $string;
  }
}