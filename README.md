# Dependency Injection Container

For those who understand what it is and strive for simplicity.

Zero dependencies and PHP 7.1+.

## Features
* Simple API: Container API is as simple as regular array API.
* Tags: Organize services using tags and fetch them in groups.
* Shared Instances: Easily create singletons with a built-in helper.
* Service Extenders: Extend services after they are resolved.
* Circular Dependency Detection: Automatically prevents and reports circular dependencies.
* Autowiring: Container will automatically resolve dependencies.

### Simple API
Consider `Container` as a regular array.
```php
$container = new Container();

$container['cache-file-path'] = '/tmp/app.cache';
$container[CacheStorage::class] = fn (Container $c) => new CacheStorage($c['cache-file-path']);
$container[Cache::class] = fn(Container $c) => new Cache($c[CacheStorage::class]);

$cache = $container[Cache::class];

$cache->get('currency-rates');
```

### Tags
You need to use the `Container::add()` method to use tags.
```php
$container = new Container();

$container['log-file-path'] = 'your.log';
$container->add('file-handler', fn (Container $c) => new StreamHandler($c['log-file-path']), ['handler']);
$container->add('stdout-handler', fn (Container $c) => new StreamHandler('php://output'), ['handler']);
$container[Logger::class] = function (Container $c) {
    $logger = new Logger('app');
    $logger->setHandlers($c->getByTag('handler'));

    return $logger;
};


$logger = $container[Logger::class];

$logger->warning('Oops!');
```

### Shared Instances
Use `Container::share()` wrapper or `Container::addShared()` method to create a shareable service. 
Container will return the same instance each time.
```php
$container = new Container();

$container[MySQLConnection::class] = Container::share(fn (Container $c) => new MySQLConnection());

$conn1 = $container[MySQLConnection::class];
$conn2 = $container[MySQLConnection::class];

assert(spl_object_hash($conn1) === spl_object_hash($conn2));
```

### Service Extenders
Use `Container::extend()` to modify services by a common parent, for example.
```php
$container = new Container();
$container[\Logger::class] = fn () => new \Logger();
$container[\SomeRepository::class] = fn () => new \SomeRepository();
$container->extend(\BaseRepository::class, function (\SomeRepository $repository, Container $c) {
    $repository->setLogger($c[\Logger::class]);

    return $repository;
});

$repository = $container[\SomeRepository::class];
// From now on, every repository returned by the container and which `extends` BaseRepository has a Logger inside it.
```

### Circular Dependency Detection
Container will let you know if you have a circular dependency.
```php
$container = new Container();
$container[A::class] = fn (Container $c) => new \A($c[B::class]);
$container[B::class] = fn (Container $c) => new \B($c[A::class]);

$a = $container[\A::class];
// CircularDependencyException: Circular dependency detected: A -> B -> A 
```

### Autowiring
Use `AutowireContainer` for automatic dependency resolving.
```php
class FilesystemAdapter {
    // some logic inside
}
class Filesystem {
    /**
     * @var \FilesystemAdapter
     */
    private $adapter;

    public function __construct(FilesystemAdapter $adapter)
    {
        $this->adapter = $adapter;
    }
    
    // use $this->adapter as you need
}

$container = new AutowireContainer();

$filesystem = $container[Filesystem::class]
// $filesystem will be an instance of Filesystem that contains FilesystemAdapter inside
```

## PSR-11
Install `psr/container` package first.
```bash
composer require psr/container
```

Then wrap `Container` in `PsrContainer` and pass it to whoever expected `Psr\Container\ContainerInterface`.
```php
// Define services, dependencies, etc.
$container = new Container();

// Wrap it.
$psrContainer = new PsrContainer($container);

$service = $psrContainer->get(Service::class);
```
