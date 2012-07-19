<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * This file is part of the Coupon sample application.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Coupon\OfferBundle\Entity;

use Doctrine\ORM\EntityRepository;

class OfferRepository extends EntityRepository
{
    /**
     * Returns the offer corresponding to the given city and slug.
     *
     * @param string $city   The slug of the city
     * @param string $slug   The slug of the offer
     */
    public function findOffer($city, $slug)
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery('
            SELECT offer, city, store
            FROM OfferBundle:Offer offer JOIN offer.city city JOIN offer.store store
            WHERE offer.approved = true AND offer.slug = :slug AND city.slug = :city
        ');
        $query->setParameter('slug', $slug);
        $query->setParameter('city', $city);
        $query->setMaxResults(1);

        return $query->getOneOrNullResult();
    }

    /**
     * Returns today's offer for the given city.
     *
     * @param string $city The slug of the city
     */
    public function findTodayOffer($city)
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery('
            SELECT offer, city, store
            FROM OfferBundle:Offer offer JOIN offer.city city JOIN offer.store store
            WHERE offer.approved = true AND offer.published_at < :date AND city.slug = :city
            ORDER BY offer.published_at DESC
        ');
        $query->setParameter('date', new \DateTime('now'));
        $query->setParameter('city', $city);
        $query->setMaxResults(1);

        return $query->getOneOrNullResult();
    }

    /**
     * Returns tomorrow's offer for the given city.
     *
     * @param string $city The slug of the city
     */
    public function findTomorrowOffer($city)
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery('
            SELECT offer, city, store
            FROM OfferBundle:Offer offer JOIN offer.city city JOIN offer.store store
            WHERE offer.approved = true AND offer.published_at < :date AND city.slug = :city
            ORDER BY offer.published_at DESC
        ');
        $query->setParameter('date', new \DateTime('tomorrow'));
        $query->setParameter('city', $city);
        $query->setMaxResults(1);

        return $query->getOneOrNullResult();
    }

    /**
     * Returns the five most recent offers for the given city.
     *
     * @param string $city_id The id of the city
     */
    public function findLatest($city_id)
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery('
            SELECT offer, store
            FROM OfferBundle:Offer offer JOIN offer.store store
            WHERE offer.approved = true AND offer.published_at < :date AND offer.city = :id
            ORDER BY offer.published_at DESC
        ');
        $query->setMaxResults(5);
        $query->setParameter('id', $city_id);
        $query->setParameter('date', new \DateTime('today'));
        $query->useResultCache(true, 600);

        return $query->getResult();
    }

    /**
     * Returns the five nearest offers to the given city
     *
     * @param string $city The slug of the city
     */
    public function findNearby($city)
    {
        $em = $this->getEntityManager();

        // Ideally, this should be a geolocation-aware query
        $query = $em->createQuery('
            SELECT offer, city
            FROM OfferBundle:Offer offer JOIN offer.city city
            WHERE offer.approved = true AND offer.published_at <= :date AND city.slug != :city
            ORDER BY offer.published_at DESC
        ');
        $query->setMaxResults(5);
        $query->setParameter('city', $city);
        $query->setParameter('date', new \DateTime('today'));
        $query->useResultCache(true, 600);

        return $query->getResult();
    }

    /**
     * Returns the purchases of the given offer.
     *
     * @param string $offer_id The id of the offer
     */
    public function findPurchasesByOffer($offer_id)
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery('
            SELECT purchase, offer, user
            FROM OfferBundle:Purchase purchase JOIN purchase.offer offer JOIN purchase.user user
            WHERE offer.id = :id
            ORDER BY purchase.created_at DESC
        ');
        $query->setParameter('id', $offer_id);

        return $query->getResult();
    }
}
