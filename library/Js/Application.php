<?php

class Js_Application
{
    /**
     * @var Js_Application
     */
    private static $_instance;
    
    /**
     * @var Zend_Application
     */
    private $_application = null;
    
    /**
     * Sets the current instance of Zend_Application
     * 
     * @param Zend_Application $application
     * @return Js_Application Provides fluent interface
     */
    public function setApplication(Zend_Application $application)
    {
        $this->_application = $application;
        return $this;
    }
    
    /**
     * Returns the current instance of Zend_Application
     * 
     * @return Zend_Application
     */
    public function getApplication()
    {
        return $this->_application;
    }
    
    /**
     * Singleton
     * 
     * @return Js_Application
     */
    public static function getInstance()
    {
        if (!self::$_instance) {
            self::$_instance = new Js_Application();
        }
        
        return self::$_instance;
    }
}
