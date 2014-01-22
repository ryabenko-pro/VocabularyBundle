<?php

namespace VocabularyBundle\Form\Transformer;

use NordUa\VocabularyBundle\Repository\VocabularyRepository;
use NordUa\VocabularyBundle\Service\VocabularyService;
use Symfony\Component\Form\DataTransformerInterface;

class VocabularyTransformer implements DataTransformerInterface {

  /** @var VocabularyService */
  protected $vs;

  public function __construct($vs, $type, $lang) {
    $this->vs = $vs;
    $this->type = $type;
    $this->lang = $lang;
  }

  /**
   * @param mixed $slug
   * @return string
   */
  public function transform($slug) {
    if (is_null($slug))
      return "";
    
    $word = $this->vs->vocabularyValue($slug, $this->type);
    
    if (!$word)
      return "";
    
    return $word->getValue();
  }

  /**
   * 
   * @param string $value
   * @return mixed
   */
  public function reverseTransform($value) {
    if (!$value) 
      return null;
    
    $id = $this->vs->getVocabularyRepository()->getSlugsByValues($this->type, $this->lang, $value);
    return array_pop($id);
  }

}