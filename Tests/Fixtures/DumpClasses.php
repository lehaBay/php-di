<?php
namespace Fastero\DependencyInjector\Tests\Fixtures;



class ClassWithoutDependenciesTypehints{
    public function __construct($user){
    }
}

class ClassUnableToAutowire{
    public function __construct(ClassWithoutDependencies $class, array $user){
    }
}

class ClassWithWrongTypehint{
    public function __construct(NonExistentClass $user){
    }
}

class ClassWithoutDependencies{
    public function __construct(){
    }
}

class ClassWithoutConstructor{
    public function get1(){
        return 1;
    }
}

class ClassWithCorrectDependencies{
    public $name;
    public function __construct(ClassWithoutDependencies $class, $name = ""){
        $this->name = $name;
    }
}

class FactoryForClassWithNoConstructor{

    public static function factory(){
        return new ClassWithoutConstructor();
    }
}

class DumpFactory{

    public static function factory($serviceToReturn){
        return $serviceToReturn;
    }
}
