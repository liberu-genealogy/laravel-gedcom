<?php

namespace Asdfx\LaravelGedcom\Concerns;

use ReflectionClass;

trait AccessesProtectedProperties {
    public function accessProtected($obj, $prop) {
        $reflection = new ReflectionClass($obj);
        $property = $reflection->getProperty($prop);
        $property->setAccessible(true);
        return $property->getValue($obj);
    }
}