<?php

/**
 * Js_Controller_Action_Helper_Ajax
 *
 * @package    Js
 * @subpackage Js_Ajax
 * @author     James Solomon <james@jmsolomon.com>
 * @copyright  James Solomon
 * @license    http://www.jmsolomon.com
 * @link       http://www.jmsolomon.com
 */

/**
 * @see Zend_Controller_Action_Helper_Abstract
 */
require_once 'Zend/Controller/Action/Helper/Abstract.php';

/**
 * @see Zend_Layout
 */
require_once 'Zend/Layout.php';

/**
 * A controller helper that will set the layout to "ajax", send data to a
 * default view (so no more need for empty views) and set the content type
 * for JSON responses
 * 
 * @uses   Zend_Layout
 * @uses   Zend_Controller_Action_Helper_Abstract
 * @author James Solomon <james@jmsolomon.com>
 */
class Js_Controller_Action_Helper_Ajax extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Use this for making an ajax JSON response
     * 
     * @param array|string|Js_Ajax_Reply $data
     * @return null
     */
    public function json($data)
    {
        // Set our response
        $response = $this->getResponse();
        $response->setHeader("Content-type", "application/json");
        
        $content = null;
        switch (true) {
            case is_array($data):
                $content = Zend_Json::encode($data);
                break;
                
            case $data instanceof Js_Ajax_Reply:
                $content = $data->send();
                break;
                
            default:
                $content = (string) $data;
                break;
        }
        
        return $this->_render($content);
    }
    
    /**
     * Simple way to send HTML to through AJAX if there is no need
     * for you to be using a seperate view.  But typically HTML
     * ajax responses can just come from the regular rendering process
     * 
     * @param string $html
     * @return null
     */
    public function html($html)
    {
        return $this->_render($html);
    }
    
    /**
     * Sets the content for the view, and sets the view to be rendered
     * 
     * @param string $contents
     * @return null
     */
    protected function _render($contents)
    {
        Zend_Layout::getMvcInstance()->disableLayout();
        
        $actionController = $this->getActionController();
        $actionController->view->content = $contents;
        $actionController->view->setBasePath(AP . "/views/");
        
        return $actionController->renderScript("index/ajax.phtml");
    }
}