<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * This file is part of the Coupon sample application.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Coupon\StoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Coupon\OfferBundle\Entity\Offer;
use Coupon\OfferBundle\Form\Extranet\OfferType;
use Coupon\StoreBundle\Form\Extranet\StoreType;

class ExtranetController extends Controller
{
    /**
     * Renders the store login form page
     */
    public function loginAction()
    {
        $request = $this->getRequest();
        $session = $request->getSession();

        $lastError = $request->attributes->get(
            SecurityContext::AUTHENTICATION_ERROR,
            $session->get(SecurityContext::AUTHENTICATION_ERROR)
        );

        return $this->render('StoreBundle:Extranet:login.html.twig', array(
            'error' => $lastError
        ));
    }

    /**
     * Renders the homepage of each store extranet
     */
    public function homepageAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $store = $this->get('security.context')->getToken()->getUser();
        $offers = $em->getRepository('StoreBundle:Store')->findLatestOffers($store->getId(), 50);

        return $this->render('StoreBundle:Extranet:homepage.html.twig', array(
            'offers' => $offers
        ));
    }

    /**
     * Renders the purchases of the given offer
     *
     * @param string $id the id of the offer
     */
    public function purchasesAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $purchases = $em->getRepository('OfferBundle:Offer')->findPurchasesByOffer($id);

        return $this->render('StoreBundle:Extranet:purchases.html.twig', array(
            'offer'     => $purchases[0]->getOffer(),
            'purchases' => $purchases
        ));
    }

    /**
     * Renders the form to add a new offer and processes it
     */
    public function newOfferAction()
    {
        $request = $this->getRequest();

        $offer = new Offer();
        $form = $this->createForm(new OfferType(), $offer);

        if ($request->getMethod() == 'POST') {
           $form->bindRequest($request);

           if ($form->isValid()) {
               // fill in the Offer properties that a store cannot set
               $store = $this->get('security.context')->getToken()->getUser();
               $offer->setPurchases(0);
               $offer->setApproved(false);
               $offer->setStore($store);
               $offer->setCity($store->getCity());

               // Copy the offer photo and save its path
               $offer->uploadPhoto($this->container->getParameter('coupon.images_dir'));

               $em = $this->getDoctrine()->getEntityManager();
               $em->persist($offer);
               $em->flush();

               // Use the ACEL permissions to ensure that the offer can only be edited by this store
               $objectId  = ObjectIdentity::fromDomainObject($offer);
               $userId = UserSecurityIdentity::fromAccount($store);

               $acl = $this->get('security.acl.provider')->createAcl($objectId);
               $acl->insertObjectAce($userId, MaskBuilder::MASK_OPERATOR);
               $this->get('security.acl.provider')->updateAcl($acl);

               return $this->redirect($this->generateUrl('extranet_homepage'));
           }
       }

        return $this->render('StoreBundle:Extranet:form.html.twig', array(
            'action' => 'new',
            'form'   => $form->createView()
        ));
    }

    
    /**
     * Renders the form to edit Offer entities
     * 
     * @param string $id The id of the offer
     */
    public function editOfferAction($id)
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getEntityManager();

        $offer = $em->getRepository('OfferBundle:Offer')->find($id);

        if (!$offer) {
            throw $this->createNotFoundException('The requested offer is unavailable.');
        }

        // Check that the user can edit this offer
        if (false === $this->get('security.context')->isGranted('EDIT', $offer)) {
            throw new AccessDeniedException();
        }

        // Offers can only be edited if they haven't been approved yet
        if ($offer->isApproved()) {
            $this->get('session')->setFlash('error',
                'Offer cannot be edited once it has been approved by site administrators'
            );

            return $this->redirect($this->generateUrl('extranet_homepage'));
        }

        $form = $this->createForm(new OfferType(), $offer);

        if ($request->getMethod() == 'POST') {
            $originalPhotoPath = $form->getData()->getPhoto();

            $form->bindRequest($request);

            if ($form->isValid()) {
                // If the user hasn't edited the photo, save the original photo path
                if (null == $offer->getPhoto()) {
                    $offer->setPhoto($originalPhotoPath);
                } else {
                    $offer->uploadPhoto($this->container->getParameter('coupon.images_dir'));

                    // Delete previous photo file
                    unlink($this->container->getParameter('coupon.images_dir').$originalPhotoPath);
                }

                $em->persist($offer);
                $em->flush();

                return $this->redirect($this->generateUrl('extranet_homepage'));
            }
        }

        return $this->render('StoreBundle:Extranet:form.html.twig', array(
            'action' => 'edit',
            'offer'  => $offer,
            'form'   => $form->createView()
        ));
    }

    /**
     * Renders the form to view and edit the profile of the store
     */
    public function profileAction()
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getEntityManager();

        $store = $this->get('security.context')->getToken()->getUser();
        $form = $this->createForm(new StoreType(), $store);

        if ($request->getMethod() == 'POST') {
            $originalPassword = $form->getData()->getPassword();

            $form->bindRequest($request);

            if ($form->isValid()) {
                // If the user hasn't edited the password, save again the original password
                if (null == $store->getPassword()) {
                    $store->setPassword($originalPassword);
                } else {
                    $encoder = $this->get('security.encoder_factory')->getEncoder($store);
                    $hashedPassword = $encoder->encodePassword(
                        $store->getPassword(),
                        $store->getSalt()
                    );
                    $store->setPassword($hashedPassword);
                }

                $em->persist($store);
                $em->flush();

                $this->get('session')->setFlash('info',
                    'Your profile has been successfully updated'
                );

                return $this->redirect($this->generateUrl('extranet_homepage'));
            }
        }

        return $this->render('StoreBundle:Extranet:profile.html.twig', array(
            'store' => $store,
            'form'  => $form->createView()
        ));
    }
}
