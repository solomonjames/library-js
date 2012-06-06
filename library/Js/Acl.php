<?php

/**
 * Js_Acl
 *
 * @package    Js
 * @subpackage Js_Acl
 * @author     James Solomon <james@jmsolomon.com>
 * @copyright  2010 James Solomon
 * @license    http://www.jmsolomon.com
 * @link       http://www.jmsolomon.com
 */

/**
 * @see Zend_Acl
 */
require_once 'Zend/Acl.php';

/**
 * Access Control List object
 *
 * This is built on the Proxy Pattern {@see http://en.wikipedia.org/wiki/Proxy_pattern}
 *
 * Flow :
 *      1. Intialize new Zend_Acl obect
 *
 *      2. Set the Model_User object
 *
 *      3. If no cache is available from the session, build the object up
 *         with all of the users rules & users groups rules.
 *
 *         3a. If cache is availabe, all we do is grab it, and restore the
 *             object for isAllowed queries
 *
 *      4. Save the whole Zend_Acl object to the session
 *
 * Features :
 *      1. isAllowed Backtrace : the full list of all isAllowed checks that have
 *         occured, is freely available from Js_Acl::getIsAllowedBacktrace()
 *
 *      2. Caching : Utilizes Zend_Session for caching.  So if the user needs
 *         to get updates to their ACL settings, they just need to log out,
 *         and back in, for them to re-cache their ACL object
 *
 *      3. Js_Acl::isAllowed() : This does not allow for the $role param, that
 *         is available in Zend_Acl.  Why?  Well we want to enforce the idea
 *         that we never want to check rules for a specific group, but only
 *         want to check if that user has access to a certain resource
 *         or privilege.
 *
 * @author James Solomon <james@jmsolomon.com>
 * @uses   Zend_Acl
 * @uses   Zend_Auth_Storage_Session
 */
class Js_Acl
{
    const CACHE_KEY  = "Js_Acl_Registry_User";

    /**
     * @var Js_Acl
     */
    protected static $_instance;

    /**
     * @var Zend_Auth_Storage_Interface
     */
    protected $_storage;

    /**
     * @var Model_User
     */
    protected $_user = null;

    /**
     * This will disable the use of caching
     *
     * @var boolean
     */
    protected $_disableCache = false;

    /**
     * Array of isAllowed calls that have been performed
     *
     * @var array
     */
    protected $_isAllowedBacktrace = array();

    /**
     * @var Zend_Acl
     */
    private $_acl;

    /**
     * Constructor
     *
     * @return void
     */
    private function __construct()
    {
        $this->_acl = new Zend_Acl();
    }

    /**
     * This is where the Proxy Pattern gets implemented, as we are
     * sending any unknown calls into the Zend_Acl object (if they
     * are available in there)
     *
     * @param  string $method The method name that is being called
     * @param  array  $args   The array of args that were apart of the call
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (method_exists($this->_acl, $method)) {
            return call_user_func_array(array($this->_acl, $method), $args);
        }
    }

    /**
     * Singleton
     *
     * @return Js_Acl
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Allows the ability to disable the use of caching
     *
     * @param  boolean $boolean True | false
     * @return App_Acl Provides fluent interface
     */
    public function setDisableCache($boolean)
    {
        $this->_disableCache = (bool) $boolean;
        return $this;
    }
    
    public function initGuest()
    {
        $identity = Doctrine::getTable("Model_User")->getGuest(Doctrine::HYDRATE_RECORD);
        
        $this->setUser($identity);
        
        return $this;
    }
    
    /**
     * This will set the contact model to use, will also check if the
     * cache is empty, and if it is, will rebuild / save it, then load
     * the object from cache
     *
     * @param  Model_User $user Our Model_User object
     * @uses   Js_Acl::getStorage()
     * @uses   Js_Acl::_build()
     * @uses   Js_Acl::restoreFromCache()
     * @return Js_Acl Provides fluent interface
     */
    public function setUser(Model_User $user)
    {
        $isNew = md5($this->_user) != md5($user);
        
        if ($isNew) {
            $this->_user = $user;
        }
        
        if ($this->getStorage()->isEmpty() && $isNew) {
            $this->_build();
        }
        $this->_restoreFromCache();
        
        return $this;
    }

    /**
     * This will clear the Zend_Acl object and will clear the cache
     *
     * @uses   Js_Acl::getStorage()
     * @uses   Zend_Acl::removeRoleAll()
     * @return Js_Acl Provides fluent interface
     */
    public function reset()
    {
        // Clear all from Zend_Acl
        $this->removeRoleAll();

        // Clear the cache
        $this->getStorage()->clear();

        return $this;
    }

    /**
     * Get the current role id from the Model_User object
     *
     * @throws Js_Acl_Exception
     * @return string
     */
    public function getCurrentRole()
    {
        if (null === $this->_user) {
            require_once "Js/Acl/Exception.php";
            throw new Js_Acl_Exception("An instance of Model_User is needed for this ACL.");
        }

        return $this->_user->getRoleId();
    }

    /**
     * Provides the full array of all isAllowed checks that have occured
     *
     * @return array
     */
    public function getIsAlllowedBacktrace()
    {
        return $this->_isAllowedBacktrace;
    }

    /**
     * Grabs the current logged in RoleId, does the isAllowed check,
     * then saves the check to the backtrace array
     *
     * @param  Zend_Acl_Resource_Interface|string $resource
     * @param  string                             $privilege
     * @uses   Zend_Acl::isAllowed()
     * @uses   Zend_Acl::has()
     * @uses   Js_Acl::getCurrentRole()
     * @return boolean
     */
    public function isAllowed($resource = null, $privilege = null)
    {
        $role = $this->getCurrentRole();

        if (!$this->has($resource)) {
            $resource = null;
        }

        $isAllowed = $this->_acl->isAllowed($role, $resource, $privilege);

        $this->_isAllowedBacktrace[] = array(
            'role'      => $role,
            'resource'  => $resource,
            'privilege' => $privilege
        );

        return $isAllowed;
    }

    /**
     * Set the cache object (if not set), and return it
     *
     * @return Zend_Auth_Storage_Interface
     */
    public function getStorage()
    {
        if (null === $this->_storage) {
            if ($this->_disableCache) {
                /**
                 * @see Zend_Auth_Storage_NonPersisten
                 */
                require_once 'Zend/Auth/Storage/NonPersistent.php';
                $this->_storage = new Zend_Auth_Storage_NonPersistent();
            } else {
                /**
                 * @see Zend_Auth_Storage_Session
                 */
                require_once 'Zend/Auth/Storage/Session.php';
                $this->_storage = new Zend_Auth_Storage_Session(self::CACHE_KEY, $this->getCurrentRole());
            }
        }

        return $this->_storage;
    }

    /**
     * Will grab the Zend_Acl object from cache, and set it to the $_acl
     * property so we can query against that object
     *
     * @uses   Js_Acl::getStorage()
     * @return Js_Acl Provides fluent interface
     */
    protected function _restoreFromCache()
    {
        $this->_acl = $this->getStorage()->read();
        return $this;
    }

    /**
     * Will save the current Zend_Acl object to cache
     *
     * @uses   Js_Acl::getStorage()
     * @return Js_Acl Provides fluent interface
     */
    protected function _saveToCache()
    {
        $this->getStorage()->write($this->_acl);
        return $this;
    }

    /**
     * Build will gather the full array of resources, privileges, rules,
     * and populate the Zend_Acl object with them, then save to the cache
     *
     * @uses   Js_Acl::getCurrentRole()
     * @uses   Js_Acl::_fetchRules()
     * @uses   Js_Acl::_saveToCache()
     * @return Js_Acl Provides fluent interface
     */
    protected function _build()
    {
        if (null === $this->_user) {
            require_once "Js/Acl/Exception.php";
            throw new Js_Acl_Exception("An instance of Model_User is needed for this ACL.");
        }

        $resources = array();
        $rules = $this->_fetchRules($this->_user->user_id);
        $role  = $this->getCurrentRole();

        // Add role
        $this->addRole($role);

        foreach($rules as $rule) {
            
            $privilege = $rule['Acl_Privilege']['name'];

            if ($privilege) {
                $resourceParts = explode('/', $privilege);
                $privilege     = array_pop($resourceParts);
                $numResources  = count($resourceParts);

                // Build/add on to the resource tree the parts that are relevant to this rule
                $parent = null;
                for ($i = 1; $i <= $numResources; $i++) {
                    $resource = implode('/', array_slice($resourceParts, 0, $i));
                    if (!isset($resources[$resource])) {
                        $resources[$resource] = true;
                        if (!$this->has($resource)) {
                            $this->addResource($resource, $parent);
                        }
                    }
                    $parent = $resource;
                }
            }

            // Add rule to whitelist or blacklist
            switch ($rule['type']) {
                case Zend_Acl::TYPE_ALLOW:
                    $this->allow($role, $resource, $privilege);
                    break;

                case Zend_Acl::TYPE_DENY:
                default:
                    $this->deny($role, $resource, $privilege);
                    break;
            }
        }

        $this->_saveToCache();
        return $this;
    }

    /**
     * Fetches the rules for a user, and all their groups
     *
     * @param  integer $userId
     * @uses   Js_Acl::_buildGroupsArray()
     * @return array
     */
    protected function _fetchRules($userId)
    {
        $group = Js_Query::create()
            ->select('gm.group_id')
            ->from('Model_Group_Membership gm')
            ->where('gm.user_id = ?', $userId)
            ->fetchOne(array(), Doctrine::HYDRATE_ARRAY);

        $groupId = $group['group_id'];

        $rules = Js_Query::create()
            ->select('rule.type, priv.name, resource.name, rule.group_id')
            ->from('Model_Acl_Rule rule')
            ->leftJoin('rule.Acl_Privilege priv')
            ->where('rule.user_id = ?', $userId)
            ->orWhere('rule.group_id = ?', $groupId)
            ->fetchArray();

        return $rules;
    }
}