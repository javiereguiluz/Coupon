<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * This file is part of the Coupon sample application.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Coupon\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DefaultController extends Controller
{
    /**
     * Allows the administrator to seitch the active city of
     * the backend application.
     *
     * @param string $city The slug of the new city
     */
    public function switchCityAction($city)
    {
        $this->getRequest()->getSession()->set('city', $city);

        // Try to redirect the administrator to the same page where he/she was
        $referrer = $this->getRequest()->server->get('HTTP_REFERER');

        return new RedirectResponse($referrer, 302);
    }
}
