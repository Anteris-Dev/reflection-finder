<?php

namespace Anteris\ReflectionFinder\Collection;

use Ramsey\Collection\Collection;
use ReflectionClass;

class ReflectionCollection extends Collection
{
    public function __construct(array $data = [])
    {
        parent::__construct(ReflectionClass::class, $data);
    }
}
