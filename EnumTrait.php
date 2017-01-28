<?php

namespace hjcostabr\phpEnum;

/**
 * Contains the methods to create the ENUM facility inside PHP.
 *
 * @namespace hjcostabr\phpEnum
 * @author: hjCostaBR
 */
trait EnumTrait {

    /** @var array Enum items list. */
    private static $items;

    /** @var bool Flag: Determines if this enum items have already been defined. */
    private static $itemsSet   = false;
    
    /**
     * Return list of enum class array properties for enum items configuration.
     * @return array
     */
    private static function getEnumItemsConfigurationProperties()
    {
        // Get the class properties list
        $reflectionClass    = new \ReflectionClass(__CLASS__);
        $classProperties    = $reflectionClass->getProperties();
        
        // Instantiate the return array
        $properties = [];

        // Fill returned properties array
        foreach ($classProperties as $classProperty) {
            
            if ($classProperty->isPrivate() && !$classProperty->isStatic() ) {
                $properties[]   = $classProperty->name;
                continue;
            }
        }
        
        return $properties;
    }


    /**
     * Check if this enum items have been set and, if not, set them.
     *
     * @return void
     * @throws EnumException
     */
    private static function checkItemsSet()
    {
        // Check if this enum items are already set
        if (self::$itemsSet) {
            return;
        }

        // Define enum items
        self::setItems();
    }

    /**
     * Define the enum items:
     * Transform each enum property (array) into an instance of the defined enum items class.
     *
     * @return void
     * @throws EnumException
     */
    private static function setItems()
    {
        // Get the list of the enum class properties
        $enumProperties = get_class_vars(__CLASS__);

        // Get list of enum constants
        $reflection     = new \ReflectionClass(__CLASS__);
        $enumConstants  = $reflection->getConstants();

        // Get list of enum class array properties for enum items configuration
        $enumItemsConfigurationProperties   = self::getEnumItemsConfigurationProperties();

        // Instantiate the list of enum item class array properties to be transformed into enum item instances
        $enumItemsProperties    = array();

        // Build the list of enum items properties
        foreach ($enumConstants as $enumConstantName=>$enumConstantValue) {

            // Instantiate item's configuration values array
            $value  = array();

            // Validate enum item configuration array property (if necessary)
            if (in_array($enumConstantName, $enumItemsConfigurationProperties)) {

                if (!is_array($enumProperties[$enumConstantName])) {
                    throw new EnumException("Invalid enum item array property: '$enumConstantName' in '".__CLASS__."'", EnumException::INVALID_ENUM_ITEM_PROPERTY, __FILE__, __LINE__);
                }

                // Set the item configuration values array
                $value  = $enumProperties[$enumConstantName];
            }

            // Set the prepared enum item configuration array property
            $value["code"]  = $enumConstantValue;
            $enumItemsProperties[]  = $value;
        }

        // Instantiate the enum items
        self::instantiateEnumItems($enumItemsProperties);

        // Update the enum items definition status flag
        self::$itemsSet   = TRUE;
    }

    /**
     * Validate the enum items configuration array properties list and instantiate the enum items.
     *
     * @param array $enumItemsProperties List of enum items array properties witch are supposed to be valid enum items configuration data.
     * @return void
     * @throws EnumException
     */
    private static function instantiateEnumItems(array $enumItemsProperties)
    {
        // Check if is there a list to validate
        if (count($enumItemsProperties) == 1) {
            self::$items[ $enumItemsProperties[0]["code"] ] = new EnumItem($enumItemsProperties[0]);
            return;
        }


        /*============================================================================*/
        /*== Prepare lists for validation ============================================*/

        // List of enum item properties (every enum item must have exactly the same properties)
        $itemsProperties    = array();

        // List of the enum items defined values for each of its properties
        $itemsPropertyValues    = array();


        /*============================================================================*/
        /*== Read the first enum item to define the validation rules for the others ==*/

        // Instantiate the first enum item
        self::$items[ $enumItemsProperties[0]["code"] ]  = new EnumItem($enumItemsProperties[0]);

        foreach ($enumItemsProperties[0] as $itemPropertyName=>$itemPropertyValue) {

            // Add enum item property name
            $itemsProperties[]  = $itemPropertyName;

            // Add defined enum item property value
            $itemsPropertyValues[$itemPropertyName][]   = $itemPropertyValue;
        }


        /*============================================================================*/
        /*== Validate each one of the other enum items ===============================*/
        for ($i = 1; $i < count($enumItemsProperties); $i++) {

            // Validate enum item properties
            foreach ($itemsProperties as $itemsProperty) {
                if (!isset($enumItemsProperties[$i][$itemsProperty])
                    || in_array($enumItemsProperties[$i][$itemsProperty], $itemsPropertyValues[$itemsProperty])
                ) {
                    throw new EnumException("Enum item configuration arrays are not properly defined in '".__CLASS__."''", EnumException::INVALID_ENUM_ITEM_PROPERTY, __FILE__, __LINE__);
                }
            }

            // Instantiate the enum item
            self::$items[ $enumItemsProperties[$i]["code"] ]  = new EnumItem($enumItemsProperties[$i]);
        }
    }


    /**
     * Return a enum item found by its reference (the value of the enum class constant related to the requested item).
     *
     * @param int $itemReference Requested item code.
     * @return EnumItem
     */
    public static function get(int $itemReference)
    {
        // Certifies that the enum items are set
        self::checkItemsSet();

        // Return the requested item
        return isset(self::$items[$itemReference]) ? self::$items[$itemReference] : null;
    }

    /**
     * Return the list of all enum items.
     * @return array
     */
    public static function getList()
    {
        self::checkItemsSet();
        return self::$items;
    }

    /**
     * Return an enum item found by the value of one property.
     *
     * @param string $propertyName The name of the property witch is gone to be used as a filter for the search.
     * @param mixed $propertyValue The value of teh defined property to be searched.
     *
     * @return EnumItem
     * @throws EnumException
     */
    public static function getBy(string $propertyName, $propertyValue)
    {
        // Certifies that the enum items are set
        self::checkItemsSet();

        // Search for the item with the specified name
        foreach (self::$items as $item) {

            if ($item->{$propertyName} == $propertyValue) {
                return $item;
            }
        }
    }
}