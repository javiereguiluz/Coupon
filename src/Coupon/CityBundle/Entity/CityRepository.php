<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * This file is part of the Coupon sample application.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Coupon\CityBundle\Entity;

use Doctrine\ORM\EntityRepository;

class CityRepository extends EntityRepository
{
    /**
     * Returns a simple array with all the available cities.
     */
    public function findAllCities()
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery('
            SELECT city
            FROM CityBundle:City city
            ORDER BY city.name
        ');
        $query->useResultCache(true, 3600);

        return $query->getArrayResult();
    }

    /**
     * Returns the five nearest cities to the given city
     *
     * @param string $city_id The id of the city
     */
    public function findNearby($city_id)
    {
        $em = $this->getEntityManager();

        // Ideally, this should be a geolocation-aware query
        $query = $em->createQuery('
            SELECT city
            FROM CityBundle:City city
            WHERE city.id != :id
            ORDER BY city.name ASC
        ');
        $query->setMaxResults(5);
        $query->setParameter('id', $city_id);
        $query->useResultCache(true, 3600);

        return $query->getResult();
    }

    /**
     * Returns all the offers for the given city
     *
     * @param string $city The slug of the city
     */
    public function findAllOffers($city)
    {
        return $this->queryAllOffers($city)->getResult();
    }

    /**
     * Special method that returns just the query used by `findAllOffers()` method.
     * It's mandatory to paginate the query results.
     *
     * @param string $city The slug of the city
     */
    public function queryAllOffers($city)
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery('
            SELECT offer, store
            FROM OfferBundle:Offer offer JOIN offer.store store JOIN offer.city city
            WHERE city.slug = :city
            ORDER BY offer.published_at DESC
        ');
        $query->setParameter('city', $city);
        $query->useResultCache(true, 600);

        return $query;
    }

    /**
     * Returns all the users associated with the given city
     *
     * @param string $city The slug of the city
     */
    public function findAllUsers($city)
    {
        return $this->queryAllUsers($city)->getResult();
    }

    /**
     * Special method that returns just the query used by `findAllUsers()` method.
     * It's mandatory to paginate the query results.
     *
     * @param string $city The slug of the city
     */
    public function queryAllUsers($city)
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery('
            SELECT user
            FROM UserBundle:User user JOIN user.city city
            WHERE city.slug = :city
            ORDER BY user.surname ASC
        ');
        $query->setParameter('city', $city);

        return $query;
    }

    /**
     * Returns all the stores associated with the given city
     *
     * @param string $city The slug of the city
     */
    public function findAllStores($city)
    {
        return $this->queryAllStores($city)->getResult();
    }

    /**
     * Special method that returns just the query used by `findAllStores()` method.
     * It's mandatory to paginate the query results.
     *
     * @param string $city The slug of the city
     */
    public function queryAllStores($city)
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery('
            SELECT store
            FROM StoreBundle:Store store JOIN store.city city
            WHERE city.slug = :city
            ORDER BY store.name ASC
        ');
        $query->setParameter('city', $city);
        $query->useResultCache(true, 600);

        return $query;
    }
}
