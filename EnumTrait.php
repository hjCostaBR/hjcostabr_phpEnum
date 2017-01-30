<?php

namespace hjcostabr\phpEnum;

/**
 * Contains the methods to create the ENUM facility inside PHP.
 *
 * @namespace hjcostabr\phpEnum
 * @author hjCostaBR
 */
trait EnumTrait {

    /** @var array Enum items list. */
    private static $elements;


    /**
     * Check if this enum elements have been set and, if not, set them.
     *
     * @return void
     * @throws EnumException
     */
    private static function checkElementsSet()
    {
        // Check if this enum elements are already set
        if (is_array(self::$elements)) {
            return;
        }

        // Define enum elements
        self::setElements();
    }

    /**
     * Define the enum elements:
     * Transform each enum element configuration properties array into an enum element class instance.
     *
     * @return void
     * @throws EnumException
     */
    private static function setElements()
    {
        // Get the list of the enum class properties
        $enumProperties = get_class_vars(__CLASS__);

        // Get list of enum constants
        $reflection     = new \ReflectionClass(__CLASS__);
        $enumConstants  = $reflection->getConstants();

        // Get list of enum class array properties for enum items configuration
        $enumElementsConfigurationProperties    = self::getEnumElementsConfigurationProperties();

        // Instantiate the list of enum element class array properties to be transformed into enum element instances
        $enumElementsProperties = array();

        // Build the list of enum items properties
        foreach ($enumConstants as $enumConstantName=>$enumConstantValue) {

            // Instantiate element's configuration values array
            $value  = array();

            // Validate enum element configuration array property (if necessary)
            if (in_array($enumConstantName, $enumElementsConfigurationProperties)) {

                if (!is_array($enumProperties[$enumConstantName])) {
                    throw new EnumException("Invalid enum element array property: '$enumConstantName' in '".__CLASS__."'", EnumException::INVALID_ENUM_ELEMENT_PROPERTY, __FILE__, __LINE__);
                }

                // Set the element configuration values array
                $value  = $enumProperties[$enumConstantName];
            }

            // Set the prepared enum element configuration array property
            $value["code"]  = $enumConstantValue;
            $enumElementsProperties[]  = $value;
        }

        // Instantiate the enum elements
        self::instantiateEnumElements($enumElementsProperties);
    }
    
    /**
     * Return list of enum class array properties for enum elements configuration.
     * @return array
     */
    private static function getEnumElementsConfigurationProperties()
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
            }
        }
        
        return $properties;
    }

    /**
     * Validate the enum elements configuration array properties list and instantiate the enum elements.
     *
     * @param array $enumElementsProperties List of enum elements array properties witch are supposed to be valid enum elements configuration data.
     * @return void
     * @throws EnumException
     */
    private static function instantiateEnumElements(array $enumElementsProperties)
    {
        // Check if is there a list to validate
        if (count($enumElementsProperties) == 1) {
            self::$elements[ $enumElementsProperties[0]["code"] ] = new EnumElement($enumElementsProperties[0]);
            return;
        }


        /*===============================================================================*/
        /*== Prepare lists for validation ===============================================*/

        // List of enum element properties (every enum element must have exactly the same properties)
        $elementsProperties    = array();

        // List of the enum elements defined values for each of its properties
        $elementsPropertyValues = array();


        /*===============================================================================*/
        /*== Read the first enum element to define the validation rules for the others ==*/

        // Instantiate the first enum element
        self::$elements[ $enumElementsProperties[0]["code"] ]  = new EnumElement($enumElementsProperties[0]);

        foreach ($enumElementsProperties[0] as $elementPropertyName=>$elementPropertyValue) {

            // Add enum element property name
            $elementsProperties[]  = $elementPropertyName;

            // Add defined enum element property value
            $elementsPropertyValues[$elementPropertyName][]   = $elementPropertyValue;
        }


        /*===============================================================================*/
        /*== Validate each one of the other enum elements ===============================*/
        for ($i = 1; $i < count($enumElementsProperties); $i++) {

            // Validate enum element properties
            foreach ($elementsProperties as $elementsProperty) {
                if (!isset($enumElementsProperties[$i][$elementsProperty])
                    || in_array($enumElementsProperties[$i][$elementsProperty], $elementsPropertyValues[$elementsProperty])
                ) {
                    throw new EnumException("Enum element configuration arrays are not properly defined in '".__CLASS__."''", EnumException::INVALID_ENUM_ELEMENT_PROPERTY, __FILE__, __LINE__);
                }
            }

            // Instantiate the enum element
            self::$elements[ $enumElementsProperties[$i]["code"] ]  = new EnumElement($enumElementsProperties[$i]);
        }
    }


    /**
     * Return a enum element found by its reference (the value of the enum class constant related to the requested element).
     *
     * @param int $elementReference Requested element code.
     * @return EnumElement
     */
    public static function get(int $elementReference)
    {
        // Certifies that the enum elements are set
        self::checkElementsSet();

        // Return the requested element
        return isset(self::$elements[$elementReference]) ? self::$elements[$elementReference] : null;
    }

    /**
     * Return the list of all enum elements.
     * @return array
     */
    public static function getList()
    {
        self::checkElementsSet();
        return self::$elements;
    }

    /**
     * Return an enum element found by the value of one property.
     *
     * @param string $propertyName The name of the property witch is going to be used as a filter for the search.
     * @param mixed $propertyValue The value of the defined property to be searched.
     *
     * @return EnumElement
     * @throws EnumException
     */
    public static function getBy(string $propertyName, $propertyValue)
    {
        // Certifies that the enum elements are set
        self::checkElementsSet();

        // Search for the element with the specified name
        foreach (self::$elements as $item) {

            if ($item->{$propertyName} == $propertyValue) {
                return $item;
            }
        }
    }
}