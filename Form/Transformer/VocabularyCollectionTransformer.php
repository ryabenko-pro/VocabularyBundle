<?php

namespace VocabularyBundle\Form\Transformer;

use NordUa\VocabularyBundle\Document\Vocabulary;
use NordUa\VocabularyBundle\Service\VocabularyService;
use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\MongoDB\Collection;

class VocabularyCollectionTransformer implements DataTransformerInterface {

  /** @var VocabularyService */
  protected $vs;

  public function __construct($vs, $type, $lang) {
    $this->vs = $vs;
    $this->type = $type;
    $this->lang = $lang;
  }

  /**
   * @param Collection $slugs
   * @return string
   */
  public function transform($slugs) {
    if (is_null($slugs))
      return "";
    
    $words = $this->vs->vocabularyValues($this->type, $slugs);
    
    if (empty($words))
      return "";
    
    $result = array();
    /* @var $doc Vocabulary */
    foreach ($words as $doc)
      $result[] = $doc->getValue();
    
    return implode(", ", $result);
  }

  public function reverseTransform($value) {
    if (!$value) 
      return null;
    
    $values = explode(",", $value);
    $words = array();
    foreach ($values as $word) {
      $word = trim($word);
      
      if (empty($word))
        continue;
      
      $words[] = $word;
    }
    
    $ids = $this->vs->getVocabularyRepository()->getSlugsByValues($this->type, $this->lang, $words);
    return $ids;
  }

}