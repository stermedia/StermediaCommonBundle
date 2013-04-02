<?php

/*
 * This file is part of the CommonBundle package.
 *
 * (c) Stermedia <http://stermedia.pl/>
 */

namespace Stermedia\Bundle\CommonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Common controller.
 *
 * @package    CommonBundle
 * @subpackage Controllers
 * @author     Jakub Paszkiewicz <paszkiewicz.jakub@gmail.com>
 * @author     Krzysztof Niziol <krzysztof.niziol@meritoo.pl>
 */
abstract class CommonController extends Controller
{
    /**
     * Returns Security context
     *
     * @return \Symfony\Component\Security\Core\SecurityContext
     */
    protected function getSecurityContext()
    {
        return $this->get('security.context');
    }

    /**
     * Returns Entity Manager
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getDoctrine()->getManager();
    }

    /**
     * Returns current logged User
     *
     * @return \FOS\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->getSecurityContext()->getToken()->getUser();
    }

    /**
     * Returns Acl provider
     *
     * @return \Symfony\Component\Security\Acl\Dbal\MutableAclProvider
     */
    protected function getAclProvider()
    {
        return $this->get('security.acl.provider');
    }

    /**
     * Returns Session
     *
     * @return \Symfony\Component\HttpFoundation\Session\Session
     */
    protected function getSession()
    {
        return $this->get('session');
    }

    /**
     * @return \Symfony\Component\Routing\RouterInterface
     */
    protected function getRouter()
    {
        return $this->get('router');
    }

    /**
     * @return \Symfony\Component\Translation\Translator
     */
    protected function getTranslator()
    {
        return $this->get('translator');
    }

    /**
     *  Returns mailer
     *
     * @return \Swift_Mailer
     */
    protected function getMailer()
    {
        return $this->get('mailer');
    }

    /**
     * Returns Paginator
     *
     * @return \Knp\Component\Pager\Paginator
     */
    protected function getPaginator()
    {
        return $this->get('knp_paginator');
    }

    /**
     * Returns Acl Manager
     *
     * @return \Stermedia\CommonBundle\Domain\AclManager
     */
    protected function getAclManager()
    {
        return $this->get('stermedia.acl_manager');
    }

    /**
     * Creates and returns a named form builder instance
     *
     * @return \Symfony\Component\Form\FormFactory
     */
    public function getFormFactory()
    {
        return $this->get('form.factory');
    }

    /**
     * Gets the flashBag interface.
     *
     * @return \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface
     */
    public function getFlashBag()
    {
        return $this->getSession()->getFlashBag();
    }

    /**
     * Method tries to resolve referer route name
     *
     * @return null|string null if path do not match any route or (string) routeName to use with router->generate
     */
    public function getRefererRouteName()
    {
        $server = $this->getRequest()->server->all();
        if (!isset($server['HTTP_REFERER'])) {
            return null;
        }
        $referer = $server['HTTP_REFERER'];
        //strip http
        $referer = str_replace('http://', '', $referer);
        $referer = str_replace('https://', '', $referer);
        //strip host
        $referer = str_replace($server['HTTP_HOST'], '', $referer);
        //strip script
        $referer = str_replace($server['SCRIPT_NAME'], '', $referer);
        try {
            $routeParams = $this->getRouter()->match($referer);
        } catch (\RuntimeException $e) {
            return null;
        }
        // Get the route name
        $routeName = $routeParams['_route'];

        return $routeName;
    }

    /**
     * Redirects to referer
     *
     * @param string $alternateRoute Alternate route when referer not found
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToReferer($alternateRoute)
    {
        if ($referer = $this->getRefererRouteName()) {
            return new RedirectResponse($this->getRouter()->generate($referer));
        } else {
            return new RedirectResponse($this->getRouter()->generate($alternateRoute));
        }
    }

    /**
     * Redirects to the given route
     *
     * @param string $route      The name of the route
     * @param array  $parameters [default= array()] An array of parameters
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectTo($route, $parameters = array())
    {
        $url = $this->generateUrl($route, $parameters);

        return $this->redirect($url);
    }

    /**
     * Returns the container
     *
     * @return \Symfony\Component\DependencyInjection\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Returns value of parameter stored in the container
     *
     * @param string $parameterName Name of the parameter
     *
     * @return mixed The parameter value
     */
    public function getContainerParameter($parameterName)
    {
        return $this->getContainer()->getParameter($parameterName);
    }
}
