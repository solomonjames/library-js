<?php

/**
 * @link https://gist.github.com/1340365
 */
class Js_Bijective
{
    protected static $_instance = null;

    protected $_dictionary = "JK6nY23q8RMB9bZEyoAOtNHarCfPvgD51UkwFShl4mzcuiV7sTxeLj0IdXpQGW";

    protected function __construct()
    {
        $this->_dictionary = str_split($this->_dictionary);
    }

    public function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function encode($i)
    {
        if ($i == 0)
            return $this->_dictionary[0];

        $result = '';
        $base = count($this->_dictionary);

        while ($i > 0)
        {
            $result[] = $this->_dictionary[($i % $base)];
            $i = floor($i / $base);
        }

        $result = array_reverse($result);

        return join("", $result);
    }

    public function decode($input)
    {
        $i = 0;
        $base = count($this->_dictionary);

        $input = str_split($input);

        foreach($input as $char)
        {
            $pos = array_search($char, $this->_dictionary);

            $i = $i * $base + $pos;
        }

        return $i;
    }
}