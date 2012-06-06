<?php

class Js_Auth_Storage implements Zend_Auth_Storage_Interface
{
    protected $_cache;
    protected $_id;

    public function __construct()
    {
        $appConfig = new Zend_Config_Ini(AP . '/configs/application.ini');

        $env = empty($_SERVER) ? $_SERVER['AE'] : AE;

        // TODO create new section in the ini for just the Zend_Auth_Result Storage
        // instead of the cache setting
        $cache = $appConfig->{$env}->cache;

        foreach($cache->servers as $server) {
            $servers[] = array('host' => $server->host);
        }

        $this->_cache = new Zend_Cache_Backend_Memcached(
            array(
                'servers' => $servers,
                'compression' => false,
                'automatic_serialization' => true,
                'cache_id_prefix' => 'evilauth',
                'timeout' => 20 // longer connection timeout since this is auth,
            )
        );

        $this->_id = $this->getId();
        $auth = $this->_cache->load($this->_id);
       
    }

    public function isEmpty()
    {
        return ($this->_cache->test($this->_id)) ? false : true;
    }

    public function read()
    {
        $contents = $this->_cache->load($this->_id);

        return $contents;
    }

    public function write($contents)
    {
        $this->_cache->save($contents, $this->_id, array(), (session_cache_expire()*60));
    }

    public function clear()
    {
        $this->_cache->remove($this->_id);
    }
    
    public function getCache()
    {
        return $this->_cache;
    }

    protected function getId()
    {
        return Zend_Session::getId();
    }
}