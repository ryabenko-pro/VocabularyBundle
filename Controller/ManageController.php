<?php

namespace NordUa\VocabularyBundle\Controller;

use NordUa\VocabularyBundle\Document\Vocabulary;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use NordUa\VocabularyBundle\Form\Type\VocabularyType;


/**
 * @Route("/manage/vocabulary")
 */
class ManageController extends Controller
{

  /**
   * @Route("/{type}/{lang}", name="vocabulary_manage_index")
   */
  public function indexValues($type, $lang, Request $request)
  {
    $query = $this->getDocumentManager()
      ->getRepository('VocabularyBundle:Vocabulary')->createQueryBuilder()
      ->field('type')->equals($type)
      ->field('lang')->equals($lang)
      ->sort('slug', 'DESC')->getQuery();

    $paginator = $this->get('knp_paginator');
    $pagination = $paginator->paginate($query, $request->get('page', 1), 2);
    $pagination->setUsedRoute('vocabulary_manage_index');
    $pagination->setPageRange(10);

    return $this->render('VocabularyBundle:Manage:index.html.twig', [
      'docs' => $pagination, 'type' => $type, 'lang' => $lang
    ]);
  }

  /**
   * @Route("/{type}/{lang}/new", name="vocabulary_manage_new")
   * @Template("VocabularyBundle:Manage:edit.html.twig")
   */
  public function newAction($type, $lang, Request $request)
  {
    $object = new Vocabulary();
    $object->setLang($lang)
      ->setType($type);

    $this->getDocumentManager()->persist($object);

    return $this->processForm($object, $type, $lang);
  }

  /**
   * @Route("/{type}/{lang}/{id}/edit", name="vocabulary_manage_edit")
   * @Template("VocabularyBundle:Manage:edit.html.twig")
   */
  public function editAction($type, $lang, $id)
  {
    $object = $this->getDocumentVocabulary($id);

    return $this->processForm($object, $type, $lang);
  }


  public function processForm($object, $type, $lang)
  {
    $request = $this->get('request');
    $form = $this->createForm(new VocabularyType(), $object);

    $form->handleRequest($request);

    if ($form->isValid()) {
      $this->get('session')->getFlashBag()->add('success', 'Изменения сохранены');
      $dm = $this->getDocumentManager();
      $dm->flush($object);

      return $this->redirect($this->generateUrl("vocabulary_manage_index", ['type' => $type, 'lang' => $lang]));
    }

    return array(
      'action_path' => !$object->getId() ?
          $this->generateUrl('vocabulary_manage_new', ['type' => $type, 'lang' => $lang]) :
          $this->generateUrl('vocabulary_manage_edit', ['type' => $type, 'lang' => $lang, 'id' => $object->getId()]
          ),
      'form' => $form->createView(),
      'object' => $object,
      'type' => $type,
      'lang' => $lang
    );
  }

  /**
   * @Route("/{id}/delete", name="vocabulary_manage_delete")
   */
  public function deleteAction($id)
  {
    $object = $this->getDocumentVocabulary($id);

    $this->getDocumentManager()->remove($object);
    $this->getDocumentManager()->flush($object);

    $this->get('session')->getFlashBag()->add('success', 'Удаление прошло успешно.');

    return $this->redirect($this->generateUrl('vocabulary_manage_index', ['type' => $type, 'lang' => $lang]));
  }

  /**
   * @param $id
   * @return Vocabulary
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   */
  private function getDocumentVocabulary($id)
  {
    $doc = $this->getDocumentManager()->getRepository('VocabularyBundle:Vocabulary')->find($id);

    if (!$doc)
      throw $this->createNotFoundException();

    return $doc;
  }

  /**
   * @return \Doctrine\ODM\MongoDB\DocumentManager
   */
  private function getDocumentManager()
  {
    return $this->get('doctrine.odm.mongodb.document_manager');
  }

}
