<?php
/*
 * This file is part of the CommonBundle package.
 *
 * (c) Stermedia <http://stermedia.pl/>
 */

namespace Stermedia\Bundle\CommonBundle\Tests;

use Symfony\Component\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Container aware test case
 *
 * @author Jakub Paszkiewicz <j.paszkiewicz@stermedia.pl>
 */
abstract class ContainerAwareTestCase extends WebTestCase
{
    /**
     * Kernel
     *
     * @var \Symfony\Component\HttpKernel\KernelInterface
     */
    protected static $kernel;

    /**
     * Container
     *
     * @var \Symfony\Component\DependencyInjection\Container
     */
    protected static $container;

    /**
     * Set up before class
     */
    public static function setUpBeforeClass()
    {
        if (null !== static::$kernel) {
            static::$kernel->shutdown();
        }

        static::$kernel = static::createKernel();
        static::$kernel->boot();

        self::$container = self::$kernel->getContainer();

    }

    /**
     * Clear out parent tear down
     */
    protected function tearDown()
    {
    }

    /**
     * Tear down after class. Shuts the kernel down if it was used in the test.
     */
    public static function tearDownAfterClass()
    {
        if (null !== static::$kernel) {
            static::$kernel->shutdown();
        }
    }
}
