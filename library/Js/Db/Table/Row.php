<?php

class Js_Db_Table_Row extends Zend_Db_Table_Row_Abstract
{
    protected $_validateColumns = array();

    protected $_errorMessages = array();

    public function save()
    {
        try {
            return parent::save();
        } catch (Js_Db_ValidationException $e) {
            return false;
        }
    }

    public static function fromArray(array $data)
    {
        $className = get_called_class();

        $instance = new $className;
        $newRow = $instance->getTable()->createRow($data);

        unset($instance);

        return $newRow;
    }

    protected function _insert()
    {
        if (!$this->isValid())
            throw new Js_Db_ValidationException("Invalid data has been supplied.");
    }

    protected function _update()
    {
        if (!$this->isValid())
            throw new Js_Db_ValidationException("Invalid data has been supplied.");
    }

    public function isValid()
    {
        $tests = array();

        foreach ($this->_validateColumns as $columnName) {
            $validationMethod = "_valid" . $this->_toCamelCase($columnName);

            $tests[] = $this->$validationMethod();
        }

        return !in_array(false, $tests);
    }

    public function getErrorMessages()
    {
        return $this->_errorMessages;
    }

    public function hasErrorMessage($field)
    {
        return !isset($this->_errorMessages[$field]);
    }

    protected function _addErrorMessage($field, $message)
    {
        $this->_errorMessages[$field][] = $message;

        return $this;
    }

    protected function _toCamelCase($string)
    {
        $withSpaces = str_replace("_", " ", $string);
        $uppercase  = ucwords($withSpaces);

        return str_replace(" ", "", $uppercase);
    }
}