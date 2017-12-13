<?php


namespace Fastero\DependencyInjector\Config;


class ServiceConfiguration extends AbstractGenerator
{
    protected $parameters = [];

    /**
     * create service that represents some Class
     * @param $classFullName - class to be created and returned by DI
     * @return static
     */
    public static function setupClass($classFullName){
        $me = static::getInstance();
        $me->resultData["class"] = $classFullName;
        return $me;
    }

    /**
     * @param $callable - any callable supported by call_user_function_array()
     * it should return a complete service whatever it is
     * @return static
     */
    public static function setupFactory($callable){
        $me = static::getInstance();
        $me->resultData["factory"] = $callable;
        return $me;
    }

    /**
     * @param $closure - a closure function which receives two parameters: dependencyInjector object and
     * requested service name. Must return service;
     * @return static
     */
    public static function setupClosure($closure){
        $me = static::getInstance();
        $me->resultData["closure"] = $closure;
        return $me;
    }

    /**
     * if set to true and no parameters set then constructor and factory parameters will
     * be resolved automatically
     *
     * @param bool $autowire
     */
    public function setAutowire(bool $autowire){
        $this->resultData["autowire"] = $autowire;
        return $this;
    }

    /**
     * add constructor parameter which will be resolved as a service
     * before used as a constructor parameter
     * @param $serviceName
     * @return ServiceConfiguration
     */
    public function addParameterService($serviceName){
        $this->parameters[] = ['type' => 'service', 'value' => $serviceName];
        return $this;
    }

    /**
     * add constructor parameter which will be used as is
     * @param $value
     * @return ServiceConfiguration
     */
    public function addParameterValue($value){
        $this->parameters[] = ['type' => 'value', 'value' => $value];
        return $this;
    }

    protected function reset()
    {
        parent::reset();
        $this->parameters = [];
    }

    public function get(){

        if(!empty($this->parameters)) $this->resultData['parameters'] = $this->parameters;

        return parent::get();
    }

}