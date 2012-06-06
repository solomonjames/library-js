<?php

/**
 * Js_Ajax_Reply
 *
 * @package    Js
 * @subpackage Js_Ajax
 * @author     James Solomon <solomonjames@gmail.com>
 * @copyright  2010 James Solomon
 * @license    http://www.jmsolomon.com
 * @link       http://www.jmsolomon.com
 */

/**
 * A simple class for handling ajax responses in a very fluent and
 * easy way.
 *
 * @author James Solomon <solomonjames@gmail.com>
 */
class Js_Ajax_Reply
{
    /**
     * Standard JSON reply object
     *
     * @var array
     */
    protected $_jsonArray = array(
        "data" => array(),
        "success" => false,
        "messages" => array()
    );

    /**
     * If using within a controller, this can be set to handle
     * all of the view/layout settings for you.
     *
     * @var Js_Controller_Action_Helper_Ajax
     */
    protected $_ajaxHelper = null;

    /**
     * Public construct.  Allows you to set the controller object to use
     *
     * @param Zend_Controller_Action $actionController
     * @return void
     */
    public function __construct($actionController = null)
    {
        if ($actionController instanceof Zend_Controller_Action) {
            $this->_ajaxHelper = Zend_Controller_Action_HelperBroker::getStaticHelper("Ajax");
            $this->_ajaxHelper->setActionController($actionController);
        }
    }
    
    /**
     * Helps you create and instance and chain calls together for
     * quick or small replies.
     * 
     * @param Zend_Controller_Action $actionController
     * @return Js_Ajax_Reply
     */
    public static function create($actionController = null)
    {
        return new self($actionController);
    }

    /**
     * Magic method if class is echo'd
     *
     * @uses   Js_Ajax_Reply::send()
     * @return string
     */
    public function __toString()
    {
        return $this->send();
    }

    /**
     * Complile and JSON encode data
     *
     * @uses   Js_Ajax_Reply::setData()
     * @return string
     */
    public function send()
    {
        // Do some reflection to grap all public properties that were set,
        // and put them into the data array
        $reflection = new ReflectionObject($this);
        $publicProperties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        if (!empty($publicProperties)) {
            $vars = array();
            foreach ($publicProperties as $p) {
                $vars[$p->getName()] = $this->{$p->getName()};
            }

            $this->setData($vars);
        }

        // If we brought in a controller, use the ajax helper to
        // set the layout, view, and headers
        if ($this->_ajaxHelper instanceof Js_Controller_Action_Helper_Ajax) {
            $data = $this->_ajaxHelper->json($this->_jsonArray);
        } else {
            $data = Zend_Json::encode($this->_jsonArray);
        }

        return $data;
    }

    /**
     * Set the response to success = true
     *
     * @return Js_Ajax_Reply
     */
    public function setSuccessful()
    {
        $this->_jsonArray['success'] = true;
        return $this;
    }

    /**
     * Set the response to success = false
     *
     * @return Js_Ajax_Reply
     */
    public function setFailed()
    {
        $this->_jsonArray['success'] = false;
        return $this;
    }

    /**
     * Is the response set to success = true?
     *
     * @return boolean
     */
    public function isSuccessful()
    {
        return $this->_jsonArray['success'];
    }

    /**
     * Assign the "data" portion of the JSON object
     *
     * @param $data
     * @return Js_Ajax_Reply
     */
    public function setData($data)
    {
        // Did this if you wanted to pass in a stdClass, or one
        // with public vars
        if (is_object($data)) {
            $data = (array) $data;
        }

        $this->_jsonArray['data'] = $data;
        return $this;
    }

    /**
     * Add a message to the JSON object
     *
     * @param  string $message
     * @return Js_Ajax_Reply
     */
    public function addMessage($message)
    {
        $this->_jsonArray['messages'][] = $message;
        return $this;
    }

    /**
     * Clear the current messages array
     *
     * @return Js_Ajax_Reply
     */
    public function clearMessages()
    {
        $this->_jsonArray['messages'] = array();
        return $this;
    }

    /**
     * Get the current array of messages
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->_jsonArray['messages'];
    }
}