<?php
 
class Js_Application_Resource_Doctrine extends Zend_Application_Resource_ResourceAbstract
{
    /**
    * Initialize
    */
    public function init()
    {
        $doctrineConfig = $this->getOptions();
 
        if (isset($doctrineConfig['compiled']) 
            && $doctrineConfig['compiled'] == true 
            && file_exists($doctrineConfig['compiled_path'])
        ) {
            require_once $doctrineConfig['compiled_path'];
        } else {
            require_once 'Doctrine.php';
        }

        $loader = Zend_Loader_Autoloader::getInstance();
        $loader->pushAutoloader(array('Doctrine_Core', 'autoload'), 'Doctrine');
        $loader->pushAutoloader(array('Doctrine', 'extensionsAutoload'), 'Doctrine');
        
        Doctrine_Core::setExtensionsPath(AP . '/../library/Js/Doctrine/Extensions');
        
        $manager = Doctrine_Manager::getInstance();
 
        $manager->setAttribute(Doctrine_Core::ATTR_QUOTE_IDENTIFIER, true);
        
        // set models to be autoloaded and not included (Doctrine_Core::MODEL_LOADING_AGGRESSIVE)
        $manager->setAttribute(Doctrine_Core::ATTR_MODEL_LOADING, Doctrine_Core::MODEL_LOADING_CONSERVATIVE);
 
        // enable ModelTable classes to be loaded automatically
        $manager->setAttribute(Doctrine_Core::ATTR_AUTOLOAD_TABLE_CLASSES, true);
 
        // enable validation on save()
        $manager->setAttribute(Doctrine_Core::ATTR_VALIDATE, Doctrine_Core::VALIDATE_ALL);
 
        // enable sql callbacks to make SoftDelete and other behaviours work transparently
        $manager->setAttribute(Doctrine_Core::ATTR_USE_DQL_CALLBACKS, true);
 
        // enable automatic queries resource freeing
        $manager->setAttribute(Doctrine_Core::ATTR_AUTO_FREE_QUERY_OBJECTS, true);
        
        // Allows for accessors to be overloaded for custom logic
        $manager->setAttribute(Doctrine_Core::ATTR_AUTO_ACCESSOR_OVERRIDE, true);
        
        // Set needed Doctrine Extensions
        $doctrineConfig['extensions'] = (isset($doctrineConfig['extensions'])) ? $doctrineConfig['extensions'] : array();
        foreach ($doctrineConfig['extensions'] as $extension) {
            $manager->registerExtension($extension);
        }
    
        // Disabling Doctrine ACL from config
        if (isset($doctrineConfig['acl']['enabled'])) {
            $enabled = (boolean) $doctrineConfig['acl']['enabled'];
            Doctrine_Template_Listener_Acl::setEnabled($enabled);
        }
        
        // connect to database(s)
        $manager->openConnection($doctrineConfig['master'], 'master');
        if (array_key_exists('slave', $doctrineConfig)) {
            foreach ($doctrineConfig['slave'] as $id => $slave) {
                $manager->openConnection($slave, 'slave_'.$id);
            }
        }
 
        // set to utf8
        $manager->connection()->setCharset('utf8');
 
        if (isset($doctrineConfig['cache']) && $doctrineConfig['cache'] == true) {
            $cacheDriver = new Doctrine_Cache_Apc();
 
            $manager->setAttribute(Doctrine_Core::ATTR_QUERY_CACHE, $cacheDriver);
        }
 
        return $manager;
    }
}
