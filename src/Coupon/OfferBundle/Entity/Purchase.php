<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * This file is part of the Coupon sample application.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Coupon\OfferBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Coupon\OfferBundle\Entity\Offer;
use Coupon\UserBundle\Entity\User;

/**
 * @ORM\Entity
 */
class Purchase
{
    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Coupon\OfferBundle\Entity\Offer")
     */
    protected $offer;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Coupon\UserBundle\Entity\User")
     */
    protected $user;

    /**
     * Set created_at
     *
     * @param datetime $created_at
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
    }

    /**
     * Get created_at
     *
     * @return datetime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set offer
     *
     * @param Coupon\OfferBundle\Entity\Offer $offer
     */
    public function setOffer(Offer $offer)
    {
        $this->offer = $offer;
    }

    /**
     * Get offer
     *
     * @return Coupon\OfferBundle\Entity\Offer
     */
    public function getOffer()
    {
        return $this->offer;
    }

    /**
     * Set user
     *
     * @param Coupon\UserBundle\Entity\User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return Coupon\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
