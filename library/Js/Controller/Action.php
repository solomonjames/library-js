<?php

class Js_Controller_Action extends Zend_Controller_Action
{
    const FLASH_MESSAGE = 'good';
    const FLASH_ERROR   = 'bad';
    const FLASH_INFO    = 'info';

    protected $_resource = '';

    protected $_flashMessenger;

    /**
     * Stores all instant flashes
     *
     * @var array
     */
    protected $_flashMessages = array();

    public function init()
    {
        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $this->view->headTitle()->setSeparator(' - ');
    }

    /**
     * Helper to create session flashes
     *
     * @author James Solomon <james@jmsolomon.com>
     * @param  string $message Message to be displayed
     * @param  string $type    Use "bad" or "good"
     * @return Js_Controller_Action
     */
    public function flash($message, $type = null)
    {
        if (! $this->_flashMessenger instanceof Zend_Controller_Action_Helper_FlashMessenger) {
            $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
        }

        if (null === $type) {
            $type = self::FLASH_MESSAGE;
        }
        
        $message = array(
            "type"    => $type,
            "message" => $message
        );
        
        $this->_flashMessenger->addMessage($message);
        
        return $this;
    }
}