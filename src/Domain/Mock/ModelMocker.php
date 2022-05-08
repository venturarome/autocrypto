<?php

namespace App\Domain\Mock;

class ModelMocker
{
    final protected static function bulk($object, $data): void
    {
        $rc = new \ReflectionObject($object);

        foreach ($data as $prop => $value) {
            $rp = self::getFullProperty($rc, $prop);
            $rp->setAccessible(true);
            $rp->setValue($object, $value);
            $rp->setAccessible(false);
        }
    }

    private static function getFullProperty(\ReflectionObject $rc, $prop)
    {
        $parent = $rc->getParentClass();
        if ($parent && $parent->hasProperty($prop) && !$rc->hasProperty($prop)) {
            return $parent->getProperty($prop);
        }
        return $rc->getProperty($prop);
    }
}