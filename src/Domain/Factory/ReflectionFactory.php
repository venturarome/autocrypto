<?php

namespace App\Domain\Factory;

use ReflectionClass;

abstract class ReflectionFactory
{
    protected function instantiateObject(string $class): object
    {
        if (!class_exists($class)) {
            throw new \DomainException("Class '$class' is not defined.");
        }

        $ref_class = new ReflectionClass($class);
        return $ref_class->newInstanceWithoutConstructor();
    }

    protected function fillObject($object, $parameters = []): void
    {
        $properties_assigned = 0;

        $ref_object = new \ReflectionObject($object);

        $properties = $ref_object->getProperties();
        foreach ($properties as $property) {
            $name = $property->getName();
            if (array_key_exists($name, $parameters)) {
                $is_public = $property->isPublic();
                $property->setAccessible(true);
                $property->setValue($object, $parameters[$name]);
                $property->setAccessible($is_public);
                $properties_assigned++;
            }
        }
        if (count($parameters) !== $properties_assigned) {
            $missing = count($parameters) - $properties_assigned;
            throw new \InvalidArgumentException(
                "Impossible to find $missing parameters on object of class '".get_class($object)."'.");
        }
    }
}