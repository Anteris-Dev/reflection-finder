<?php

namespace Anteris\ReflectionFinder\LoadingStrategy;

use Anteris\ReflectionFinder\Collection\ReflectionCollection;
use Anteris\ReflectionFinder\Reflection\ReflectionClass as ReflectionReflectionClass;
use ReflectionClass;
use Symfony\Component\Finder\Finder;
use Throwable;

class Psr4LoadingStrategy implements LoadingStrategyInterface
{
    public function resolve(string $directory): ReflectionCollection
    {
        $files      = Finder::create()->name('*.php')->in($directory);
        $collection = new ReflectionCollection;

        foreach ($files as $file) {
            $contents  = $file->getContents();
            $fromPos   = strpos($contents, 'namespace') + 9;
            $toPos     = strpos($contents, ';', $fromPos);
            $namespace = trim(substr($contents, $fromPos, ($toPos - $fromPos)));
            $fqdnClass = "{$namespace}\\{$file->getFilenameWithoutExtension()}";

            try {
                $collection[] = new ReflectionReflectionClass($fqdnClass);
            } catch (Throwable $error)
            {}
        }

        return $collection;
    }
}
