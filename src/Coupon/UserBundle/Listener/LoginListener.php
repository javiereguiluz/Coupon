<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * This file is part of the Coupon sample application.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Coupon\UserBundle\Listener;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * When a user logs in the website, the application redirects him/her to the
 * homepage of his/her associated city. This listener performs this
 * advanced redirect and takes into account if the user comes from the
 * frontend or from the extranet.
 */
class LoginListener
{
    private $context, $router, $city = null;

    public function __construct(SecurityContext $context, Router $router)
    {
        $this->context = $context;
        $this->router  = $router;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $token = $event->getAuthenticationToken();
        $this->city = $token->getUser()->getCity()->getSlug();
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (null != $this->city) {
            if ($this->context->isGranted('ROLE_STORE')) {
                $index = $this->router->generate('extranet_index');
            } else {
                $index = $this->router->generate('homepage', array(
                    'city' => $this->city
                ));
            }

            $event->setResponse(new RedirectResponse($index));
            $event->stopPropagation();
        }
    }
}
