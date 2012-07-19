<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * This file is part of the Coupon sample application.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Coupon\OfferBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class RequestListener
{
    /**
     * Simple sample method that shows how to add new request formats
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $event->getRequest()->setFormat('pdf', 'application/pdf');
    }
}
