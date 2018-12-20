<?php

namespace hjcostabr\phpEnum;

/**
 * Contains the methods to create the ENUM facility inside PHP.
 *
 * @namespace hjcostabr\phpEnum
 * @author hjcostabr
 */
trait EnumTrait {
    
    /** @var array Enum elements list. */
    private static $elements;
    
    
    /**
     * Check if this enum elements have been set and, if not, set them.
     *
     * @return void
     * @throws EnumException
     */
    private static function checkElementsSet(): void
    {
        if (is_array(self::$elements)) return;
        self::setElements();
    }
    
    /**
     * Define the enum elements:
     * Transform each enum element configuration properties array into an enum element class instance.
     *
     * @return void
     * @throws EnumException
     */
    private static function setElements(): void
    {
        // Get enum elements config parameters
        $enumProperties = get_class_vars(__CLASS__);
        $reflection = new \ReflectionClass(__CLASS__);
        $enumConstants = $reflection->getConstants();
        $enumElementsConfigProps = self::getEnumElementsConfigurationProperties();
        
        // Build list of enum elements properties
        $enumElementsProps = [];
        
        foreach ($enumConstants as $enumConstantName => $enumConstantValue) {
            
            $value = [];
            
            // Validate configuration array (if necessary)
            if (in_array($enumConstantName, $enumElementsConfigProps)) {
                
                if (!is_array($enumProperties[$enumConstantName]))
                    throw new EnumException("Invalid enum element array property: '$enumConstantName' in '".__CLASS__."'", EnumException::INVALID_ENUM_ELEMENT_PROPERTY, __FILE__, __LINE__);
                
                // Set element configuration values array
                $value = $enumProperties[$enumConstantName];
            }
            
            // Set prepared enum element configuration array property
            $value['id'] = $enumConstantValue;
            $enumElementsProps[] = $value;
        }
        
        // Instantiate enum elements
        self::instantiateEnumElements($enumElementsProps);
    }
    
    /**
     * Return list of enum class array properties for enum elements configuration.
     * @return array
     */
    private static function getEnumElementsConfigurationProperties()
    {
        $reflectionClass = new \ReflectionClass(__CLASS__);
        $classProperties = $reflectionClass->getProperties();
        
        $properties = [];
        
        foreach ($classProperties as $classProperty) {
            if ($classProperty->isPrivate() && !$classProperty->isStatic())
                $properties[] = $classProperty->name;
        }
        
        return $properties;
    }
    
    /**
     * Validate enum elements configuration array properties list and instantiate enum elements.
     *
     * @param array $enumElementsProps List of enum elements array properties witch are supposed to be valid enum elements configuration data.
     * @return void
     * @throws EnumException
     */
    private static function instantiateEnumElements(array $enumElementsProps): void
    {
        // Check if is there a list to validate
        if (count($enumElementsProps) == 1) {
            self::$elements[$enumElementsProps[0]['id']] = new EnumElement($enumElementsProps[0]);
            return;
        }
        
        $elementsProperties = [];       // Enum element properties (every element must have the same properties)
        $elementsPropertyValues = [];   // Enum elements defined values for each of its properties
        
        // Define validation rules for all elements according to the format of the first element
        self::$elements[$enumElementsProps[0]['id']] = new EnumElement($enumElementsProps[0]);
        
        foreach ($enumElementsProps[0] as $elementPropertyName => $elementPropertyValue) {
            $elementsProperties[] = $elementPropertyName;
            $elementsPropertyValues[$elementPropertyName][] = $elementPropertyValue;
        }
        
        // Instantiate enum elements
        for ($i = 1; $i < count($enumElementsProps); $i++) {
            
            foreach ($elementsProperties as $elementsProperty) {
                if (!isset($enumElementsProps[$i][$elementsProperty])
                    || in_array($enumElementsProps[$i][$elementsProperty], $elementsPropertyValues[$elementsProperty])
                ) {
                    throw new EnumException("Enum element configuration arrays are not properly defined in '".__CLASS__."''", EnumException::INVALID_ENUM_ELEMENT_PROPERTY, __FILE__, __LINE__);
                }
            }
            
            self::$elements[$enumElementsProps[$i]['id']] = new EnumElement($enumElementsProps[$i]);
        }
    }
    
    
    /**
     * Return enum element found by its reference (value of enum class constant related to the requested element).
     *
     * @param mixed $elementReference Requested element code.
     * @return EnumElement
     * @throws EnumException
     */
    public static function get($elementReference): EnumElement
    {
        self::checkElementsSet();
        return isset(self::$elements[$elementReference]) ? self::$elements[$elementReference] : null;
    }
    
    /**
     * Return the list of all enum elements.
     *
     * @return array
     * @throws EnumException
     */
    public static function getList(): array
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
    public static function getBy(string $propertyName, $propertyValue): EnumElement
    {
        self::checkElementsSet();
        
        foreach (self::$elements as $item) {
            if ($item->{$propertyName} == $propertyValue)
                return $item;
        }
    }
}
