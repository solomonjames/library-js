<?php

class Js_Auth_Adapter_DbTable extends Zend_Auth_Adapter_DbTable
{
    protected function _authenticateCreateAuthResult()
    {
        $identity = (Zend_Auth_Result::SUCCESS === $this->_authenticateResultInfo['code']) 
                    ? $this->getResultRowObject()
                    : $this->_authenticateResultInfo['identity'];

        return new Zend_Auth_Result(
            $this->_authenticateResultInfo['code'],
            $identity,
            $this->_authenticateResultInfo['messages']
        );
    }  
}