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
use Symfony\Component\Validator\Constraints as Assert;
use Coupon\OfferBundle\Util\Util;
use Coupon\CityBundle\Entity\City;
use Coupon\StoreBundle\Entity\Store;

/**
 * @ORM\Entity(repositoryClass="Coupon\OfferBundle\Entity\OfferRepository")
 */
class Offer
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank()
     */
    protected $slug;

    /**
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank()
     * @Assert\MinLength(30)
     */
    protected $description;

    /**
     * @ORM\Column(type="text")
     */
    protected $terms;

    /**
     * @ORM\Column(type="string")
     *
     * @Assert\Image(maxSize = "500k")
     */
    protected $photo;

    /**
     * @ORM\Column(type="decimal", scale=2)
     *
     * @Assert\Min(0)
     */
    protected $price;

    /**
     * @ORM\Column(type="decimal", scale=2)
     */
    protected $discount;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Assert\DateTime
     */
    protected $published_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Assert\DateTime
     */
    protected $expired_at;

    /**
     * @ORM\Column(type="integer")
     */
    protected $purchases;

    /**
     * @ORM\Column(type="integer")
     *
     * @Assert\Type(type="integer")
     * @Assert\Min(0)
     */
    protected $minimum;

    /**
     * @ORM\Column(type="boolean")
     *
     * @Assert\Type(type="bool")
     */
    protected $approved;

    /**
     * @ORM\ManyToOne(targetEntity="Coupon\CityBundle\Entity\City")
     */
    protected $city;

    /**
     * @ORM\ManyToOne(targetEntity="Coupon\StoreBundle\Entity\Store")
     */
    protected $store;

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @Assert\True(message = "Expiration date must be set after the publication date")
     */
    public function isValidDate()
    {
        if ($this->published_at == null || $this->expired_at == null) {
            return true;
        }

        return $this->published_at < $this->expired_at;
    }

    /**
     * Uploads offer photo by copying the file in the fiven $targetDir and
     * setting its path in the entity property.
     *
     * @param string $targetDir Full path of the directory where the photo is uploaded to
     */
    public function uploadPhoto($targetDir)
    {
        if (null === $this->photo) {
            return;
        }

        $filename = uniqid('coupon-').'-1.'.$this->photo->guessExtension();

        $this->photo->move($targetDir, $filename);

        $this->setPhoto($filename);
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
        $this->slug = Util::getSlug($name);
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set slug
     *
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set description
     *
     * @param text $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return text
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set terms
     *
     * @param text $terms
     */
    public function setTerms($terms)
    {
        $this->terms = $terms;
    }

    /**
     * Get terms
     *
     * @return text
     */
    public function getTerms()
    {
        return $this->terms;
    }

    /**
     * Set photo
     *
     * @param string $photo
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;
    }

    /**
     * Get photo
     *
     * @return string
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * Set price
     *
     * @param decimal $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * Get price
     *
     * @return decimal
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set discount
     *
     * @param decimal $discount
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
    }

    /**
     * Get discount
     *
     * @return decimal
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * Set published_at
     *
     * @param datetime $publishedAt
     */
    public function setPublishedAt($publishedAt)
    {
        $this->published_at = $publishedAt;
    }

    /**
     * Get published_at
     *
     * @return datetime
     */
    public function getPublishedAt()
    {
        return $this->published_at;
    }

    /**
     * Set expired_at
     *
     * @param datetime $expiredAt
     */
    public function setExpiredAt($expiredAt)
    {
        $this->expired_at = $expiredAt;
    }

    /**
     * Get expired_at
     *
     * @return datetime
     */
    public function getExpiredAt()
    {
        return $this->expired_at;
    }

    /**
     * Set purchases
     *
     * @param integer $purchases
     */
    public function setPurchases($purchases)
    {
        $this->purchases = $purchases;
    }

    /**
     * Get purchases
     *
     * @return integer
     */
    public function getPurchases()
    {
        return $this->purchases;
    }

    /**
     * Set minimum
     *
     * @param integer $minimum
     */
    public function setMinimum($minimum)
    {
        $this->minimum = $minimum;
    }

    /**
     * Get minimum
     *
     * @return integer
     */
    public function getMinimum()
    {
        return $this->minimum;
    }

    /**
     * Set approved
     *
     * @param boolean $approved
     */
    public function setApproved($approved)
    {
        $this->approved = $approved;
    }

    /**
     * Get approved
     *
     * @return boolean
     */
    public function isApproved()
    {
        return $this->approved;
    }

    /**
     * Set city
     *
     * @param Coupon\CityBundle\Entity\City $city
     */
    public function setCity(City $city)
    {
        $this->city = $city;
    }

    /**
     * Get city
     *
     * @return Coupon\CityBundle\Entity\City
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set store
     *
     * @param Coupon\StoreBundle\Entity\Store $store
     */
    public function setStore(Store $store)
    {
        $this->store = $store;
    }

    /**
     * Get store
     *
     * @return Coupon\StoreBundle\Entity\Store
     */
    public function getStore()
    {
        return $this->store;
    }
}
