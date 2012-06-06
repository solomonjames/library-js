<?php

class Js_Auth_Adapter implements Zend_Auth_Adapter_Interface
{
    private static $_salt = 'j34asU94m@i0p';
    
    private $_email = '';
    private $_password = '';

    /**
     * Sets email address and password for authentication
     *
     * @param string $emailAddress email address (username) to check for
     * @param string $password     password to check against
     *
     * @return void
     */
    public function __construct($email, $password)
    {
        $this->_email = $email;
        $this->_password = $password;
    }

    /**
     * Performs an authentication attempt
     *
     * @throws Zend_Auth_Adapter_Exception If authentication cannot be performed
     *
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {
        $concat = $this->_password.self::getSalt();

        $sql = "SELECT
                    id
                FROM account
                WHERE
                    email = '{$this->_email}'
                    AND password = MD5(CONCAT(hash, '{$concat}'))";

        $user = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchOne($sql);
        
        // check to make sure the email address and password match
        if (!$user) {
            $error = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
            return new Zend_Auth_Result($error, 0);
        }

        $account = Js_Query::create()->from('account')->where('id = ?', $user['id'])->fetchOne();
        
        // return a valid login
        return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $account);
    }
    
    public static function getSalt()
    {
        return self::$_salt;
    }
    
    public static function generateHash($length = 25, $includeAlpha = true, $includeNumeric = true, $includeSpecial = true)
    {
        $use = array();
        $hash = '';

        $alpha = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numeric = '0123456789';
        $special = '!@#$%^*_-+=?|:';

        if ($includeAlpha) {
            $use[] = $alpha;
        }

        if ($includeNumeric) {
            $use[] = $numeric;
        }

        if ($includeSpecial) {
            $use[] = $special;
        }

        for ($i = 0; $i < $length; $i++) {
            $max = strlen($use[$i % count($use)]) - 1;
            $hash .= substr($use[$i % count($use)], rand(0, $max), 1);
        }

        return $hash;
    }
}
