<?php

namespace NordUa\VocabularyBundle\Parser;

/**
 * @author Sergey Ryabenko <ryabenko.sergey@gmail.com>
 */
class VocabularyTextParser {
  
  static public function parseFile($content) {
    $lines = array();
    
    $commonParams = array();
    foreach (array_map("trim", explode("\n", $content)) as $lineStr) {
      if (empty($lineStr) || '#' == mb_substr($lineStr, 0, 1))
        continue;
      
      $line = self::parseLine($lineStr);
      if ($line->isParams()) {
        $commonParams = $line->getParams();
        continue;
      }
      
      if ($line->isEndOfParams()) {
        $commonParams = array();
        continue;
      }
      
      if ($commonParams)
        $line->prependParams($commonParams);
      
      $lines[] = $line;
    }
    
    return $lines;
  }
  
  static public function parseLine($line) {
    $params = self::parseParams($line);
    
    $noParams = preg_replace("/\[.*\]/", "", $line);
    
    $parts = array_map("trim", explode(":", $noParams));
    $value = array_pop($parts);
    $slug = array_pop($parts);

    $line = new VocabularyFileLine($slug, $value, $params);
    
    return $line;
  }

  static private function parseParams($line) {
    $params = array();
    
    $escaped = strtr($line, array("&" => "&amp;", "\," => "&comma;"));
    
    $matches = array();
    if (0 == preg_match('/\[([^\[]+)\]/', $escaped, $matches))
      return $params;
    
    $paramsMatches = array_map(function($val) { 
      return strtr($val, array("&comma;" => ",", "&amp;" => "&"));
    }, explode(",", $matches[1]));
    
    foreach ($paramsMatches as $match) {
      $parts = array_map("trim", explode("=", $match));
      $key = array_shift($parts);
      $value = array_shift($parts) ?: true;
      
      $params[$key] = $value;
    }
    
    
    return $params;
  }
}

