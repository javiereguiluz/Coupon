<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * This file is part of the Coupon sample application.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Coupon\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    /**
     * Returns the purchases of the given user.
     *
     * @param string $user_id The id of the user
     */
    public function findAllPurchases($user_id)
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery('
            SELECT purchase, offer, store
            FROM OfferBundle:Purchase purchase JOIN purchase.offer offer JOIN offer.store store
            WHERE purchase.user = :id
            ORDER BY purchase.created_at DESC
        ');
        $query->setParameter('id', $user_id);

        return $query->getResult();
    }
}
