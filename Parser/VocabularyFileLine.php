<?php

namespace NordUa\VocabularyBundle\Parser;

class VocabularyFileLine {

  protected $slug;
  protected $value;
  protected $params;

  public function __construct($slug, $value, $params) {
    $this->slug = $slug;
    $this->value = $value;
    $this->params = $params;
  }

  public function getSlug() {
    return $this->slug;
  }

  public function getValue() {
    return $this->value;
  }

  public function getParams() {
    return $this->params;
  }

  public function prependParams($params) {
    return $this->params += $params;
  }
  
  public function isParams() {
    return empty($this->value) && !empty($this->params);
  }

  public function isEndOfParams() {
    return '---' == $this->value;
  }

  public function setSlug($slug) {
    $this->slug = $slug;
  }
  
}