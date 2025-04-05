<?php

namespace SDI\Tests\TestCase;

use PHPUnit\Framework\TestCase;
use SDI\Container;

class GraphvizTest extends TestCase
{
    public function testGraph()
    {
        $container = new Container();

        $container['mysql-dsn'] = 'mysql:host=localhost;port=3306;dbname=posts';
        $container['redis-dsn'] = 'redis:bla';
        $container['export-path'] = '/var/log/export';

        $container->addShared(\GMySqlClient::class, function (Container $container) {
            return new \GMySqlClient($container['mysql-dsn']);
        });
        $container->addShared(\GMySqlSlaveClient::class, function (Container $container) {
            return new \GMySqlSlaveClient($container['mysql-dsn']);
        });
        $container->symlink(\GMySqlClient::class, \GMySqlInterface::class);

        $container->add(\GLoggerFileHandler::class, function (Container $c) {
            return new \GLoggerFileHandler($c['logfile-path']);
        }, ['handler']);
        $container->add(\GLoggerDbHandler::class, function (Container $c) {
            return new \GLoggerDbHandler($c[\GMySqlInterface::class]);
        }, ['handler']);
        $container->add(\GLogger::class, function (Container $c) {
            return new \GLogger($c->getByTag('handler'));
        });

        $container->add(\GQueryBuilder::class, function (Container $container) {
            return new \GQueryBuilder($container->get(\GMySqlInterface::class));
        });
        $container->addShared(\GRedisClient::class, function (Container $container) {
            return new \GRedisClient($container['redis-dsn']);
        });

        $container->add(\GUsersRepository::class, function (Container $c) {
            return new \GUsersRepository($c[\GMySqlInterface::class]);
        });
        $container->add(\GPostsRepository::class, function (Container $c) {
            return new \GPostsRepository($c[\GMySqlInterface::class]);
        });
        $container->add(\GSessionsHandler::class, function (Container $c) {
            return new \GSessionsHandler($c[\GLogger::class], $c[\GRedisClient::class]);
        });

        $container->add(\GPostsService::class, function (Container $c) {
            return new \GPostsService($c[\GLogger::class], $c[\GUsersRepository::class], $c[\GPostsRepository::class]);
        });
        $container->add(\GUsersExportService::class, function ($cont) {
            return new \GUsersExportService($cont[\GUsersRepository::class], $cont['export-path']);
        });

        $container->add(\GExportBuilder::class, function () {
            return new \GExportBuilder();
        });

        $graph = new \SDI\Export\Graphviz($container);

        $this->assertEquals('digraph sc {
  ratio="compress"
  node [fontsize="11" fontname="Arial"];
  edge [fontsize="9" fontname="Arial" color="grey" arrowhead="open" arrowsize="0.5"];

  node_mysql_dsn [label="mysql-dsn", shape="oval", style="filled", fillcolor="#d4d7ff"];
  node_redis_dsn [label="redis-dsn", shape="oval", style="filled", fillcolor="#d4d7ff"];
  node_export_path [label="export-path", shape="oval", style="filled", fillcolor="#d4d7ff"];
  node_GMySqlClient [label="GMySqlClient\n(GMySqlInterface)", shape="record", style="filled, dashed", fillcolor="#eeeeee"];
  node_GMySqlSlaveClient [label="GMySqlSlaveClient", shape="record", style="filled, dashed", fillcolor="#eeeeee"];
  node_GLoggerFileHandler [label="GLoggerFileHandler", shape="record", style="filled", fillcolor="#eeeeee"];
  node_GLoggerDbHandler [label="GLoggerDbHandler", shape="record", style="filled", fillcolor="#eeeeee"];
  node_GLogger [label="GLogger", shape="record", style="filled", fillcolor="#eeeeee"];
  node_GQueryBuilder [label="GQueryBuilder", shape="record", style="filled", fillcolor="#eeeeee"];
  node_GRedisClient [label="GRedisClient", shape="record", style="filled, dashed", fillcolor="#eeeeee"];
  node_GUsersRepository [label="GUsersRepository", shape="record", style="filled", fillcolor="#eeeeee"];
  node_GPostsRepository [label="GPostsRepository", shape="record", style="filled", fillcolor="#eeeeee"];
  node_GSessionsHandler [label="GSessionsHandler", shape="record", style="filled", fillcolor="#eeeeee"];
  node_GPostsService [label="GPostsService", shape="record", style="filled", fillcolor="#eeeeee"];
  node_GUsersExportService [label="GUsersExportService", shape="record", style="filled", fillcolor="#eeeeee"];
  node_GExportBuilder [label="GExportBuilder", shape="record", style="filled", fillcolor="#eeeeee"];
  node_logfile_path [label="logfile-path", shape="parallelogram", style="filled, dotted", fillcolor="#ffbfbe"];
  node_GMySqlClient -> node_mysql_dsn [style="filled"];
  node_GMySqlSlaveClient -> node_mysql_dsn [style="filled"];
  node_GLoggerFileHandler -> node_logfile_path [style="filled"];
  node_GLoggerDbHandler -> node_GMySqlClient [style="filled"];
  node_GLogger -> node_GLoggerFileHandler [style="filled"];
  node_GLogger -> node_GLoggerDbHandler [style="filled"];
  node_GQueryBuilder -> node_GMySqlClient [style="filled"];
  node_GRedisClient -> node_redis_dsn [style="filled"];
  node_GUsersRepository -> node_GMySqlClient [style="filled"];
  node_GPostsRepository -> node_GMySqlClient [style="filled"];
  node_GSessionsHandler -> node_GLogger [style="filled"];
  node_GSessionsHandler -> node_GRedisClient [style="filled"];
  node_GPostsService -> node_GLogger [style="filled"];
  node_GPostsService -> node_GUsersRepository [style="filled"];
  node_GPostsService -> node_GPostsRepository [style="filled"];
  node_GUsersExportService -> node_GUsersRepository [style="filled"];
  node_GUsersExportService -> node_export_path [style="filled"];
}
', $graph->build());
    }
}
