<?php

namespace NordUa\VocabularyBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(collection="vocabulary", repositoryClass="NordUa\VocabularyBundle\Repository\VocabularyRepository")
 * @MongoDB\UniqueIndex(keys={"lang"="asc", "type"="asc", "slug"="asc"})
 */
class Vocabulary {

  /**
   * @MongoDB\Id(strategy="INCREMENT")
   */
  protected $id;

  /**
   * @MongoDB\String
   */
  protected $lang;

  /**
   * @MongoDB\String
   */
  protected $type;

  /**
   * @MongoDB\String
   */
  protected $slug;

  /**
   * @MongoDB\String
   * @MongoDB\Index
   */
  protected $value;

  /**
   * @MongoDB\Hash
   */
  protected $params;
  
  /** @var Vocabulary */
  protected $parent = null;
  protected $children = array();

  /**
   * @MongoDB\PrePersist
   */
  public function prePersist() {
    if (!$this->slug)
      $this->slug = self::slugify($this->value);
  }

  /**
   * Set lang
   *
   * @param string $lang
   * @return Vocabulary
   */
  public function setLang($lang) {
    $this->lang = $lang;
    return $this;
  }

  /**
   * Get lang
   *
   * @return string $lang
   */
  public function getLang() {
    return $this->lang;
  }

  /**
   * Set value
   *
   * @param string $value
   * @return Vocabulary
   */
  public function setValue($value) {
    $this->value = $value;
    return $this;
  }

  /**
   * Get value
   *
   * @return string $value
   */
  public function getValue() {
    return $this->value;
  }

  /**
   * Get id
   *
   * @return id $id
   */
  public function getId() {
    return $this->id;
  }

  /**
   * Set type
   *
   * @param string $type
   * @return Vocabulary
   */
  public function setType($type) {
    $this->type = $type;
    return $this;
  }

  /**
   * Get type
   *
   * @return string $type
   */
  public function getType() {
    return $this->type;
  }

  public function __toString() {
    return $this->getValue();
  }
  
  public function toArray() {
    return array(
      'value' => $this->value,
      'slug' => $this->slug,
      'params' => $this->params,
    );
  }

  /**
   * Set slug
   *
   * @param string $slug
   * @return \Vocabulary
   */
  public function setSlug($slug) {
    $this->slug = $slug;
    return $this;
  }

  /**
   * Get slug
   *
   * @return string $slug
   */
  public function getSlug() {
    return $this->slug;
  }

  /**
   * Set params
   *
   * @param hash $params
   * @return \Vocabulary
   */
  public function setParams($params) {
    $this->params = $params;
    return $this;
  }

  /**
   * Get params
   *
   * @return hash $params
   */
  public function getParams() {
    return $this->params;
  }

  /**
   * Get params
   *
   * @return hash $params
   */
  public function getParam($name, $default = null) {
    if (!$this->hasParam($name))
      return $default;
    
    return $this->params[$name];
  }
  
  public function hasParam($name) {
    return array_key_exists($name, $this->params);
  }
  
  public function setParent($parent) {
    $this->parent = $parent;
  }

  public function addChild($child) {
    $this->children[$child->getSlug()] = $child;
  }
  
  /** @return Vocabulary */
  public function getParent() {
    return $this->parent;
  }
  
  public function getChildren() {
    return $this->children;
  }
  
  static public function slugify($text) {
    // replace non letter or digits by - 
    $text = preg_replace('~[^\\pL\d]+~u', '-', $text);

    // trim 
    $text = trim($text, '-');

    // transliterate 
    $text = self::rus2translit($text);

    // lowercase 
    $text = strtolower($text);

    // remove unwanted characters 
    $text = preg_replace('~[^-\w]+~', '', $text);
    
    return $text;
  }

  static protected function rus2translit($string) {
    $converter = array(
      'а' => 'a', 'б' => 'b', 'в' => 'v',
      'г' => 'g', 'д' => 'd', 'е' => 'e',
      'ё' => 'e', 'ж' => 'zh', 'з' => 'z',
      'и' => 'i', 'й' => 'y', 'к' => 'k',
      'л' => 'l', 'м' => 'm', 'н' => 'n',
      'о' => 'o', 'п' => 'p', 'р' => 'r',
      'с' => 's', 'т' => 't', 'у' => 'u',
      'ф' => 'f', 'х' => 'h', 'ц' => 'c',
      'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
      'ь' => "'", 'ы' => 'y', 'ъ' => "'",
      'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
      'А' => 'A', 'Б' => 'B', 'В' => 'V',
      'Г' => 'G', 'Д' => 'D', 'Е' => 'E',
      'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z',
      'И' => 'I', 'Й' => 'Y', 'К' => 'K',
      'Л' => 'L', 'М' => 'M', 'Н' => 'N',
      'О' => 'O', 'П' => 'P', 'Р' => 'R',
      'С' => 'S', 'Т' => 'T', 'У' => 'U',
      'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
      'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch',
      'Ь' => "'", 'Ы' => 'Y', 'Ъ' => "'",
      'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
    );
    return strtr($string, $converter);
  }
}
