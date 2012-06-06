<?php

class Js_Db_Table extends Zend_Db_Table_Abstract
{
    public static function direct()
    {
        $calledClass = get_called_class();
        return new $calledClass();
    }
}