<?php

class Js_Form extends Zend_Form
{
    public $validators = array(
        "NotEmpty" => array(
            "NotEmpty", "breakChainOnFailure" => true, 
            array(
                "messages" => array(Zend_Validate_NotEmpty::IS_EMPTY => "This field is required.")
            )
        ),
        "EmailAddress" => array(
            "EmailAddress", null, 
            array(
                "messages" => array(
                    Zend_Validate_EmailAddress::INVALID            => "Please enter a valid email address.",
                    Zend_Validate_EmailAddress::INVALID_FORMAT     => "Please enter a valid email address.",
                    Zend_Validate_EmailAddress::INVALID_HOSTNAME   => "Please enter a valid email address.",
                    Zend_Validate_EmailAddress::INVALID_LOCAL_PART => "Please enter a valid email address.",
                    Zend_Validate_EmailAddress::INVALID_MX_RECORD  => "Please enter a valid email address.",
                    Zend_Validate_EmailAddress::INVALID_SEGMENT    => "Please enter a valid email address."
                )
            )
        )
    );
}