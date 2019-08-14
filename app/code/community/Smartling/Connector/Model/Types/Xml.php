<?php

/**
 * Description of Xml
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Types_Xml 
{
    
    /**
     *
     * @var DOMElement
     */
    protected $_rootNode;
    
    /**
     *
     * @var DOMElement 
     */
    protected $_contentGroupNode;
    
    /**
     *
     * @var string
     */
    protected $_contentNodeName;
    
    /**
     *
     * @var string 
     */
    protected $_rootNodeName;
    
    /**
     *
     * @var string 
     */
    protected $_contentGroupNodeName;
    
    /**
     *
     * @var DOMDocument 
     */
    protected $_xml;
    
    /**
     *
     * @var array
     */
    protected $_params;
    
    /**
     *
     * @var string 
     */
    protected $_noTranslateComment = 'smartling.sltrans = notranslate';
    
    /**
     *
     * @var string
     */
    protected $_translateComment = 'smartling.sltrans = translate';
    
    /**
     *
     * @var string
     */
    protected $_translatePaths = "smartling.translate_paths";
    
    /**
     *
     * @var string
     */
    protected $_stringFormatPaths = "smartling.string_format_paths";
    
    /**
     * @var string
     */
    protected $_placeholderCustomName = "smartling.placeholder_format_custom";
    
    /**
     *
     * @var array 
     */
    protected $_customPlaceholder = array();
    
    /**
     *
     * @var array
     */
    protected $_htmlContentNodeName = array();
    
    /**
     * init some actions
     * 
     * @param array $params
     */
    public function __construct($params = array()) {
        $this->_xml = new DOMDocument("1.0", "UTF-8");
        $this->_params = $params;
        $this->_setRootNode();
        $this->_setContentGroupNode();
        $this->_setContentNode();
        $this->_initNodes();
        
        $placeholder = '(?>\{\{)[^\{].+?(?>\}\})';
        $this->addCustomPlaceholder($placeholder);
    }
    
    /**
     * set content root node name
     * 
     * @return void
     */
    protected function _setRootNode(){
        if (isset($this->_params['root'])){
            $this->_rootNodeName = $this->_params['root'];
        } else {
            $this->_rootNodeName = 'data';
        }        
    }
    
    /**
     * set content group node name
     * 
     * @return void
     */
    protected function _setContentGroupNode(){
        if (isset($this->_params['content_group'])){
            $this->_contentGroupNodeName = $this->_params['content_group'];
        } else {
            $this->_contentGroupNodeName = 'translate';
        }
    }
    
    /**
     * set content node name
     * 
     * @return void
     */
    protected function _setContentNode(){
        if (isset($this->_params['content'])){
            $this->_contentNodeName = $this->_params['content'];
        } else {
            $this->_contentNodeName = 'content';
        }
    }
    
    /**
     * init root and group nodes
     */
    protected function _initNodes(){
        $this->_rootNode = $this->_xml->createElement($this->_rootNodeName);
        $this->_contentGroupNode = $this->_xml->createElement($this->_contentGroupNodeName);
    }
    
    /**
     * 
     * @param array $params
     */
    public function setContentGroupAttribute($params = array()){
        if (!empty($params)){
            foreach ($params as $name => $value) {
                $this->_contentGroupNode->setAttribute($name, $value);
            }
        }
    }
            
    
    /**
     * set translate paths
     * 
     * @return DOMElement
     */
    protected function _getTranslatePaths(){
        
        $translatePaths = '';
        
        $translatePathsItems[] = $this->_translatePaths . " = " 
                        . $this->_rootNodeName . "/" . $this->_contentGroupNodeName
                        . "/" . $this->_contentNodeName;
        
        if (!empty($this->_htmlContentNodeName)) {
            $htmlNode = array_unique($this->_htmlContentNodeName);
            
            foreach($htmlNode as $nodeName){
                $translatePathsItems[] = $this->_rootNodeName . "/" 
                                        . $this->_contentGroupNodeName . "/" 
                                        . $nodeName;
            }
        }
        
        $translatePaths .= implode(', ', $translatePathsItems);
        
        $translatePathsComment = $this->_xml->createComment($translatePaths);
        return $translatePathsComment;
    }
    
    /**
     * 
     * @param string placeholder
     */
    public function addCustomPlaceholder($placeholder){
        if (!empty($placeholder)){
            $this->_customPlaceholder[] = $placeholder;
        }        
    }    
    
    /**
     * 
     * @return null | DOMElement
     */
    protected function _getCustomPlaceholder(){        
         if (sizeof($this->_customPlaceholder) > 0){
             $customPlaceholder = $this->_placeholderCustomName . " = ";
             for ($i = 0; $i < sizeof($this->_customPlaceholder); $i++){
                 $customPlaceholder .= $this->_customPlaceholder[$i];
             }
             
             $placeholderComment = $this->_xml->createComment($customPlaceholder);
             return $placeholderComment;
         } 
         return null;
    }

    /**
     * 
     * @param string $content
     * @param array $attribute
     */
    public function setTranslateContent($content, $attribute = array()){
        $contentNode = $this->_xml->createElement($this->_contentNodeName);
        if (!empty($attribute)){
            foreach ($attribute as $name => $value) {
                $contentNode->setAttribute($name, $value);
            }            
        }
        
        $contentTextNode = $this->_xml->createTextNode($content);
        $contentNode->appendChild($contentTextNode);
        $this->_contentGroupNode->appendChild($contentNode);        
    }
    
    /**
     * 
     * @param string $content
     * @param array $attribute
     * @return void
     */
    public function setNonTranslateContent($content, $attribute = array()){
        $contentNode = $this->_xml->createElement($this->_contentNodeName);
        if (!empty($attribute)){
            foreach ($attribute as $name => $value) {
                $contentNode->setAttribute($name, $value);
            }            
        }
        
        $contentTextNode = $this->_xml->createTextNode($content);        
        $contentNode->appendChild($contentTextNode);
        $comment = $this->_xml->createComment($this->_noTranslateComment);
        $this->_contentGroupNode->appendChild($comment);
        $this->_contentGroupNode->appendChild($contentNode);
    }
    
    /**
     * 
     * @return string
     */
    public function createContentFile(){
        $this->_xml->appendChild($this->_getTranslatePaths());
        
        if (!is_null($this->_getCustomPlaceholder())){
            $this->_xml->appendChild($this->_getCustomPlaceholder());
        }
        
        if (!empty($this->_htmlContentNodeName)){
            $this->_xml->appendChild($this->_getFormatPaths("html", $this->_htmlContentNodeName));
        }
        
        $this->_xml->appendChild($this->_rootNode);
        $this->_rootNode->appendChild($this->_contentGroupNode);       
        return $this->_xml->saveXML();
    }
    
    /**
     * 
     * @param string $filename
     */
    public function saveToFile($filename){
        $this->_xml->save($filename);
    } 
    
    /**
     * 
     * @param string $type
     * @param array $paths
     */
    protected function _getFormatPaths($type, $paths){
        $formatPaths = $this->_stringFormatPaths . " = ";
        for ($i = 0; $i < count($paths); $i++){
            $formatPathsArray[] = $type . " : ". $this->_rootNodeName 
                         . "/" . $this->_contentGroupNodeName 
                         . "/" . $paths[$i];
        }
        $formatPathsArray = array_unique($formatPathsArray);
        $formatPaths .= implode(', ', $formatPathsArray);
        $translateFormatPaths = $this->_xml->createComment($formatPaths);
        return $translateFormatPaths;
    }
    
    /**
     * 
     * @param string $nodeName
     * @param string $content
     * @param array $attribute
     */
    public function setHtmlContent($nodeName, $content, $attribute = array()){        
        $this->_htmlContentNodeName[] = $nodeName;
        $contentNode = $this->_xml->createElement($nodeName);
        if (!empty($attribute)){
            foreach ($attribute as $name => $value) {
                $contentNode->setAttribute($name, $value);
            }            
        }        
        $contentTextNode = $this->_xml->createCDATASection($content);
        $contentNode->appendChild($contentTextNode);
        $this->_contentGroupNode->appendChild($contentNode);
    }
    
    /**
     * 
     * @param string $source
     */
    public function loadContent($source){
        $this->_xml->loadXML($source);
        return $this;
    } 
    
    /**
     * Return format capatible to save EAV attributes
     * @return array
     */
    public function getAllData() {
        
        $content = array();
        
        $xpath = new DOMXPath($this->_xml);
        $queryText = '//data/translate/content | '
                   . '//data/translate/htmlcontent';
        $resultText = $xpath->query($queryText);
        
        if(sizeof($resultText))
        foreach($resultText as $element) {
            $content[$element->getAttribute('attribute')] = $element->nodeValue;
        }
        
        $queryOptions = '//data/translate/select';
        $resultOptions = $xpath->query($queryOptions);
        
        if(sizeof($resultOptions))
        foreach($resultOptions as $element) {
            $items = array();
            foreach($element->getElementsByTagName('item') as $item) {
                $option_id = $item->getAttribute('option_id');
                if(!is_numeric($option_id)) continue;
                $items[] = (int)$option_id; 
            }
            
            if(sizeof($items)) {
                switch ($element->getAttribute('type')) {
                    case 'select':
                        $content[$element->getAttribute('attribute')] = $items[0];
                    break;
                    case 'multiselect':
                    default:
                        $content[$element->getAttribute('attribute')] = $items;
                    break;
                }
            } 
        }
        
        return $content;
    }
    
    /**
     * Return format to save attributes options
     * @return array
     */
    public function getOptionsValues() {
        
        $items = array();
        $xpath = new DOMXPath($this->_xml);
        
        $queryOptions = '//data/translate/select';
        $resultOptions = $xpath->query($queryOptions);
        
        if(sizeof($resultOptions))
        foreach($resultOptions as $element) {
            
            foreach($element->getElementsByTagName('item') as $item) {
                $option_id = $item->getAttribute('option_id');
                if(!is_numeric($option_id)) continue;

                $items[$option_id] = array(
                                'option_id' => (int)$option_id, 
                                'value' => $item->nodeValue
                                );
            }
            
        }
        
    
        return $items;
    }
    
    /**
     * 
     * @return array
     * @deprecated since version 0.1.7
     */
    public function getContentElementsData(){
        $content = array();
        foreach ($this->_xml->getElementsByTagName($this->_contentNodeName) as $element) {
            $content[$element->getAttribute('attribute')] = $element->nodeValue;
        }        
        return $content;
    }
    
    /**
     * 
     * @return array
     * @deprecated since version 0.1.7
     */
    public function getHtmlContentElementData($nodeName){
        $content = array();         
        foreach ($this->_xml->getElementsByTagName($nodeName) as $element) {           
            $content[$element->getAttribute('attribute')] = $element->nodeValue;
        }        
        return $content;
    }
    
    /**
     * Push node name to _htmlContentNodeName list
     * @param type $nodeName
     */
    public function pushHtmlContentNodeName($nodeName) {
        $this->_htmlContentNodeName[] = $nodeName;
    }
    
    /**
     * 
     * @return DOMDocument
     */
    public function getXmlInstance() {
        return $this->_xml;
    }
    
    /**
     * 
     * @return DOMElement
     */
    public function getСontentGroupNode() {
        return $this->_contentGroupNode;
    }
    
}