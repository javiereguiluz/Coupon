<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * This file is part of the Coupon sample application.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Coupon\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Coupon\UserBundle\Entity\User;
use Coupon\OfferBundle\Entity\Purchase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Coupon\UserBundle\Form\Frontend\UserProfileType;
use Coupon\UserBundle\Form\Frontend\UserRegisterType;

class DefaultController extends Controller
{
    /**
     * Renders the user login form page
     */
    public function loginAction()
    {
        $request = $this->getRequest();
        $session = $request->getSession();

        $lastError = $request->attributes->get(
            SecurityContext::AUTHENTICATION_ERROR,
            $session->get(SecurityContext::AUTHENTICATION_ERROR)
        );

        return $this->render('UserBundle:Default:login.html.twig', array(
            'last_username' => $session->get(SecurityContext::LAST_USERNAME),
            'error'         => $lastError
        ));
    }

    /**
     * Renders the user login box displayed in the sidebar of every application page.
     * When the user is logged in, the box renders the basic user information and
     * the links to view/edit the profile and to log out.
     *
     * @param string $id The 'id' attribute used in the template
     */
    public function loginBoxAction($id = '')
    {
        $user = $this->get('security.context')->getToken()->getUser();

        $response = $this->render('UserBundle:Default:loginBox.html.twig', array(
            'id'   => $id,
            'user' => $user
        ));

        $response->setMaxAge(30);

        return $response;
    }

    /**
     * Renders and processes the user registration form
     */
    public function registerAction()
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getEntityManager();

        $user = new User();
        $user->isSubscribed(true);

        $form = $this->createForm(new UserRegisterType(), $user);

        if ($request->getMethod() == 'POST') {

            $form->bindRequest($request);

            if ($form->isValid()) {
                $user->setSalt(md5(time()));

                $encoder = $this->get('security.encoder_factory')->getEncoder($user);
                $hashedPassword = $encoder->encodePassword(
                    $user->getPassword(),
                    $user->getSalt()
                );
                $user->setPassword($hashedPassword);

                $em->persist($user);
                $em->flush();

                $this->get('session')->setFlash('info',
                    'Congratulations! You\'ve sucessfully registered in Coupon'
                );

                // Log in the user automatically
                $token = new UsernamePasswordToken($user, $user->getPassword(), 'users', $user->getRoles());
                $this->container->get('security.context')->setToken($token);

                return $this->redirect($this->generateUrl('homepage', array(
                    'city' => $user->getCity()->getSlug()
                )));
            }
        }

        return $this->render('UserBundle:Default:register.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * Renders and processes the user profile form
     */
    public function profileAction()
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getEntityManager();

        $user = $this->get('security.context')->getToken()->getUser();
        $form = $this->createForm(new UserProfileType(), $user);

        if ($request->getMethod() == 'POST') {
            $originalPassword = $form->getData()->getPassword();

            $form->bindRequest($request);

            if ($form->isValid()) {
                // If the user hasn't edited the password, save again the original password
                if (null == $user->getPassword()) {
                    $user->setPassword($originalPassword);
                } else {
                    $encoder = $this->get('security.encoder_factory')->getEncoder($user);
                    $hashedPassword = $encoder->encodePassword(
                        $user->getPassword(),
                        $user->getSalt()
                    );
                    $user->setPassword($hashedPassword);
                }

                $em->persist($user);
                $em->flush();

                $this->get('session')->setFlash('info',
                    'Your profile has been succesfully updated.'
                );

                return $this->redirect($this->generateUrl('user_profile'));
            }
        }

        return $this->render('UserBundle:Default:profile.html.twig', array(
            'user' => $user,
            'form' => $form->createView()
        ));
    }

    /**
     * Renders the purchases of the logged user
     */
    public function purchasesAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->get('security.context')->getToken()->getUser();

        $nearby = $em->getRepository('CityBundle:City')->findNearby(
            $user->getCity()->getId()
        );

        $purchases = $em->getRepository('UserBundle:User')->findAllPurchases($user->getId());

        return $this->render('UserBundle:Default:purchases.html.twig', array(
            'purchases' => $purchases,
            'nearby'    => $nearby
        ));
    }

    /**
     * Processes user purchases and renders the thank-you page.
     *
     * @param string $city   The slug of the city
     * @param string $slug   The slug of the offer
     */
    public function purchaseAction($city, $slug)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->get('security.context')->getToken()->getUser();

        // Only registered and logged in users can purchase offers
        if (null == $user || !$this->get('security.context')->isGranted('ROLE_USER')) {
            $this->get('session')->setFlash('info',
                'Please, register for free or sign in before purchasing an offer.'
            );

            return $this->redirect($this->generateUrl('user_login'));
        }

        $city = $em->getRepository('CityBundle:City')->findOneBySlug($city);
        if (!$city) {
            throw $this->createNotFoundException('The requested city is unavailable.');
        }

        $offer = $em->getRepository('OfferBundle:Offer')->findOneBy(array('city' => $city->getId(), 'slug' => $slug));
        if (!$offer) {
            throw $this->createNotFoundException('The requested offer is unavailable.');
        }

        // The same user can't buy an offer twice
        $purchase = $em->getRepository('OfferBundle:Purchase')->findOneBy(array(
            'offer' => $offer->getId(),
            'user'  => $user->getId()
        ));

        if (null != $purchase) {
            $purchasedAt = $purchase->getCreatedAt();

            $formatter = \IntlDateFormatter::create(
                $this->get('translator')->getLocale(),
                \IntlDateFormatter::LONG,
                \IntlDateFormatter::NONE
            );

            $this->get('session')->setFlash('error',
                'You can\'t buy the same offer more than once (you bought it on '.$formatter->format($purchasedAt).').'
            );

            return $this->redirect(
                $this->getRequest()->headers->get('Referer', $this->generateUrl('homepage'))
            );
        }

        $purchase = new Purchase();

        $purchase->setOffer($offer);
        $purchase->setUser($user);
        $purchase->setCreatedAt(new \DateTime());

        $em->persist($purchase);

        $offer->setPurchases($offer->getPurchases()+1);

        $em->flush();

        return $this->render('UserBundle:Default:purchase.html.twig', array(
            'offer' => $offer,
            'user'  => $user
        ));
    }
}
