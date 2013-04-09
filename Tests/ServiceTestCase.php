<?php

/*
 * This file is part of the Stermedia/CommonBundle
 *
 * (c) Stermedia <http://stermedia.eu>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Stermedia\Bundle\CommonBundle\Tests;

/**
 * Abstract Service test case
 *
 * @author Jakub Paszkeiwicz <j.paszkiewicz@stermedia.pl>
 */
abstract class ServiceTestCase extends ContainerAwareTestCase
{
    /**
     * Service object
     */
    protected static $service;

    /**
     * Set up before class
     *
     * @param string $serviceName Name of the service to test
     */
    public static function setUpBeforeClass($serviceName)
    {
        parent::setUpBeforeClass();
        self::$service = self::$container->get($serviceName);
    }
}