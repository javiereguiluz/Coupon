<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * This file is part of the Coupon sample application.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Coupon\StoreBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use Coupon\OfferBundle\Util\Util;
use Coupon\CityBundle\Entity\City;

/**
 * @ORM\Entity(repositoryClass="Coupon\StoreBundle\Entity\StoreRepository")
 */
class Store implements UserInterface
{
    /** Method required by UserInterface */
    public function equals(UserInterface $user)
    {
        return $this->getLogin() == $user->getLogin();
    }

    /** Method required by UserInterface */
    public function eraseCredentials()
    {
    }

    /** Method required by UserInterface */
    public function getRoles()
    {
        return array('ROLE_STORE');
    }

    /** Method required by UserInterface */
    public function getUsername()
    {
        return $this->getLogin();
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $slug;

    /**
     * @ORM\Column(type="string", length=10)
     */
    protected $login;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $salt;

    /**
     * @ORM\Column(type="text")
     */
    protected $description;

    /**
     * @ORM\Column(type="text")
     */
    protected $address;

    /**
     * @ORM\ManyToOne(targetEntity="Coupon\CityBundle\Entity\City")
     */
    protected $city;

    public function __toString()
    {
        return $this->getName();
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
     * Set login
     *
     * @param string $login
     */
    public function setLogin($login)
    {
        $this->login = $login;
    }

    /**
     * Get login
     *
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Set password
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Get salt
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set salt
     *
     * @param string $salt
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
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
     * Set address
     *
     * @param text $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * Get address
     *
     * @return text
     */
    public function getAddress()
    {
        return $this->address;
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
     * Get City
     *
     * @return Coupon\CityBundle\Entity\City
     */
    public function getCity()
    {
        return $this->city;
    }
}
