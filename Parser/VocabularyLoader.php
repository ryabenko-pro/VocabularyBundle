<?php

namespace NordUa\VocabularyBundle\Parser;

use Doctrine\ODM\MongoDB\DocumentManager;

class VocabularyLoader {
  
  /** @var DocumentManager */
  protected $dm;
  
  public function __construct(DocumentManager $dm) {
    $this->dm = $dm;
  }
  
  public function readFile($filename) {
    
  }
  
}