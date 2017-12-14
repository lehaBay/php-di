# PHP Dependency Injector

**Simple** and **fast** Dependency Injector. It supports autowire and autoloading.

The main job of Dependency Injector is to Inject Dependencies (whoa!) not to 
configure every single class-service.

To reduce configuration here it comes, **autoloading**

Just go get class by it's **full name** 
from the DependencyInjector. 
Make sure autoloading is on (which is by default) and 
constructor has proper typehints(see example below) 


Of course if some configuration needed it can be done.


# Easy to install with **composer**

```sh
$ composer require fastero/php-di
```

# Usage

```php

$di = new \Fastero\DependencyInjector\DependencyInjector();

$user = $di->get(User::class);

```
As easy as this, no configuration needed just proper typehins for the constructor(see below)


More complete examples:
let's create some classes to work with
```php
class Company{

}
class User{
    public $company;
    public $language;
    //typehint Company says that first parameter expected to be of 
    //type Company and DependencyInjetor can understand this
    public function __construct(Company $userCompany, $language = null)
    {
        $this->company = $userCompany;
        $this->language = $language;
    }
}

class UserFactory{
    public static function create(){
        return new User(new Company(), 'en');
    }
}

```
No configuration needed by default, but if you must
here is how to use utility class **ServiceConfiguration** 
to add some configuration:
```php
$configuration = [
        "services" =>[
            Company::class => ServiceConfiguration::setupClass(Company::class) //service that will create object of Company
                ->get(),//return complete configuration, should be very last call for every definition
            User::class => ServiceConfiguration::setupClass(User::class) //service that will create object of User
                ->addParameterService(Company::class) //first parameter in the constructor will service with name Company::class
                ->addParameterValue('fr') //second parameter will be value "fr"
                ->get(),//return complete configuration
            'forty-two' => ServiceConfiguration::setupClosure(function ($di, $serviceName){//closure will be called and returned value is a service
                        return 42;
                    })
                ->get(),
        ]
    ];
```

Using **ServiceConfiguration** is recommended way of creating configuration 
for services even though actual configuration is a simple array and can be 
created manually but this utility class helps to avoid mistakes.
Using this class does not create bunch of objects so it has almost no overhead. 
Downside is that one have to call ->get() at the very end of each definition


```php
$configuration = [
        "services" =>[
            Company::class => ServiceConfiguration::setupClass(Company::class) //service that will create object of Company
                ->get(),//return complete configuration, should be very last call for every definition
            User::class => ServiceConfiguration::setupClass(User::class) //service that will create object of User
                ->addParameterService(Company::class) //first parameter in the constructor will service with name Company::class
                ->addParameterValue('fr') //second parameter will be value "fr"
                ->get(),//return complete configuration
            'forty-two' => ServiceConfiguration::setupClosure(function ($di, $serviceName){//closure will be called and returned value is a service
                        return 42;
                    })
                ->get(),
        ]
    ];
    
```
So, creating configuration must be started with one of the **->setup\*(..)** methods and finished with **->get()** method which actually returns configuration array

```php
$di = new Fastero\DependencyInjector\DependencyInjector($configuration);

//if you need to define some service after configuration is set there is a way
//here we create service type factory but sure thing it can be any supported type
$UserEngServiceConfiguration =
    ServiceConfiguration::setupFactory([UserFactory::class, 'create'])// call_user_function_array([UserFactory::class, 'create'], $params) will be called and return value is a service
        ->get();

$di->setServiceConfiguration('UserEng', $UserEngServiceConfiguration);
$service = $di->get(User::class );

var_dump($service instanceof User);
var_dump($service->language);

$service = $di->get("UserEng" );
var_dump($service instanceof User);
var_dump($service->language);

$service = $di->get("forty-two");
var_dump($service);
```
result:

```
bool(true)
string(2) "fr"
bool(true)
string(2) "en"
int(42)

```


MIT Licensed, http://www.opensource.org/licenses/MIT
