<?php

use _PHPStan_2f712479f\Symfony\Contracts\Service\ServiceProviderInterface;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../tests/psr.php';

class User {
    public $id;

    public function __construct($auto = false)
    {
        if ($auto) {
            $this->id = uniqid();
        }
    }
}

class A {
    public function __construct(B $b) {}
}

class B {
    public function __construct(A $a) {}
}

class A2 {
    public function __construct(B2 $b) {}
}

class B2 {
    public function setA2(A2 $a) {}
}

class C {}

class D {}

class E {}

class F {}

class G {
    public $client;

    public function __construct(User $client) {
        $this->client = $client;
    }
}

class H {
    public function __construct($someArg) {}
}

class I {
    public function __construct(string $someArg2) {}
}

class Logger {}

class BaseRepository {
    public $logger;

    public function setLogger(Logger $logger) {
        $this->logger = $logger;
    }
}

class SomeRepository extends BaseRepository {}

class DbConnection {

    /**
     * @var string
     */
    public $connectionString;

    public function __construct(string $connectionString)
    {
        $this->connectionString = $connectionString;
    }

    public function executeQuery(string $query): array
    {
        $conn = $this->connectionString;
        return [$conn];
    }
}

class SomeServiceProvider implements \SDI\ProviderInterface {
    public function register(\SDI\ContainerInterface $container): void
    {
        $container['from-service-provider'] = 123;
        $container['from-service-provider-2'] = [$this, 'registerService2'];
    }

    public static function registerService2(\SDI\ContainerInterface $container): int
    {
        return 456;
    }
}

interface GMySqlInterface {}
class GMySqlClient implements GMySqlInterface {
    public $dsnString;

    public function __construct(string $dsnString){
        $this->dsnString = $dsnString;
    }
}
class GMySqlSlaveClient implements GMySqlInterface {
    public $dsnString;

    public function __construct(string $dsnString){
        $this->dsnString = $dsnString;
    }
}
class GQueryBuilder {
    public $mysqlClient;

    public function __construct(GMySqlClient $mysqlClient){
        $this->mysqlClient = $mysqlClient;
    }}
class GRedisClient {
    public $dsnString;

    public function __construct(string $dsnString){
        $this->dsnString = $dsnString;
    }}
class GUsersRepository {
    public $mysqlClient;

    public function __construct(GMySqlClient $mysqlClient){
        $this->mysqlClient = $mysqlClient;
    }}
class GPostsRepository {
    public $mysqlClient;

    public function __construct(GMySqlClient $mysqlClient){
        $this->mysqlClient = $mysqlClient;
    }}
class GSessionsHandler {
    public $redisClient;
    public $logger;

    public function __construct(GLogger $logger, GRedisClient $redisClient){
        $this->logger = $logger;
        $this->redisClient = $redisClient;
    }}
class GPostsService {
    public $logger;
    public $usersRepository;
    public $postsRepository;

    public function __construct(GLogger $logger, GUsersRepository $usersRepository, GPostsRepository $postsRepository){
        $this->postsRepository = $postsRepository;
        $this->usersRepository = $usersRepository;
        $this->logger = $logger;
    }}
class GUsersExportService  {
    public $usersRepository;
    /**
     * @var string
     */
    private $exportFilePath;

    public function __construct(GUsersRepository $usersRepository, string $exportFilePath){
        $this->usersRepository = $usersRepository;
        $this->exportFilePath = $exportFilePath;
    }}
class GExportBuilder {}
class GLoggerFileHandler {
    public $filePath;

    public function __construct(string $filePath){
        $this->filePath = $filePath;
    }}
class GLoggerDbHandler {
    public $mysqlClient;

    public function __construct(GMySqlClient $mysqlClient){
        $this->mysqlClient = $mysqlClient;
    }}
class GLogger {public function __construct(array $handlers){}}
