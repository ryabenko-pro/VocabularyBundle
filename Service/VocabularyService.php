<?php

namespace NordUa\VocabularyBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use NordUa\VocabularyBundle\Document\Vocabulary;

/**
 * @author Sergey Ryabenko <ryabenko.sergey@gmail.com>
 */
class VocabularyService {

  /** @var DocumentManager */
  protected $dm;
  
  protected $lang;
  /** Preloaded types */
  protected $types = array();
  
  /** Documents groupped by lang and types */
  protected $documents = array();
  /** Documents with no parents */
  protected $roots = array();
  
  public function __construct($lang, DocumentManager $dm, $types = null) {
    $this->dm = $dm;
    $this->lang = $lang;
    
    if ($types) 
      $this->preload($types);
  }
  
  public function preload($types) {
    return $this->preloadTree($types, null);
  }
  
  public function preloadTree($types, $parent = 'parent') {
    // TODO: preload only on vocabulary usage!
    $types = (array)$types;
    $newTypes = array_diff($types, array_keys($this->types));
    
    // No new types to preload
    if (0 == count($newTypes))
      return;
    
    foreach ($newTypes as $type)
      $this->documents[$type] = array();
    
    $documents = $this->getSortedDocuments($newTypes, $parent);
    
    $parents = array();
    /* @var $doc Vocabulary */
    foreach ($documents as $doc) {
      $type = $doc->getType();
      
      $this->documents[$type][$doc->getSlug()] = $doc;
      
      // TREE PART
      if (!is_null($parent)) {
        if ($doc->hasParam($parent)) {
          $parentKey = $doc->getLang() . '_' . $doc->getType() . '_' . $doc->getParam($parent);
          if (isset($parents[$parentKey])) {
            $doc->setParent($parents[$parentKey]);
            $parents[$parentKey]->addChild($doc);
          }
        } else {
          if (!isset($this->roots[$type]))
            $this->roots[$type] = array();

          $this->roots[$type][$doc->getSlug()] = $doc;
          $parents[$doc->getLang() . '_' . $doc->getType() . '_' . $doc->getSlug()] = $doc;
        } 
      } // END OF TREE
      
    }
    
    foreach ($types as $type)
      $this->types[$type] = true;
  }
  
  private function getSortedDocuments($types, $parent) {
    $documents = $this->getVocabularyRepository()->preload($this->lang, $types);
    
    if (!$parent) 
      return $documents;
    
    $parents = array();
    $childs = array();
    foreach ($documents as $doc) {
      if (!$doc->hasParam($parent))
        $parents[] = $doc;
      else
        $childs[] = $doc;
    }
    
    return array_merge($parents, $childs);
  }
  
  public function getRoots($type) {
    if (!isset($this->roots[$type]))
      return array();
    
    return $this->roots[$type];
  }
  
  public function vocabularyValueExists($slug, $type) {
    if (empty($slug))
      return false;
    
    $empty = $this->vocabularyValue($slug, $type);
    
    return !empty($empty);
  }
  
  public function vocabularyValue($slug, $type) {
    if (empty($slug))
      return "";
    
    if (isset($this->types[$type])) {
      if (isset($this->documents[$type]) && isset($this->documents[$type][$slug])) {
        return $this->documents[$type][$slug];
      }
      
      return "";
    }
    
    $vr = $this->getVocabularyRepository();
    $doc = $vr->getOneBySlug($type, $slug, $this->lang);
    
    if (!$doc) 
      return $slug;
    
    return $doc;
  }

  public function vocabularyValues($slugs, $type) {
    if (!is_array($slugs))
      return array();
    
    if (isset($this->types[$type])) {
      $result = array();
      foreach ($slugs as $slug) {
        if (isset($this->documents[$type]) && isset($this->documents[$type][$slug]))
          $result[] = $this->documents[$type][$slug];
      }
      
      return $result;
    }
    
    $vr = $this->getVocabularyRepository();
    
    $result = array();
    foreach ($vr->getBySlugs($type, $slugs, $this->lang) as $doc) 
      $result[] = $doc;
    
    return $result;
  }
  
  public function getDocs($type) {
    if (isset($this->types[$type])) {
      return $this->documents[$type];
    }
    
    $result = array();
    $vr = $this->getVocabularyRepository();
    foreach ($vr->getByTypeAndLang($type, $this->lang) as $doc) 
      $result[] = $doc;
    
    return $result;
  }

  /**
   * @return \NordUa\VocabularyBundle\Repository\VocabularyRepository
   */
  protected function getVocabularyRepository() {
    return $this->dm->getRepository('CommonVocabularyBundle:Vocabulary');
  }
  
}