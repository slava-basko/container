<?php

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
