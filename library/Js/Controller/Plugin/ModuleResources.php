<?php

/**
 * @see Zend_Controller_Action_Helper_Abstract
 */
require_once 'Zend/Controller/Action/Helper/Abstract.php';

/**
 * 
 * @category   Js
 * @package    Js_Controller
 * @subpackage Plugins
 * @author     James Solomon <james@jmsolomon.com>
 */
class Js_Controller_Plugin_ModuleResources extends Zend_Controller_Plugin_Abstract
{
    /**
     * Holds all the custom resources we want modular
     * 
     * @var array
     */
    protected $_resources = array();
    
    /**
     * Initialize and get custom resources
     * 
     * @param array $resources
     * @return void
     */
    public function __construct($resources = array())
    {
        $this->_resources = $resources;
    }
    
    /**
     * Setup Module Autoloader to know about our custom resources
     * for the module that is being loaded
     * 
     * @param Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $frontController = Zend_Controller_Front::getInstance();
        $moduleDirectory = $frontController->getModuleDirectory($request->getModuleName());
        $moduleNamespace = ucwords($request->getModuleName());
        
        $autoloader = new Zend_Application_Module_Autoloader(array(
            'namespace'     => $moduleNamespace,
            'basePath'      => $moduleDirectory,
            'resourceTypes' => $this->_resources
        ));
    }
}