<?php

/**
 * Description of Request
 *
 * @author snail
 */
class Smartling_Connector_Model_Request 
    extends Mage_Core_Model_Abstract
{
    
    /**
     *
     * @var array 
     */
    protected $_headers = array();
    
    /**
     *
     * @var Zend_Http_Client_Adapter_Socket | null 
     */
    protected $_client = null;
    
    /**
     *
     * @var Zend_Http_Uri | null
     */
    protected $_uri = null;
    
    /**
     * 
     */
    protected $_port = null;
    
    /**
     *
     * @var array 
     */
    protected $_post = array();
    
    /**
     *
     * @var string 
     */
    protected $_method = Zend_Http_Client::GET;

    /**
     * 
     * @param string $uri
     */
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * 
     * @param array $headers
     * @return \Smartling_Connector_Model_Request
     */
    public function setHeaders($headers = array()){
        $this->_headers = array_merge($this->_headers, $headers);
        return $this;
    }
    
    /**
     * 
     * @param array $config
     */
    public function initConnection($config = array()){
        $this->_client = new Zend_Http_Client_Adapter_Socket();
        if (!empty($config)){
            $this->_client->setConfig($config);
        }        
        $this->_client->connect($this->_uri->getHost(), $this->_uri->getPort());
        
    }
    
    /**
     * 
     * @param array $post
     * @return \Smartling_Connector_Model_Request
     */
    public function setPostParams($post = array()){
        $this->_post = $post;
        return $this;
    }
    
    /**
     * 
     * @param string $method
     * @return \Smartling_Connector_Model_Request
     */
    public function setMethod($method){
        $this->_method = $method;
        return $this;
    }
    
    /**
     * 
     * @param string $uri
     * @return \Smartling_Connector_Model_Request
     */
    public function setUri($uri){
        $this->_uri = Zend_Uri_Http::factory($uri);
        $this->_setPort($uri);
        return $this;
    }
    
    /**
     * send request
     * 
     * @return void
     */
    public function sendRequest(){
        $this->_prepareRequest();
        $body = '';
        if (!empty($this->_post)){
            $body = http_build_query($this->_post);
            $this->setHeaders(array('Content-Length' => strlen($body)));
        }    
        $this->setHeaders(array('Connection' => 'Close'));
        $this->_client->write($this->_method, $this->_uri, '1.1', $this->_headers, $body);
    }
    
    public function getResponse(){
        return $this->_client->read();
    }
    
    /**
     * 
     * @return string
     */
    protected function _getCookie(){
        $webSites = Mage::app()->getWebsites();
        $code = $webSites[1]->getCode();
        $session = Mage::getSingleton("customer/session"); 
        $session->init('customer_' . $code);         
        return session_id();
    }
    
    /**
     * set default headers
     */
    protected function _prepareRequest(){
        $cookie = $this->_getCookie();
        $headers = array('Host'        => $this->_uri->getHost(),
                         'Cookie'      => "adminhtml={$cookie}",
                         'Cntent-type' => 'application/x-www-form-urlencoded; charset=utf-8',
                         'Accept'      => '*/*;q=0.5',
                        );
        $this->setHeaders($headers);                 
    }
    
    /**
     * Set port if not defined
     * 
     * @throws Mage_Exception
     */
    protected function _setPort($uri){
        if (!$this->_uri->getPort()) {
            $port = (parse_url($uri, PHP_URL_SCHEME) == 'https') ? 443 : 80;
            $this->_uri->setPort($port);
        }
    }
}
