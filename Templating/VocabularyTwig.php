<?php

namespace NordUa\VocabularyBundle\Templating;

use NordUa\VocabularyBundle\Service\VocabularyService;

/**
 * @author Sergey Ryabenko <ryabenko.sergey@gmail.com>
 */
class VocabularyTwig extends \Twig_Extension {

  /** @var VocabularyService */
  protected $vs;
  
  public function __construct(VocabularyService $vs) {
    $this->vs = $vs;
  }

  public function initRuntime(\Twig_Environment $environment) {
    $this->environment = $environment;
  }

  public function getGlobals() {
    return array(
      'vocabulary' => $this
    );
  }

  public function getName() {
    return 'vocabulary';
  }

  public function getFilters() {
    return array(
      'vocabularyValue' => new \Twig_Filter_Method($this, 'vocabularyValue'),
      'vocabularyValueExists' => new \Twig_Filter_Method($this, 'vocabularyValueExists'),
      'vocabularyValues' => new \Twig_Filter_Method($this, 'vocabularyValues'),
    );
  }

  public function getFunctions() {
    return array(
      'preload' => new \Twig_Filter_Method($this, 'preload'),
      'preloadTree' => new \Twig_Filter_Method($this, 'preloadTree'),
      'getRoots' => new \Twig_Filter_Method($this, 'getRoots'),
    );
  }

  public function vocabularyValue($slug, $type) {
    return $this->vs->vocabularyValue($slug, $type);
  }

  public function vocabularyValueExists($slug, $type) {
    return $this->vs->vocabularyValueExists($slug, $type);
  }

  public function vocabularyValues($slugs, $type) {
    return $this->vs->vocabularyValues($slugs, $type);
  }

  public function getRoots($type) {
    return $this->vs->getRoots($type);
  }

  public function preload($types) {
    return $this->vs->preload($types);
  }

  public function preloadTree($types, $parent = 'parent') {
    return $this->vs->preloadTree($types, $parent);
  }

}