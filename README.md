# PHP Dependency Injector

Simple and fast Dependency Injector. It support autowire and autoloading (can automatically load 
classes that are not in configuration)

It does not support annotations because it seems unreasonable

# Easy to install with **composer**

```sh
$ composer require fastero/php-di
```

#Usage

```php

$di = new \Fastero\DependencyInjector\DependencyInjector();

$kernel = $di->get(\SomeProject\Kernel::class);

```
As easy as this, you don't need to specify any dependencies or any configuration at all it you have proper type hints in the constructor


let's see for example Kernel class:

```php
class Kernel{
    public function __construct(ConfigureManager $configuration){
    
    }
}
```

It will create a $kernel class with ConfigureManager injected in the constructor

#Configure parameters manually


MIT Licensed, http://www.opensource.org/licenses/MIT
