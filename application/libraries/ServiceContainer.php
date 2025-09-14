<?php

defined('BASEPATH') or exit('No direct script access allowed');

class ServiceContainer
{
    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Service definitions
     */
    protected $services = [];

    /**
     * Resolved services
     */
    protected $resolved = [];

    /**
     * Singleton constructor
     */
    private function __construct()
    {
        $this->registerDefaultServices();
    }

    /**
     * Get singleton instance
     * 
     * @return ServiceContainer
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Bind a service to the container
     * 
     * @param string $name
     * @param callable|string $resolver
     * @param bool $singleton
     * @return void
     */
    public function bind($name, $resolver, $singleton = true)
    {
        $this->services[$name] = [
            'resolver' => $resolver,
            'singleton' => $singleton
        ];

        // Clear any previously resolved instance
        if (isset($this->resolved[$name])) {
            unset($this->resolved[$name]);
        }
    }

    /**
     * Bind a singleton service
     * 
     * @param string $name
     * @param callable|string $resolver
     * @return void
     */
    public function singleton($name, $resolver)
    {
        $this->bind($name, $resolver, true);
    }

    /**
     * Resolve a service from the container
     * 
     * @param string $name
     * @return mixed
     * @throws RuntimeException
     */
    public function resolve($name)
    {
        // Return cached singleton if exists
        if (isset($this->resolved[$name])) {
            return $this->resolved[$name];
        }

        // Check if service is registered
        if (!isset($this->services[$name])) {
            throw new RuntimeException("Service '{$name}' not found in container");
        }

        $service = $this->services[$name];
        $resolver = $service['resolver'];

        // Resolve the service
        if (is_callable($resolver)) {
            $instance = $resolver($this);
        } elseif (is_string($resolver) && class_exists($resolver)) {
            $instance = $this->createInstance($resolver);
        } else {
            throw new RuntimeException("Invalid resolver for service '{$name}'");
        }

        // Cache if singleton
        if ($service['singleton']) {
            $this->resolved[$name] = $instance;
        }

        return $instance;
    }

    /**
     * Check if service is registered
     * 
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return isset($this->services[$name]);
    }

    /**
     * Create instance with dependency injection
     * 
     * @param string $className
     * @return object
     * @throws ReflectionException
     */
    protected function createInstance($className)
    {
        $reflection = new ReflectionClass($className);

        $constructor = $reflection->getConstructor();

        if (!$constructor) {
            return new $className();
        }

        $parameters = $constructor->getParameters();
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();

            if ($type && !$type->isBuiltin()) {
                $dependencyClass = $type->getName();

                // Try to resolve from container first
                if ($this->has($dependencyClass)) {
                    $dependencies[] = $this->resolve($dependencyClass);
                } else {
                    // Auto-wire dependency
                    $dependencies[] = $this->createInstance($dependencyClass);
                }
            } elseif ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
            } else {
                throw new RuntimeException("Cannot resolve dependency '{$parameter->getName()}' for class '{$className}'");
            }
        }

        return $reflection->newInstanceArgs($dependencies);
    }

    /**
     * Register default services
     */
    protected function registerDefaultServices()
    {
        // Register Post Repository
        $this->singleton('PostRepositoryInterface', function ($container) {
            require_once APPPATH . 'repositories/PostRepository.php';
            return new PostRepository();
        });

        // Register Post Validator
        $this->singleton('PostValidator', function ($container) {
            require_once APPPATH . 'validators/PostValidator.php';
            return new PostValidator();
        });

        // Register Post Service
        $this->singleton('PostServiceInterface', function ($container) {
            require_once APPPATH . 'services/PostService.php';
            return new PostService(
                $container->resolve('PostRepositoryInterface'),
                $container->resolve('PostValidator')
            );
        });

        // Register Response Service
        $this->singleton('ApiResponseService', function ($container) {
            require_once APPPATH . 'services/ApiResponseService.php';
            return new ApiResponseService();
        });
    }

    /**
     * Get all registered services
     * 
     * @return array
     */
    public function getRegisteredServices()
    {
        return array_keys($this->services);
    }

    /**
     * Clear all resolved instances
     */
    public function clearResolved()
    {
        $this->resolved = [];
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialization
     */
    public function __wakeup()
    {
        throw new RuntimeException("Cannot unserialize singleton");
    }
}
