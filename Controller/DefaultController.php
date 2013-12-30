<?php

namespace NordUa\VocabularyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller {

  /**
   * TODO: BC method, remove it
   * @Route("/vocabulary.get", name="vocabulary_get")
   * @param Request $request
   */
  public function vocabularyGetValuesBC(Request $request)
  {
    $lang    = $request->get('lang', 'ru');
    $type    = $request->get('type');
    $keyword = $request->get('term', null);
    
    $values = $this->getVocabularyRepository()->findByQuery($type, $lang, $keyword);
    
    $result = array();
    foreach ($values as $value) {
      $result[] = array(
        'id'    => $value->getId(),
        'label' => $value->getValue(),
        'value' => $value->getValue()
      );
    }
    
    $response = new Response(JsonHelper::encode($result));
    
    return $response;
  }

  /**
   * @Route("/vocabulary/{type}/{lang}", name="vocabulary_get", defaults={"lang": null})
   * @Route("/vocabulary/{type}/{lang}/{term}", defaults={"lang": null})
   * @param \Symfony\Component\HttpFoundation\Request $request
   */
  public function vocabularyGetValues($type, $lang, Request $request) {
    $keyword = $request->get('term', null);

    $values = $this->getVocabularyRepository()->findByQuery($type, $lang, $keyword);

    $result = array();
    foreach ($values as $value) {
      $result[] = array(
        'id' => $value->getId(),
        'label' => $value->getValue(),
        'value' => $value->getValue()
      );
    }

    $response = new Response(JsonHelper::encode($result));

    return $response;
  }
  
  /**
   * @return \NordUa\VocabularyBundle\Repository\VocabularyRepository
   */
  private function getVocabularyRepository() {
    return $this->container->get('doctrine_mongodb')->getManager()->getRepository('CommonVocabularyBundle:Vocabulary');
  }
  
}
