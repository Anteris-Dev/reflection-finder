<?php

namespace Anteris\Tests\ReflectionFinder;

use Anteris\ReflectionFinder\Finder;
use Anteris\Tests\ReflectionFinder\Examples\TestChildClass;
use Anteris\Tests\ReflectionFinder\Examples\TestClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \Anteris\ReflectionFinder\Finder
 */
class FinderTest extends TestCase
{
    public function test_it_can_find_by_namespace()
    {
        $result = Finder::new()
            ->namespace('Anteris\Tests\ReflectionFinder\Examples')
            ->in(__DIR__);

        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(ReflectionClass::class, $result);
    }
}
