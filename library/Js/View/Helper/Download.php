<?php

class Js_View_Helper_Download extends Zend_View_Helper_Abstract
{
    protected $_baseDirectory = '/downloads/';
    
    protected $_fileName;
    
    public function __toString()
    {
        return $this->render();
    }
    
    public function download($fileName)
    {
        $this->_fileName = $fileName;
        
        return $this;
    }
    
    public function setController($controllerName)
    {
        $this->_controllerName = $controllerName;
        return $this;
    }
    
    public function setAction($actionName)
    {
        $this->_actionName = $actionName;
        return $this;
    }
    
    public function render()
    {
        if (isset($this->_controllerName) && isset($this->_actionName)) {
            return sprintf(
                "/%s/%s/f/%s", 
                $this->_controllerName, 
                $this->_actionName, 
                $this->_fileName
            );
        }
    }
}