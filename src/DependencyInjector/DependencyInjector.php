<?php

namespace Fastero\DependencyInjector;


use Fastero\DependencyInjector\Exception\LoadServiceException;
use Fastero\DependencyInjector\Exception\WrongConfigurationException;


class DependencyInjector
{
    protected $configuration = [];

    /**
     * @var array services that already loaded by the container
     */
    protected $loadedServices = [];
    protected $loaders = [];
    protected $aliases = [];
    protected $services = [];

    /**
     * if true and service with given name does not exist in the container it will interpreted as a
     * class name and automatically defined in the container if the class with this name exists.
     * @var bool
     */
    protected $autoload = true;

    public function __construct($configuration = [])
    {
        !empty($configuration) && $this->replaceConfiguration($configuration);
    }

    public function replaceConfiguration($configuration){
        $this->configuration = $configuration;
        if(isset($configuration['autoload'])){
            $this->autoload = (bool)$configuration['autoload'];
        }
        $this->aliases = $configuration['aliases'] ?? [];
        $this->services = $configuration['services'] ?? [];
    }

    /**
     * return instance of service and save it to the list(cache) so it will be returned
     * again next time ->get called
     * @param $name
     * @return mixed
     */
    public function get($name){
        $realName = $this->aliases[$name] ?? $name;
        if(isset($this->loadedServices[$realName])){
            return $this->loadedServices[$realName];
        }else{
            $this->loadedServices[$realName] = $this->loadService($realName);
            return $this->loadedServices[$realName];
        }

    }

    /**
     * return new instance of service without saving it in a list of retrieved services
     * so this instance will never be returned again by get or getNew
     * @param $name
     * @return mixed
     */
    public function getNew($name){
        $realName = $this->aliases[$name] ?? $name;
        return $this->loadService($realName);
    }

    public function has($name){
        $realName = $this->aliases[$name] ?? $name;
        return isset($this->services[$realName])
            || ($this->autoload && class_exists($realName));
    }

    protected function loadService($serviceName){
        if(isset($this->services[$serviceName])){
            return $this->loadServiceFromConfiguration($serviceName,$this->services[$serviceName]);
        }else if($this->autoload and class_exists($serviceName)){
            return $this->autoLoadClass($serviceName);
        }else{
            throw new LoadServiceException(sprintf("Unknown service \"%s\"", $serviceName));
        }
    }

    protected function loadServiceFromConfiguration($serviceName, $configuration){

        try{
            if(isset($configuration['class'])){
                $constructorParameters = [];
                if(!empty($configuration['parameters'])){
                    $constructorParameters = $this->loadParameters($configuration['parameters']);
                }else if(!empty($configuration['autowire'])){
                    $parameters = $this->getConstructorParameters($configuration['class']);
                    $constructorParameters = !empty($parameters) ? $this->loadParameters($parameters): [];
                }

                return new $configuration['class'](...$constructorParameters);
            }else if(isset($configuration['factory'])){
                $factoryParameters = [];
                if(!empty($configuration['parameters'])){
                    $factoryParameters = $this->loadParameters($configuration['parameters']);
                }
                return call_user_func_array($configuration['factory'], $factoryParameters);
            }else if(isset($configuration['closure'])){
                return call_user_func($configuration['closure'],$this, $serviceName);
            }else{
                throw new WrongConfigurationException(sprintf("Unknown type of service \"%s\"", $serviceName));
            }
        }catch (WrongConfigurationException $exception){
            throw new LoadServiceException(sprintf("Wrong configuration for service \"%s\"", $serviceName), 0, $exception);
        }
    }

    /**
     * gets array of parameters from configuration and return correct array which can be passed to the constructor
     * @param $parametersConfiguration
     * @return array
     */
    protected function loadParameters($parametersConfiguration){
        $result = [];
        foreach ($parametersConfiguration as $name => $parameter){
            if($parameter['type'] == 'value'){
                $result[] = $parameter['value'];
            }else if($parameter['type'] == 'service'){
                $result[] = $this->get($parameter['value']);
            }else{
                throw new WrongConfigurationException(sprintf("wrong parameter(\"%s\") format", $name));
            }
        }
        return $result;
    }

    /**
     * retrieve constructor parameters from constructor's typehints
     * @param $className
     * @return array - array of resolved parameters
     */
    protected function getConstructorParameters($className){
        $classReflection = new \ReflectionClass($className);
        $constructor = $classReflection->getConstructor();
        $parameters = [];
        if(!is_null($constructor)){
            $constructorParameters = $constructor->getParameters();

            foreach ($constructorParameters as $parameter){
                if(!is_null($class = $parameter->getClass())){
                    $parameters[] = ["type" => "service", 'value' => $class->getName()];
                }else if($parameter->isOptional()){
                    break;
                }else{
                    throw new LoadServiceException(
                        sprintf('Can\'t autoload "%s" service because constructor parameter "%s" is required but typehint is not a class name ',
                            $className, $parameter->getName()));
                }
            }
        }
        return $parameters;
    }

    protected function autoLoadClass($className){
        $configuration = [
            'class' => $className,
            'parameters' => $this->getConstructorParameters($className)
        ];
        return $this->loadServiceFromConfiguration($className, $configuration);
    }



}
