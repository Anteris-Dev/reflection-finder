# Reflection Finder
Helping you find the classes you're looking for.

## Getting Started
This package brings a Symfony-like finder to reflection classes. It enables you to search and retrieve reflection classes for PHP classes based on certain search parameters (e.g. Namespace, Parent classes, etc.) and allows you to create reflection objects from Filesystem content.

### To Install
`composer require anteris-dev/reflection-finder:dev-master`

## Basic Usage
To get started, create a new instance of the class by calling the static `new()` method or instantiating a new instance of the class.

For example:

```php

use Anteris\ReflectionFinder\Finder;

$finder = new Finder;

// OR

$finder = Finder::new();

```

Upon creating an instance of the finder object, you have the following search methods available to you.

- `class($className)` - Restricts the result returned to classes that have the passed FQDN or shortname.
- `extends($className)` - Restricts the result returned to classes that extend the FQDN of the class passed.
- `filter(callable $callback)` - Allows you to specify a custom filter. The callback is passed one parameter which will be an instance of `\ReflectionClass`. If the function returns true the class will be included, otherwise it will not.
- `hasConstant($constantName)` - Restricts the result returned to classes that have a constant with the name passed.
- `hasMethod($methodName)` - Restricts the result returned to classes that have a method with the name passed.
- `hasProperty($propertName)` - Restricts the result returned to classes that have a property with the name passed.
- `implements($interfaceName)` - Restricts the result returned to classes that implement the FQDN of the interface passed.
- `in(string $directory)` - Kicks off the search for the classes in the directory passed. Returns a collection of ReflectionClasses.
- `namespace($namespace)` - Restricts the result returned to classes that are under the passed namespace.
- `uses($traitName)` - Restricts the result returned to classes that use the FQDN of the trait passed.

See below for an example on how to use these methods.

```php

// Searches for classes with the namespace of "Test" in the current directory
$classes = Finder::new()->namespace('Test')->in(__DIR__);

// Searches for classes with the properties "firstName" AND "lastName" defined
$classes = Finder::new()->property('firstName')->property('lastName')->in(__DIR__);

// Runs a custom check against the reflection class' filename
$classes = Finder::new()
    ->filter(function (\ReflectionClass $reflection) {
        if (str_contains($reflection->getFileName(), 'Test.php')) {
            return false;
        }

        return true;
    })->in(__DIR__);

```

## Filesystem Operations
When the `in()` method is returned, an instance of `Anteris\ReflectionFinder\Collection\ReflectionCollection` is returned. This is an iterable collection that contains instances of `Anteris\ReflectionFinder\Reflection\ReflectionClass`.

`Anteris\ReflectionFinder\Reflection\ReflectionClass` extends `\ReflectionClass` and adds the following methods for your convenience in any reflection utilities you may build. Please note that they could mess up the usability of the class in an instance where you are using PSR-4, etc.

- `copy(string $targetFile, bool $overwriteNewerFiles = false)` - Copies the class file to the specified target file.
- `rename(string $target, bool $overwrite = false)` - Renames the class file to the specified target file.
- `remove()` - Deletes the class from the filesystem.

## Advanced Features
This package uses a `LoadingStrategy` class to determine how to resolve a file object to a ReflectionClass. By default this package ships with a `Psr4LoadingStrategy` class that allows it to pickup the correct class FQDN from any files found with Symfony's finder. You can create your own LoadingStrategy by implementing `Anteris\ReflectionFinder\LoadingStrategyInterface` and passing an instance of it to `Finder::registerLoadingStrategy()`.

For example:

```php

use Anteris\ReflectionFinder\Finder;
use Anteris\ReflectionFinder\LoadingStrategy\LoadingStrategyInterface;

class MyCustomLoadingStrategy implements LoadingStrategyInterface
{
    public function resolve(string $directory): ReflectionCollection
    {
        // ...do something here
    }
}

$finder = Finder::new()->registerLoadingStrategy(new MyCustomLoadingStrategy);

// Use your finder here...

```

For more information, check out the `Anteris\ReflectionFinder\Psr4LoadingStrategy` class.
