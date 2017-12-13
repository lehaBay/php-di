<?php
namespace Fastero\DependencyInjector\Tests\DependencyInjector\Config;

use Fastero\DependencyInjector\Config\ServiceConfiguration;
use Fastero\DependencyInjector\Exception\ConfigurationException;
use PHPUnit\Framework\TestCase;

class ServiceConfigurationTest extends TestCase
{
    public function setUp()
    {
        $reflection = new \ReflectionClass(ServiceConfiguration::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, null);
    }

    public function testServiceConfigurationCreateServiceTypeClass()
    {
        $expected = [
            "class" =>  "some_class_full_name"
        ];
        $sc = ServiceConfiguration::setupClass("some_class_full_name")->get();

        $this->assertEquals($expected,$sc);

    }

    public function testServiceConfigurationCreateServiceTypeClassWithAttributes()
    {
        $someObject = new \stdClass();
        $expected = [
            "class" =>  "some_class_full_name",
            "parameters" => [
                ["type" => "service", "value" => "depend_on_serviced"],
                ["type" => "service", "value" => "depend_on_serviced2"],
                ["type" => "value", "value" => 42],
                ["type" => "value", "value" => $someObject],
            ]
        ];
        $sc = ServiceConfiguration::setupClass("some_class_full_name")
            ->addParameterService("depend_on_serviced")
            ->addParameterService("depend_on_serviced2")
            ->addParameterValue(42)
            ->addParameterValue($someObject)

            ->get();

        $this->assertEquals($expected,$sc);

    }
    public function testServiceConfigurationCreateServiceTypeFactoryWithAttributes()
    {
        $someObject = new \stdClass();
        $expected = [
            "factory" =>  ["class_name", "method_name"],
            "parameters" => [
                ["type" => "service", "value" => "depend_on_serviced"],
                ["type" => "service", "value" => "depend_on_serviced2"],
                ["type" => "value", "value" => 42],
                ["type" => "value", "value" => $someObject],
            ]
        ];
        $sc = ServiceConfiguration::setupFactory(["class_name", "method_name"])
            ->addParameterService("depend_on_serviced")
            ->addParameterService("depend_on_serviced2")
            ->addParameterValue(42)
            ->addParameterValue($someObject)

            ->get();

        $this->assertEquals($expected,$sc);

    }

    public function testServiceConfigurationCreateServiceTypeClosure()
    {
        $closure = function(){
            //dosomethings return service
        };
        $expected = [
            "closure" =>  $closure,
        ];
        $sc = ServiceConfiguration::setupClosure($closure)
            ->get();

        $this->assertEquals($expected,$sc);

    }
    public function testServiceConfigurationCreateServiceTypeClassWithAutowiringTrue()
    {

        $expected = [
            "class" =>  "someClassName",
            "autowire" => true
        ];
        $sc = ServiceConfiguration::setupClass("someClassName")
            ->setAutowire(true)
            ->get();

        $this->assertEquals($expected,$sc);

    }

    public function testServiceConfigurationCreateServiceWithoutFinishingPrevious()
    {

        $this->expectException(ConfigurationException::class);
        $sc = ServiceConfiguration::setupClass("someClassName")
            ->setAutowire(true);

        ServiceConfiguration::setupClass("someOtherClass");



    }

    public function testServiceConfigurationDataBeengResetNextTimeSetupClass()
    {
        ServiceConfiguration::setupFactory(["class_name", "method_name"])
            ->addParameterService("depend_on_serviced")
            ->addParameterService("depend_on_serviced2")
            ->addParameterValue(42)
            ->addParameterValue(56)
            ->setAutowire(true)->get();
        $sc = ServiceConfiguration::setupClass("someClassName")->get();

        $this->assertEquals(["class"=> "someClassName"], $sc);



    }
}