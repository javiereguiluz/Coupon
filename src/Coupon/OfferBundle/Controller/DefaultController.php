<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * This file is part of the Coupon sample application.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Coupon\OfferBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DefaultController extends Controller
{
    /**
     * Renders the homepage of the website
     *
     * @param string $city The slug of the city
     */
    public function homepageAction($city)
    {
        if (null == $city) {
            $city = $this->container->getParameter('coupon.default_city');

            return new RedirectResponse($this->generateUrl('homepage', array('city' => $city)));
        }

        $em = $this->getDoctrine()->getEntityManager();
        $offer = $em->getRepository('OfferBundle:Offer')->findTodayOffer($city);

        if (!$offer) {
            throw $this->createNotFoundException('There is no offer today for the selected city.');
        }

        $response = $this->render('OfferBundle:Default:homepage.html.twig', array(
            'offer' => $offer
        ));
        $response->setSharedMaxAge(60);

        return $response;
    }

    /**
     * Renders the detail page of the given offer
     *
     * @param string $city   The slug of the city
     * @param string $slug   The slug of the offer
     */
    public function OfferAction($city, $slug)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $offer  = $em->getRepository('OfferBundle:Offer')->findOffer($city, $slug);
        $nearby = $em->getRepository('OfferBundle:Offer')->findNearby($city);

        if (!$offer) {
            throw $this->createNotFoundException('The requested offer is unavailable.');
        }

        $response = $this->render('OfferBundle:Default:show.html.twig', array(
            'nearby' => $nearby,
            'offer'  => $offer
        ));

        $response->setSharedMaxAge(60);

        return $response;
    }
}
