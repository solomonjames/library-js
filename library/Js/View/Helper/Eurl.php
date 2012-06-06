<?php

/**
 * Helper for making easy links and getting urls that depend on the routes and router
 *
 * @package    Js_View
 * @subpackage Helper
 */
class Js_View_Helper_Eurl extends Zend_View_Helper_Abstract
{
    /**
     * This stands for "easy url", because it places the route name
     *      first in the order of parameters.
     *
     * Generates an url given the name of a route.
     *
     * 
     *
     * @param  mixed $name       The name of a Route to use. If null it will use the current Route
     * @param  array $urlOptions Options passed to the assemble method of the Route object.
     * @param  bool  $reset      Whether or not to reset the route defaults with those provided
     * @param  bool  $encode
     * @return string Url for the link href attribute.
     */
    public function eurl($name = null, array $urlOptions = array(), $reset = false, $encode = true)
    {
        $router = Zend_Controller_Front::getInstance()->getRouter();
        return $router->assemble($urlOptions, $name, $reset, $encode);
    }
}