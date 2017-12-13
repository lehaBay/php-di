<?php
namespace Fastero\DependencyInjector\Tests\DependencyInjector;

use Fastero\DependencyInjector\DependencyInjector;
use Fastero\DependencyInjector\Exception\LoadServiceException;
use Fastero\DependencyInjector\Tests\Fixtures\ClassWithCorrectDependencies;
use Fastero\DependencyInjector\Tests\Fixtures\ClassWithoutConstructor;
use Fastero\DependencyInjector\Tests\Fixtures\ClassWithoutDependencies;
use Fastero\DependencyInjector\Tests\Fixtures\ClassWithoutDependenciesTypehints;
use Fastero\DependencyInjector\Tests\Fixtures\DumpFactory;
use Fastero\DependencyInjector\Tests\Fixtures\FactoryForClassWithNoConstructor;
use PHPUnit\Framework\TestCase;

/**
 * @author Alexey Fomin <fominleha@gmail.com>
 * @package Fastero\DependencyInjector
 */
class DependencyInjectorTest extends TestCase
{
    public $diConfiguration;
    public $nonExistentClassName = "DumpClassName_______o";
    public static function setUpBeforeClass(){
        require_once ($_ENV["TESTS_BASE_DIR"] . "/Fixtures/DumpClasses.php");
    }

    public function setUp(){
        $this->diConfiguration = [
          "autoload" => false,
          "services" => [
            ClassWithoutDependencies::class => [
                "class" => ClassWithoutDependencies::class,
            ],
            ClassWithCorrectDependencies::class => [
            "class" => ClassWithCorrectDependencies::class,
              "parameters" =>[
                  ["type" => "service", "value" => ClassWithoutDependencies::class]
              ]
            ],
              ClassWithoutConstructor::class => [
                  "class" => ClassWithoutConstructor::class,
              ],
              ClassWithoutDependenciesTypehints::class => [
                  "class" => ClassWithoutDependenciesTypehints::class,
                  "parameters" =>[
                      ["type" => "service", "value" => ClassWithoutDependencies::class]
                  ]
              ]


          ],
          "aliases"=>[]
        ];
    }

    public function testDependencyInjectorGetAutoloadedServiceThatDoesNotExists(){
        $this->expectException(LoadServiceException::class);
        $this->expectExceptionMessage(sprintf('Unknown service "%s"', $this->nonExistentClassName));
        $di = new DependencyInjector();
        $di->get($this->nonExistentClassName);

    }

    public function testDependencyInjectorGetAutoloadedServiceWithoutCorrectTypehint(){
        $this->expectException(LoadServiceException::class);
        $this->expectExceptionMessage(sprintf('Can\'t autoload "%s" service because constructor parameter "user" is required but typehint is not a class name', ClassWithoutDependenciesTypehints::class));
        $di = new DependencyInjector();
        $di->get(ClassWithoutDependenciesTypehints::class);

    }

    public function testDependencyInjectorGetConfiguredServiceWithoutCorrectTypehint(){

        $di = new DependencyInjector($this->diConfiguration);
        $service = $di->get(ClassWithoutDependenciesTypehints::class);
        $this->assertInstanceOf(ClassWithoutDependenciesTypehints::class, $service);
    }

    public function testDependencyInjectorGetNoConfigWithoutDependencies(){
        $di = new DependencyInjector();
        $service = $di->get(ClassWithoutDependencies::class);
        $this->assertInstanceOf(ClassWithoutDependencies::class, $service);
    }

    public function  testDependencyInjectorGetWithConfigurationFactory(){
        $this->diConfiguration['services']["withFacotory"] = [
            'factory' => [FactoryForClassWithNoConstructor::class, 'factory']
        ];
        $di = new DependencyInjector($this->diConfiguration);
        $service = $di->get("withFacotory");
        $this->assertInstanceOf(ClassWithoutConstructor::class, $service);
    }

    public function  testDependencyInjectorGetWithConfigurationFactoryWithParameters(){
        $this->diConfiguration['services']["withFacotory"] = [
            'factory' => [DumpFactory::class, 'factory'],
            'parameters' => [
                ['type' => "service", 'value' => ClassWithoutDependencies::class]
            ]
        ];
        $di = new DependencyInjector($this->diConfiguration);
        $service = $di->get("withFacotory");
        $this->assertInstanceOf(ClassWithoutDependencies::class, $service);

    }

    public function  testDependencyInjectorGetWithConfigurationClosure(){
        $this->diConfiguration['services']["withClosure"] = [
            'closure' => function($di, $serviceName){
                return new ClassWithCorrectDependencies($di->get(ClassWithoutDependencies::class),$serviceName);
            }
        ];
        $di = new DependencyInjector($this->diConfiguration);
        $service = $di->get("withClosure");
        $this->assertInstanceOf(ClassWithCorrectDependencies::class, $service);
        $this->assertEquals("withClosure", $service->name);
    }

    public function  testDependencyInjectorGetWithConfigurationWrongServiceType(){
        $this->expectException(LoadServiceException::class);
        $this->expectExceptionMessage(sprintf('Wrong configuration for service "withWrongServiceType"'));

        $this->diConfiguration['services']["withWrongServiceType"] = [
            'bublic' => function($di, $serviceName){
                return nul;
            }
        ];
        $di = new DependencyInjector($this->diConfiguration);
        $service = $di->get("withWrongServiceType");

    }
    public  function  testDependencyInjectorGetNotConfiguredWithAutoloadFalse(){
        $this->expectException(LoadServiceException::class);
        $this->expectExceptionMessage(sprintf('Unknown service "%s"', ClassWithoutDependencies::class));
        $di = new DependencyInjector(['autoload' => $this->diConfiguration['autoload']]);
        $di->get(ClassWithoutDependencies::class);
    }

    public function  testDependencyInjectorGetWithConfigurationAutoloadFalse(){
        $di = new DependencyInjector($this->diConfiguration);
        $service = $di->get(ClassWithoutDependencies::class);
        $this->assertInstanceOf(ClassWithoutDependencies::class, $service);
    }

    public function  testDependencyInjectorGetWithConfigurationAndParameters(){
        $di = new DependencyInjector($this->diConfiguration);
        $service = $di->get(ClassWithCorrectDependencies::class);
        $this->assertInstanceOf(ClassWithCorrectDependencies::class, $service);
    }
    public function  testDependencyInjectorGetWithConfigurationAndParameterTypeValue(){
        $this->diConfiguration['services'][ClassWithCorrectDependencies::class]['parameters'][] =
            ['type' => 'value', 'value' => 42];
        $di = new DependencyInjector($this->diConfiguration);
        $service = $di->get(ClassWithCorrectDependencies::class);
        $this->assertInstanceOf(ClassWithCorrectDependencies::class, $service);
        $this->assertEquals(42, $service->name);
    }
    public function  testDependencyInjectorGetWithConfigurationAutowire(){
        $this->diConfiguration["services"]["withAutowire"] = [
            "class" => ClassWithCorrectDependencies::class,
            "autowire" => true,
        ];
        $di = new DependencyInjector($this->diConfiguration);
        $service = $di->get("withAutowire");
        $this->assertInstanceOf(ClassWithCorrectDependencies::class, $service);
    }

    public function  testDependencyInjectorGetWithConfigurationRequiredParametersNotSetNoAutowire(){
        $this->expectException(LoadServiceException::class);
        $this->expectExceptionMessage(sprintf('Wrong parameters for service "%s"', "withNoAutowire"));
        $this->diConfiguration["services"]["withNoAutowire"] = [
            "class" => ClassWithCorrectDependencies::class,
            "autowire" => false,
        ];
        $di = new DependencyInjector($this->diConfiguration);
        $service = $di->get("withNoAutowire");

    }
    public function  testDependencyInjectorGetWithConfigurationWrongParameterTypeSetNoAutowire(){
        $this->expectException(LoadServiceException::class);
        $this->expectExceptionMessage(sprintf('Wrong parameters for service "%s"', "withNoAutowire"));
        $this->diConfiguration["services"]["withNoAutowire"] = [
            "class" => ClassWithCorrectDependencies::class,
            "autowire" => false,
            "parameters" => [
                ["type" => "service", "value" =>  ClassWithoutConstructor::class]
            ]
        ];
        $di = new DependencyInjector($this->diConfiguration);
        $service = $di->get("withNoAutowire");

    }
    public function  testDependencyInjectorGetWithConfigurationUnknownParameterType(){
        $this->expectException(LoadServiceException::class);
        $this->expectExceptionMessage(sprintf('Wrong configuration for service "%s"', "withNoAutowire"));
        $this->diConfiguration["services"]["withNoAutowire"] = [
            "class" => ClassWithCorrectDependencies::class,
            "autowire" => false,
            "parameters" => [
                ["type" => "service0", "value" =>  ClassWithoutConstructor::class]
            ]
        ];
        $di = new DependencyInjector($this->diConfiguration);
        $service = $di->get("withNoAutowire");

    }

    public function testDependencyInjectorGetLoadedService(){
        $di = new DependencyInjector();
        $service1 = $di->get(ClassWithoutDependencies::class);
        $service2 = $di->get(ClassWithoutDependencies::class);

        $this->assertSame($service1, $service2);
    }

    public function testDependencyInjectorGetNewReturnNewInstance(){
        $di = new DependencyInjector();
        $service1 = $di->get(ClassWithoutDependencies::class);
        $service2 = $di->getNew(ClassWithoutDependencies::class);
        $this->assertNotSame($service1, $service2);
    }
    public function testDependencyInjectorGetNewReturnNewInstanceAndDoesNotSaveToLoadedServices(){
        $di = new DependencyInjector();
        $service1 = $di->getNew(ClassWithoutDependencies::class);
        $service2 = $di->get(ClassWithoutDependencies::class);
        $this->assertNotSame($service1, $service2);
    }

    public function testDependencyInjectorHasIfClassExists(){
        $di = new DependencyInjector();
        $result = $di->has(ClassWithoutDependencies::class);
        $this->assertTrue($result);

    }

    public function testDependencyInjectorHasIfClassDoesNotExists(){
        $di = new DependencyInjector();
        $result = $di->has("DumpClassName_______o");
        $this->assertFalse($result);
    }


    public function testDependencyInjectorSetServiceConfiguration(){
        $di = new DependencyInjector();
        $di->setServiceConfiguration("some_blahblah_service",
            [
                'class' => ClassWithCorrectDependencies::class,
                'autowire' => true
            ]);
        $service = $di->get("some_blahblah_service");
        $this->assertInstanceOf(ClassWithCorrectDependencies::class, $service);
    }

    public function testDependencyInjectorSetServiceConfigurationReplace(){
        $di = new DependencyInjector(["services"=> ["some_blahblah_servie" =>["class" => "some_blahblah_service"]]]);
        $di->setServiceConfiguration("some_blahblah_service",
            ['class' => ClassWithoutDependencies::class ]);
        $service = $di->get("some_blahblah_service");
        $this->assertInstanceOf(ClassWithoutDependencies::class, $service);
    }

    public function testDependencyInjectorSetAutoloadFalse(){
        $this->expectException(LoadServiceException::class);
        $this->expectExceptionMessage(sprintf('Unknown service "%s"', ClassWithoutDependencies::class));
        $di = new DependencyInjector();
        $di->setAutoload(false);
        $di->get(ClassWithoutDependencies::class);
    }

    public function testDependencyInjectorReplaceService(){

        $di = new DependencyInjector($this->diConfiguration);
        $di->get(ClassWithoutDependencies::class);
        $di->replaceService(ClassWithoutDependencies::class, 33);
        $di->replaceService("fortytwo", 42);
        $service1 = $di->get(ClassWithoutDependencies::class);
        $service2 = $di->get("fortytwo");
        $this->assertEquals(33, $service1);
        $this->assertEquals(42, $service2);

    }
    public function testDependencyAddServiceAlias(){

        $di = new DependencyInjector($this->diConfiguration);
        $di->addServiceAlias(ClassWithoutDependencies::class, "dump_alias", "another_alias");
        $service1 = $di->get(ClassWithoutDependencies::class);
        $service2 = $di->get("dump_alias");
        $service3 = $di->get("another_alias");
        $this->assertSame($service1, $service2);
        $this->assertSame($service1, $service3);


    }

}
