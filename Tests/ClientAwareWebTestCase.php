<?php

/*
 * This file is part of the CommonBundle package.
 *
 * (c) Stermedia <http://stermedia.pl/>
 */

namespace Stermedia\Bundle\CommonBundle\Tests;

use Symfony\Component\Console\Application;

/**
 * Common Web Test Case
 */
abstract class ClientAwareWebTestCase extends DatabaseAwareTestCase
{
    /**
     * Client
     *
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    protected static $client;

    /**
     * Set up before test
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$client = static::createClient();
        self::$client->followRedirects();
    }

    public function tearDown()
    {
        self::$client->restart();
    }
}
