<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * This file is part of the Coupon sample application.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Coupon\StoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{    
    /**
     * Renders the homepage of the given store (this page includes basic store
     * information and its latest published offers)
     *
     * @param string $city  The slug of the city
     * @param string $store The slug of the store
     */
    public function indexAction($city, $store)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $city = $em->getRepository('CityBundle:City')->findOneBySlug($city);
        $store = $em->getRepository('StoreBundle:Store')->findOneBy(array(
            'slug' => $store,
            'city' => $city->getId()
        ));

        if (!$store) {
            throw $this->createNotFoundException('The requested store is unavailable.');
        }

        $offers = $em->getRepository('StoreBundle:Store')->findLatestPublishedOffers($store->getId());
        $nearby = $em->getRepository('StoreBundle:Store')->findNearby(
            $store->getSlug(),
            $store->getCity()->getSlug()
        );

        $format = $this->get('request')->getRequestFormat();
        $response = $this->render('StoreBundle:Default:index.'.$format.'.twig', array(
            'store'  => $store,
            'offers' => $offers,
            'nearby' => $nearby
        ));

        $response->setSharedMaxAge(3600);

        return $response;
    }
}
