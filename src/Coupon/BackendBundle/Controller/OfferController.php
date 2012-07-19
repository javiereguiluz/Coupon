<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * This file is part of the Coupon sample application.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Coupon\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Coupon\OfferBundle\Entity\Offer;
use Coupon\BackendBundle\Form\OfferType;

/**
 * Offer controller.
 *
 */
class OfferController extends Controller
{
    /**
     * Lists all Offer entities.
     *
     */
    public function indexAction()
    {
        // If there is no city selected, then select the default city
        $sesion = $this->getRequest()->getSession();
        if (null == $slug = $sesion->get('city')) {
            $slug = $this->container->getParameter('coupon.default_city');
            $sesion->set('city', $slug);
        }

        $em = $this->getDoctrine()->getEntityManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage(19);

        $entities  = $paginator->paginate(
            $em->getRepository('CityBundle:City')->queryAllOffers($slug)
        )->getResult();

        return $this->render('BackendBundle:Offer:index.html.twig', array(
            'entities'  => $entities,
            'paginator' => $paginator
        ));
    }

    /**
     * Finds and displays a Offer entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('OfferBundle:Offer')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('The requested offer is unavailable.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('BackendBundle:Offer:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to create a new Offer entity.
     *
     */
    public function newAction()
    {
        $entity = new Offer();

        $em = $this->getDoctrine()->getEntityManager();

        $city = $em->getRepository('CityBundle:City')->findOneBySlug(
            $this->getRequest()->getSession()->get('city')
        );

        // Fill in the entity with some default values
        $entity->setCity($city);
        $entity->setPurchases(0);
        $entity->setMinimum(0);
        $entity->setPublishedAt(new \DateTime('now'));
        $entity->setExpiredAt(new \DateTime('tomorrow'));

        $form = $this->createForm(new OfferType(), $entity);

        return $this->render('BackendBundle:Offer:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Creates a new Offer entity.
     *
     */
    public function createAction()
    {
        $entity  = new Offer();
        $request = $this->getRequest();
        $form    = $this->createForm(new OfferType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('backend_offer_show', array('id' => $entity->getId())));
        }

        return $this->render('BackendBundle:Offer:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing Offer entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('OfferBundle:Offer')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('The requested offer is unavailable.');
        }

        $editForm = $this->createForm(new OfferType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('BackendBundle:Offer:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Offer entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('OfferBundle:Offer')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('The requested offer is unavailable.');
        }

        $editForm   = $this->createForm(new OfferType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('backend_offer_edit', array('id' => $id)));
        }

        return $this->render('BackendBundle:Offer:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Offer entity.
     *
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('OfferBundle:Offer')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('The requested offer is unavailable.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('backend_offer'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
