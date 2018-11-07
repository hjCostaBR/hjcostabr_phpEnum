<?php

namespace hjcostabr\phpEnum;

/**
 * Exception to be thrown in case of enum package related error.
 * 
 * @namespace hjcostabr\phpEnum
 * @author hjcostabr
 */
class EnumException extends \Exception
{
    const INVALID_ENUM_ELEMENT_PROPERTY = 1;
    const CODE_NOT_DEFINED = 2;
    const ELEMENT_NOT_FOUND = 3;

    /**
     * EnumException constructor.
     * 
     * @param string $message
     * @param int    $code
     * @param string $file
     * @param int    $line
     */
    public function __construct(string $message, int $code, string $file, int $line)
    {
        parent::__construct($message, $code);
        
        $this->file = $file;
        $this->line = $line;
    }
}
