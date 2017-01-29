<?php

namespace hjcostabr\phpEnum;

/**
 * Contains the methods to apply an Enum item logic.
 *
 * @namespace hjcostabr\phpEnum
 * @author hjCostaBR
 */
class EnumItem {

    /** @var array List of the enum item properties. */
    private $properties;


    /**
     * Constructor.
     *
     * @param array $properties Items code.
     * @throws EnumException;
     */
    public function __construct(array $properties)
    {
        // Validate code required parameter
        if (!(isset($properties["code"]) && is_numeric($properties["code"]) && $properties["code"] >= 0)) {
            throw new EnumException("Enum item code not defined", EnumException::CODE_NOT_DEFINED, __FILE__, __LINE__);
        }

        // Define enum item properties
        $this->properties   = $properties;
    }


    /**
     * -- MAGIC METHOD --
     * Return an item property.
     *
     * @param string $name Name of the property to return;
     * @return mixed
     * @throws EnumException;
     */
    public function __get(string $name)
    {
        // Validate if the requested property exists
        if (!isset($this->properties[$name])) {
            throw new EnumException("Item not found", EnumException::ITEM_NOT_FOUND, __FILE__, __LINE__);
        }

        return $this->properties[$name];
    }
}