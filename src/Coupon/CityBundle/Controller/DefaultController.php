<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * This file is part of the Coupon sample application.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Coupon\CityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DefaultController extends Controller
{
    /**
     * Renders the <select> list included in every application page that allows
     * the user to switch the active city.
     *
     * @param string $city The slug of the active city
     */
    public function selectCityAction($city = null)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $cities = $em->getRepository('CityBundle:City')->findAllCities();

        return $this->render('CityBundle:Default:selectCity.html.twig', array(
            'selectedCity' => $city,
            'cities'       => $cities
        ));
    }

    /**
     * Swtiches the active city to the new given city and redirects the user
     * to the homepage of the new city.
     *
     * @param string $city The slug of the new city
     */
    public function switchAction($city)
    {
        return new RedirectResponse($this->generateUrl('homepage', array('city' => $city)));
    }

    /**
     * Renders the latest offers published in the given city
     *
     * @param string $city The slug of the city
     */
    public function latestAction($city)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $city = $em->getRepository('CityBundle:City')->findOneBySlug($city);
        if (!$city) {
            throw $this->createNotFoundException('The requested city is unavailable.');
        }

        $nearby = $em->getRepository('CityBundle:City')->findNearby($city->getId());
        $offers = $em->getRepository('OfferBundle:Offer')->findLatest($city->getId());

        $format   = $this->get('request')->getRequestFormat();
        $response = $this->render('CityBundle:Default:latest.'.$format.'.twig', array(
            'city'   => $city,
            'nearby' => $nearby,
            'offers' => $offers
        ));

        $response->setSharedMaxAge(3600);

        return $response;
    }
}
