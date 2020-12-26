<?php

namespace Anteris\ReflectionFinder\LoadingStrategy;

use Anteris\ReflectionFinder\Collection\ReflectionCollection;

/**
 * A loading strategy resolves files in a directory to instances of a ReflectionClass
 * and returns these resolved classes in the form of a collection. This is necessary
 * because PHP does not have a default way to get a reflection class from a files contents.
 */
interface LoadingStrategyInterface
{
    public function resolve(string $directory): ReflectionCollection;
}
