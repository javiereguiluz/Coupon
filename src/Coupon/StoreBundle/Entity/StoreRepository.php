<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * This file is part of the Coupon sample application.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Coupon\StoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class StoreRepository extends EntityRepository
{
     /**
      * Returns the latest offers for the given store.
      *
      * @param string $store_id The id of the store
      * @param string $limit    The number of offers returned (5 by default)
      */
    public function findLatestOffers($store_id, $limit = 5)
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery('
            SELECT offer, store
            FROM OfferBundle:Offer offer JOIN offer.store store
            WHERE offer.store = :id
            ORDER BY offer.expired_at DESC
        ');
        $query->setMaxResults($limit);
        $query->setParameter('id', $store_id);
        $query->useResultCache(true, 3600);

        return $query->getResult();
    }

    /**
     * Returns the latest published and approved offers for the given store.
     *
      * @param string $store_id The id of the store
      * @param string $limit    The number of offers returned (10 by default)
     */
    public function findLatestPublishedOffers($store_id, $limit = 10)
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery('
            SELECT offer, store
            FROM OfferBundle:Offer offer JOIN offer.store store
            WHERE offer.approved = true AND offer.published_at < :date AND offer.store = :id
            ORDER BY offer.published_at DESC
        ');
        $query->setMaxResults($limit);
        $query->setParameter('id', $store_id);
        $query->setParameter('date', new \DateTime('now'));

        return $query->getResult();
    }

    /**
     * Returns the five nearest stores to the given store
     *
     * @param string $store The slug of the store
     * @param string $city  The slug of the city
     */
    public function findNearby($store, $city)
    {
        $em = $this->getEntityManager();

        // Ideally, this should be a geolocation-aware query
        $query = $em->createQuery('
            SELECT store, city
            FROM StoreBundle:Store store JOIN store.city city
            WHERE city.slug = :city AND store.slug != :store
        ');
        $query->setMaxResults(5);
        $query->setParameter('city', $city);
        $query->setParameter('store', $store);
        $query->useResultCache(true, 600);

        return $query->getResult();
    }
}
