<?php

/*
 * This file is part of the Stermedia/CommonBundle
 *
 * (c) Stermedia <http://stermedia.eu>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Stermedia\CommonBundle\Tests;

use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use \Doctrine\DBAL\Driver\PDOSqlite\Driver as SqliteDriver;

/**
 * Database aware test case
 *
 * @author Jakub Paszkiewicz <paszkiewicz.jakub@gmail.com>
 */
abstract class DatabaseAwareTestCase extends ContainerAwareTestCase
{
    /**
     * Determines if database cache is available
     *
     * @var bool
     */
    protected static $isDatabaseCached = false;

    /**
     * Set up before class
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        if (!self::$isDatabaseCached) {
            self::databaseInit();
            self::databaseBackup();
            self::$isDatabaseCached = true;
        }
    }
    /**
     * Set up before test
     */
    public function setUp()
    {
        parent::setUp();

        self::databaseRestore();
    }

    /**
     * Returns entity manager
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected static function getEntityManager()
    {
        return self::$kernel->getContainer()->get('doctrine')->getManager();
    }

    /**
     * Initialize database
     */
    protected static function databaseInit()
    {
        $em=self::getEntityManager();
        $connection=$em->getConnection();
        $params = $connection->getParams();
        $name = isset($params['path']) ? $params['path'] : $params['dbname'];

        unset($params['dbname']);

        // Only quote if we don't have a path
        if (!isset($params['path'])) {
            $name = $connection->getDatabasePlatform()->quoteSingleIdentifier($name);
        }
        $connection->getSchemaManager()->dropDatabase($name);
        $schemaTool = new SchemaTool($em);
        $metadata=$em->getMetadataFactory()->getAllMetadata();
        $schemaTool->createSchema($metadata);

        self::warmUpCache();
        self::loadFixtures();
    }

    /**
     * Backup database
     *
     * @throws \Exception
     */
    protected static function databaseBackup()
    {
        $em=self::getEntityManager();
        $connection = $em->getConnection();

        if ($connection->getDriver() instanceOf SqliteDriver) {
            $params = $connection->getParams();
            $db = isset($params['path']) ? $params['path'] : $params['dbname'];
            $filename = pathinfo($db, PATHINFO_BASENAME);
            $backup = self::$container->getParameter('kernel.cache_dir').DIRECTORY_SEPARATOR.$filename;

            if (file_exists($db)) {
                if (!copy($db, $backup)) {
                    throw new \Exception('Cannot backup sqlite database file to '.$backup);
                }
            } else {
                throw new \Exception('Sqlite database file not found in '.$db);
            }
        }
    }

    /**
     * Reset database. Copy the cached file.
     *
     * @return bool
     * @throws \Exception
     */
    protected static function databaseRestore()
    {
        $em=self::getEntityManager();
        $connection = $em->getConnection();

        if ($connection->getDriver() instanceOf SqliteDriver) {
            $params = $connection->getParams();
            $db = isset($params['path']) ? $params['path'] : $params['dbname'];
            $filename = pathinfo($db, PATHINFO_BASENAME);
            $backup = self::$container->getParameter('kernel.cache_dir').DIRECTORY_SEPARATOR.$filename;

            if (file_exists($backup)) {
                if (!copy($backup, $db)) {
                    throw new \Exception('Cannot restore sqlite database file from backup to '.$backup);
                }
            } else {
                throw new \Exception('Sqlite database backup file not found in '.$db);
            }
        } else {
            self::databaseInit();
            self::warmUpCache();
            self::loadFixtures();
        }
    }

    /**
     * Warm up cache
     */
    protected static function warmUpCache()
    {
        $warmer = self::$container->get('cache_warmer');

        $warmer->warmUp(self::$container->getParameter('kernel.cache_dir'));
    }

    /**
     * Load tests fixtures
     */
    protected static function loadFixtures()
    {
        $paths = array();
        foreach (static::$kernel->getBundles() as $bundle) {
            $paths[] = $bundle->getPath().DIRECTORY_SEPARATOR.'DataFixtures'.DIRECTORY_SEPARATOR.'ORM';
        }

        $loader = new ContainerAwareLoader(self::$container);
        foreach ($paths as $path) {
            if (is_dir($path)) {
                $loader->loadFromDirectory($path);
            }
        }
        $fixtures = $loader->getFixtures();

        $em=self::getEntityManager();
        $purger = new ORMPurger($em);
        $executor = new ORMExecutor($em, $purger);
        $executor->execute($fixtures);
    }
}
