<?php

namespace Anteris\ReflectionFinder;

use Anteris\ReflectionFinder\Collection\ReflectionCollection;
use Anteris\ReflectionFinder\LoadingStrategy\LoadingStrategyInterface;
use Anteris\ReflectionFinder\LoadingStrategy\Psr4LoadingStrategy;
use ReflectionClass;

class Finder
{
    protected string $namespace;
    protected string $class;
    protected string $extends;

    protected array $implements = [];
    protected array $uses = [];
    protected array $constants = [];
    protected array $properties = [];
    protected array $methods = [];
    protected array $filters = [];

    protected array $loadingStrategies = [];

    /***************************************************************************
     * Class initialization
    ***************************************************************************/

    public function __construct()
    {
        $this->boot();
    }

    /**
     * Registers default loading strategies and filters and if set, calls the
     * hooks into the booting process so you could add your own loading strategies.
     */
    public function boot()
    {
        if (method_exists($this, 'booting')) {
            $this->booting();
        }

        $this->registerLoadingStrategy(new Psr4LoadingStrategy);

        if (method_exists($this, 'booted')) {
            $this->booted();
        }
    }

    /**
     * Creates a brand new instance of the finder.
     */
    public static function new(): Finder
    {
        return (new static);
    }

    /***************************************************************************
     * Finder methods
     ***************************************************************************/

    /**
     * Sets a namespace that we should be looking for.
     */
    public function namespace(string $namespace): Finder
    {
        $clone = clone $this;
        $clone->namespace = $namespace;
        return $clone;
    }

    /**
     * Sets a class name that we should be looking for.
     */
    public function class(string $class): Finder
    {
        $clone = clone $this;
        $clone->class = $class;
        return $clone;
    }

    /**
     * Sets a class name that the class should be extending.
     */
    public function extends(string $extends): Finder
    {
        $clone = clone $this;
        $clone->extends = $extends;
        return $clone;
    }

    /**
     * Adds an interface that the class should be implementing.
     */
    public function implements(string $implements): Finder
    {
        $clone = clone $this;
        $clone->implements[] = $implements;
        return $clone;
    }

    /**
     * Adds a trait that the class should be using.
     */
    public function uses(string $uses): Finder
    {
        $clone = clone $this;
        $clone->uses[] = $uses;
        return $clone;
    }

    /**
     * Adds a constant that the class should have defined.
     */
    public function hasConstant(string $constant): Finder
    {
        $clone = clone $this;
        $clone->constants[] = $constant;
        return $clone;
    }

    /**
     * Adds a property name that the class should have defined.
     */
    public function hasProperty(string $property): Finder
    {
        $clone = clone $this;
        $clone->properties[] = $property;
        return $clone;
    }

    /**
     * Adds a method name that the class should have defined.
     */
    public function hasMethod(string $method): Finder
    {
        $clone = clone $this;
        $clone->methods[] = $method;
        return $clone;
    }

    /**
     * Adds a custom filter method.
     */
    public function filter(callable $callback): Finder
    {
        $clone = clone $this;
        $clone->filters[] = $callback;
        return $clone;
    }

    /**
     * Runs the search against the passed directory.
     */
    public function in($directory): ReflectionCollection
    {
        $classes = new ReflectionCollection;

        foreach ($this->loadingStrategies as $loadingStrategy) {
            $classes = $classes->merge($loadingStrategy->resolve($directory));
        }

        $classes = $classes->filter(function (ReflectionClass $reflection) {
            if (! $this->filterNamespace($reflection)) {
                return false;
            }

            if (! $this->filterClassName($reflection)) {
                return false;
            }

            if (! $this->filterExtends($reflection)) {
                return false;
            }
            
            if (! $this->filterImplements($reflection)) {
                return false;
            }

            if (! $this->filterUses($reflection)) {
                return false;
            }

            if (! $this->filterConstants($reflection)) {
                return false;
            }

            if (! $this->filterProperties($reflection)) {
                return false;
            }

            if (! $this->filterMethods($reflection)) {
                return false;
            }

            return true;
        });

        foreach ($this->filters as $callback) {
            $classes = $classes->filter($callback);
        }

        return $classes;
    }

    /***************************************************************************
     * Loading Strategies
     ***************************************************************************/

    /**
     * Returns the strategy we are using to resolve reflection classes from the filesystem.
     */
    public function getLoadingStrategies()
    {
        return $this->loadingStrategies;
    }

    /**
     * Registers a loading strategy we will use to resolve reflection classes
     * from the filesystem.
     */
    public function registerLoadingStrategy(LoadingStrategyInterface $strategy)
    {
        $this->loadingStrategies[] = $strategy;
    }

    /***************************************************************************
     * In-house filter callbacks
     ***************************************************************************/

    protected function filterNamespace(ReflectionClass $reflection): bool
    {
        if (! isset($this->namespace)) {
            return true;
        }

        if ($reflection->getNamespaceName() == $this->namespace) {
            return true;
        }

        return false;
    }

    protected function filterClassName(ReflectionClass $reflection): bool
    {
        if (! isset($this->class)) {
            return true;
        }

        if ($reflection->getShortName() == $this->class) {
            return true;
        }

        if ($reflection->getName() == $this->class) {
            return true;
        }

        return false;
    }

    protected function filterExtends(ReflectionClass $reflection): bool
    {
        if (! isset($this->extends)) {
            return true;
        }

        $parent = $reflection->getParentClass();

        if ($parent && $parent->getName() == $this->extends) {
            return true;
        }

        return false;
    }

    protected function filterImplements(ReflectionClass $reflection): bool
    {
        if (! isset($this->implements) || empty($this->implements)) {
            return true;
        }

        $implements = $reflection->getInterfaceNames();

        foreach ($this->implements as $implementation) {
            if (! in_array($implementation, $implements)) {
                return false;
            }
        }

        return true;
    }

    protected function filterUses(ReflectionClass $reflection): bool
    {
        if (! isset($this->uses) || empty($this->uses)) {
            return true;
        }

        $traits = $reflection->getTraitNames();

        foreach ($this->uses as $use) {
            if (! in_array($use, $traits)) {
                return false;
            }
        }

        return true;
    }

    protected function filterConstants(ReflectionClass $reflection): bool
    {
        if (!isset($this->constants) || empty($this->constants)) {
            return true;
        }

        foreach ($this->constants as $constant) {
            if (!$reflection->hasConstant($constant)) {
                return false;
            }
        }

        return true;
    }

    protected function filterProperties(ReflectionClass $reflection): bool
    {
        if (! isset($this->properties) || empty($this->properties)) {
            return true;
        }

        foreach ($this->properties as $property) {
            if (! $reflection->hasProperty($property)) {
                return false;
            }
        }

        return true;
    }

    protected function filterMethods(ReflectionClass $reflection): bool
    {
        if (!isset($this->methods) || empty($this->methods)) {
            return true;
        }

        foreach ($this->methods as $method) {
            if (! $reflection->hasMethod($method)) {
                return false;
            }
        }

        return true;
    }
}
