<?php

namespace NordUa\VocabularyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller {

  /**
   * @Route("/vocabulary/{type}/{lang}", name="vocabulary_get", defaults={"lang": null})
   * @Route("/vocabulary/{type}/{lang}/{term}", defaults={"lang": null})
   *
   * @param string $type
   * @param string $lang
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @return \Symfony\Component\HttpFoundation\Response
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

    $response = new Response(self::encode($result));

    return $response;
  }
  
  /**
   * @return \NordUa\VocabularyBundle\Repository\VocabularyRepository
   */
  private function getVocabularyRepository() {
    return $this->container->get('doctrine_mongodb')->getManager()->getRepository('VocabularyBundle:Vocabulary');
  }

  static public function encode($v) {
    return preg_replace_callback('/\\\\u([\da-f]{4})/i', function ($match) {
      $char = hexdec($match[1]);
      // А-Яа-яёЁ
      if ($char >= 0x410 && $char <= 0x42f || $char >= 0x430 && $char <= 0x44f || $char === 0x451 || $char === 0x401) {
        return chr(0xc0 | (0x1f & ($char >> 6))) . chr(0x80 | (0x3f & $char));
      } else {
        return $match[0];
      }
    }, json_encode($v));
  }

}
