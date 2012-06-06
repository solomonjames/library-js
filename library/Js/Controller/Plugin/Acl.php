<?php

/**
 * @see Zend_Controller_Action_Helper_Abstract
 */
require_once 'Zend/Controller/Action/Helper/Abstract.php';

/**
 * @see Zend_Acl_Resource_Interface
 */
require_once 'Zend/Acl/Resource/Interface.php';

/**
 * Acl Action Helper
 * 
 * Handles checking if the user is logged in, and if they have acess
 * to the requested /module/controller/action.  If they are not, 
 * they are sent to the login page or an error page.
 * 
 * @category   Js
 * @package    Js_Controller
 * @subpackage Plugins
 * @author     James Solomon <james@jmsolomon.com>
 */
class Js_Controller_Plugin_Acl 
    extends Zend_Controller_Plugin_Abstract
        implements Zend_Acl_Resource_Interface
{
    /**
     * @var Zend_Acl
     */
    protected $_acl;
    
    /**
     * @var Zend_Auth
     */
    protected $_auth;
    
    /**
     * @var Zend_Controller_Request_Abstract
     */
    protected $_request;
    
    /**
     * @var Zend_Controller_Response_Abstract
     */
    protected $_response;
    
    /**
     * Hooking into the Helper preDispatch call.
     * We will check here if the user has access to the module/controller/action
     * that they are trying to get too.
     * 
     * If a user is not logged in, they are given the role of "guest" 
     * and treated accordingly.
     * 
     * @param Zend_Controller_Request_Abstract $request
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $this->_acl     = Js_Acl::getInstance();
        $this->_auth    = Zend_Auth::getInstance();
        $this->_request = $request;
        
        $resourceId = $this->getResourceId();
        
        if (!$this->_acl->has($resourceId)) {
            $resourceId = null;
        }
        
        // Not using the auth controller, so do normal checks
        // Check for authentication
        if (!$this->_acl->isAllowed($resourceId, $request->getActionName())) {
            if (!$this->_auth->hasIdentity()) {
                return $this->_redirect('login');
            } else {
                return $this->_redirect('error');
            }
        }
    }
    
    /**
     * Generates the needed resouceId for the request
     * 
     * @see Zend_Acl_Resource_Interface
     */
    public function getResourceId()
    {
        $module = $this->_request->getModuleName();
        $controller = $this->_request->getControllerName();
        $resource = $module ? "$module/$controller" : $controller;
        
        return $resource;
    }
    
    /**
     * Just a simple way to alter the flow.
     * 
     * Note: This does not do a redirect, but does a forward
     * 
     * @param string $type
     * @return void
     */
    protected function _redirect($type)
    {
        switch ($type) {
            case 'login':
                $module = 'default';
                $controller = 'auth';
                $action = 'index';
                break;
                
            case 'error':
                $module = 'default';
                $controller = 'error';
                $action = 'acl';
                break;
        }
        
        $this->_request->setControllerName($controller)
                       ->setModuleName($module)
                       ->setActionName($action);
    }
}