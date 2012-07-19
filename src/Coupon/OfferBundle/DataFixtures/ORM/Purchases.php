<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * This file is part of the Coupon sample application.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Coupon\OfferBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Coupon\OfferBundle\Entity\Offer;
use Coupon\UserBundle\Entity\User;
use Coupon\OfferBundle\Entity\Purchase;

/**
 * Data fixtures for the Purchase entity.
 */
class Purchases extends AbstractFixture implements OrderedFixtureInterface
{
    public function getOrder()
    {
        return 50;
    }

    public function load(ObjectManager $manager)
    {
        // Get all the offers and users from the database
        $offers = $manager->getRepository('OfferBundle:Offer')->findAll();
        $users  = $manager->getRepository('UserBundle:User')->findAll();

        foreach ($users as $user) {
            $purchases = rand(0, 10);
            $bought = array();

            for ($i=0; $i<$purchases; $i++) {
                $purchase = new Purchase();

                $purchase->setCreatedAt(new \DateTime('now - '.rand(0, 250).' hours'));

                // pick an offer randomly ...
                $offer = $offers[array_rand($offers)];
                
                // .. but check that it meets the conditions for a valid purchase:
                //   - a user can't purchase the same offer more than one time
                //   - the offer must be approved
                //   - the offer must be published
                while (in_array($offer->getId(), $bought)
                       || $offer->isApproved() == false
                       || $offer->getPublishedAt() > new \DateTime('now')) {
                    $offer = $offers[array_rand($offers)];
                }
                $bought[] = $offer->getId();

                $purchase->setOffer($offer);
                $purchase->setUser($user);

                $manager->persist($purchase);

                $offer->setPurchases($offer->getPurchases() + 1);
                $manager->persist($offer);
            }

            unset($bought);
        }

        $manager->flush();
    }
}
