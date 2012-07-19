<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * This file is part of the Coupon sample application.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Coupon\UserBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\ExecutionContext;
use Coupon\CityBundle\Entity\City;

/**
 * Coupon\UserBundle\Entity\User
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Coupon\UserBundle\Entity\UserRepository")
 * @DoctrineAssert\UniqueEntity("email")
 * @Assert\Callback(methods={"isValidPin"})
 */
class User implements UserInterface
{
    /** Method required by UserInterface */
    public function equals(UserInterface $user)
    {
        return $this->getEmail() == $user->getEmail();
    }

    /** Method required by UserInterface */
    public function eraseCredentials()
    {
    }

    /** Method required by UserInterface */
    public function getRoles()
    {
        return array('ROLE_USER');
    }

    /** Method required by UserInterface */
    public function getUsername()
    {
        return $this->getEmail();
    }

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=100)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string $surname
     *
     * @ORM\Column(name="surname", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $surname;

    /**
     * @var string $email
     *
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     * @Assert\Email()
     */
    private $email;

    /**
     * @var string $password
     *
     * @ORM\Column(name="password", type="string", length=255)
     * @Assert\NotBlank(groups={"register"})
     * @Assert\MinLength(limit=6)
     */
    private $password;

    /**
     * @var string salt
     *
     * @ORM\Column(name="salt", type="string", length="255")
     */
    protected $salt;

    /**
     * @var text $address
     *
     * @ORM\Column(name="address", type="text")
     * @Assert\NotBlank()
     */
    private $address;

    /**
     * @var boolean $subscribed
     *
     * @ORM\Column(name="subscribed", type="boolean")
     * @Assert\Type(type="bool")
     */
    private $subscribed;

    /**
     * @var datetime $created_at
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Assert\DateTime()
     */
    private $created_at;

    /**
     * @var datetime $birthday
     *
     * @ORM\Column(name="birthday", type="datetime")
     * @Assert\DateTime()
     */
    private $birthday;

    /**
     * @var string $pin
     *
     * Stores the Personal Identification Number (pin) of the user.
     * This is an imaginary personal ID composed of up to 8 numbers
     * and a letter.
     *
     * @ORM\Column(name="pin", type="string", length=9)
     */
    private $pin;

    /**
     * @var string $credit_card
     *
     * Stores the credit/debit card number.
     *
     * @ORM\Column(name="credit_card", type="string", length=20)
     * @Assert\Regex("/\d{11,19}/")
     */
    private $credit_card;

    /**
     * @var integer $city
     *
     * @ORM\ManyToOne(targetEntity="Coupon\CityBundle\Entity\City", inversedBy="users")
     * @Assert\Type("Coupon\CityBundle\Entity\City")
     */
    private $city;

    public function __construct()
    {
        $this->created_at = new \DateTime();
    }

    public function __toString()
    {
        return $this->getName().' '.$this->getSurname();
    }

    /**
     * Custom validator that checks if the given $pin is valid.
     *
     * This fictional $pin is based on the National Identity Card of Spain:
     *
     *   Format:   from 1 to 8 numbers + 1 letter
     *   Examples: 12345678Z - 11111111H - 01234567L
     *
     * Numbers can be randomly chosen, but the letter depends on the
     * numbers and acts as a global check. How to guess the letter?
     *
     *   1. Get the 'mod 23' of the number (e.g.: 12345678 mod 23 = 14).
     *   2. Choose the letter from the following table:
     *
     *   +--------+----+----+----+----+----+----+----+----+----+----+----+----+
     *   | mod 23 |  0 |  1 |  2 |  3 |  4 |  5 |  6 |  7 |  8 |  9 | 10 | 11 |
     *   +--------+----+----+----+----+----+----+----+----+----+----+----+----+
     *   | letter |  T |  R |  W |  A |  G |  M |  Y |  F |  P |  D |  X |  B |
     *   +--------+----+----+----+----+----+----+----+----+----+----+----+----+
     *   | mod 23 | 12 | 13 | 14 | 15 | 16 | 17 | 18 | 19 | 20 | 21 | 22 |    |
     *   +--------+----+----+----+----+----+----+----+----+----+----+----+----+
     *   | letter |  N |  J |  Z |  S |  Q |  V |  H |  L |  C |  K |  E |    |
     *   +--------+----+----+----+----+----+----+----+----+----+----+----+----+
     *   
     */
    public function isValidPin(ExecutionContext $context)
    {
        $property_name = $context->getPropertyPath() . '.pin';
        $pin = $this->getPin();

        // Check that the format is correct
        if (0 === preg_match("/\d{1,8}[a-z]/i", $pin)) {
            $context->setPropertyPath($property_name);
            $context->addViolation(
                "The given PIN doesn't have the proper format"
                ." (from 1 to 8 numbers followed by 1 letter without spaces)",
                array(), null
            );

            return;
        }

        // Check that the letter matches the algorithm
        $number = substr($pin, 0, -1);
        $letter = strtoupper(substr($pin, -1));
        if ($letter != substr("TRWAGMYFPDXBNJZSQVHLCKE", strtr($number, "XYZ", "012")%23, 1)) {
            $context->setPropertyPath($property_name);
            $context->addViolation(
                "The PIN number and letter don't match."
                ." Check both the number and the letter.",
                array(), null
            );
        }
    }

    /**
     * @Assert\True(message = "You must be at least 18 years old to sign up.")
     */
    public function isAdult()
    {
        return $this->birthday <= new \DateTime('today - 18 years');
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
     * Set surname
     *
     * @param string $surname
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;
    }

    /**
     * Get surname
     *
     * @return string
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * Set email
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
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
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
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
     * Get salt
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
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
     * Set subscribed
     *
     * @param boolean $subscribed
     */
    public function setSubscribed($subscribed)
    {
        $this->subscribed = $subscribed;
    }

    /**
     * Get subscribed
     *
     * @return boolean
     */
    public function isSubscribed()
    {
        return $this->subscribed;
    }

    /**
     * Set created_at
     *
     * @param datetime $createdAt
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
     * Set birthday
     *
     * @param datetime $birthday
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;
    }

    /**
     * Get birthday
     *
     * @return datetime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * Set pin
     *
     * @param string $pin
     */
    public function setPin($pin)
    {
        $this->pin = $pin;
    }

    /**
     * Get pin
     *
     * @return string
     */
    public function getPin()
    {
        return $this->pin;
    }

    /**
     * Set credit_card
     *
     * @param string $creditCard
     */
    public function setCreditCard($creditCard)
    {
        $this->credit_card = $creditCard;
    }

    /**
     * Get credit_card
     *
     * @return string
     */
    public function getCreditCard()
    {
        return $this->credit_card;
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
    public function getcity()
    {
        return $this->city;
    }
}
