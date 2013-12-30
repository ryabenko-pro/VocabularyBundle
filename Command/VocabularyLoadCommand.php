<?php

namespace NordUa\VocabularyBundle\Command;

use NordUa\VocabularyBundle\Repository\VocabularyRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use NordUa\VocabularyBundle\Document\Vocabulary;

use NordUa\VocabularyBundle\Parser\VocabularyFileLine;
use NordUa\VocabularyBundle\Parser\VocabularyTextParser;

class VocabularyLoadCommand extends ContainerAwareCommand {

  /** @var InputInterface */
  protected $input;

  /** @var OutputInterface */
  protected $output;
  
  protected $vocabulary = array();
  protected $documents = array();

  protected function configure() {
    $this
      ->setName('cleverbag:vocabulary:load')
      ->setDescription('Insert fixtures to the DB')
      ->addOption('dry-run', 'dry', InputOption::VALUE_NONE, "Just show changes, without applying them")
      ->addOption('type', 't', InputOption::VALUE_IS_ARRAY|InputOption::VALUE_OPTIONAL, "Vocabulary type(s) to load")
      ->addArgument('bundles', InputArgument::IS_ARRAY, 'Bundle list')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->input = $input;
    $this->output = $output;
    
    $bundles = $input->getArgument('bundles');
    $dryrun = filter_var($input->getOption('dry-run'), FILTER_VALIDATE_BOOLEAN);
    
    if (empty($bundles)) {
      $kernelBundles = $this->getContainer()->getParameter('kernel.bundles');
      $bundles = array_keys($kernelBundles);
    }
    
    $kernel = $this->getContainer()->get('kernel');
    /* @var $kernel Symfony\Component\HttpKernel\Kernel */
    
    $this->initVocabulary();
    
    $dm = $this->getDocumentManager();
    
    $this->documents = array();
    foreach ($bundles as $value) {
      $bundle = $kernel->getBundle($value);
      $path = $bundle->getPath() . "/Resources/vocabulary";
      
      if (is_dir($path)) {
        foreach (glob($path . "/*") as $filename) {
          if (!$this->isFileAcceptable($filename))
            continue;
          
          $this->readFile($filename);
        }
      }
    }
    if (!$dryrun) {
      $this->output->writeln("Flushing changes to database.");
      foreach ($this->documents as $document) {
        $dm->persist($document);
      }
      
      $dm->flush($this->documents);
    } else {
      $this->output->writeln("Changes was not flushed due to dry-run.");
    }
    
    $output->writeln("Done.");
  }
  
  private function isFileAcceptable($filename) {
    $basename = pathinfo($filename, PATHINFO_BASENAME);
    $parts = explode(".", $basename);
    
    $ext = array_pop($parts);
    $lang = array_pop($parts);
    $type = implode(".", $parts);
    
    $types = $this->input->getOption('type');
    
    if (!empty($types) && false === array_search($type, $types)) {
      return false;
    }
    
    return true;
  }
  
  private function readFile($filename) {
    $basename = pathinfo($filename, PATHINFO_BASENAME);
    $parts = explode(".", $basename);
    
    $ext = array_pop($parts);
    $lang = array_pop($parts);
    $type = implode(".", $parts);
    
    $oldCount = 0;
    if (isset($this->vocabulary[$type]))
      $oldCount = count($this->vocabulary[$type]);
    
    $oldDocumentsCount = count($this->documents);
    
    $content = file_get_contents($filename);
    $lines = VocabularyTextParser::parseFile($content);
    
    /* @var $line VocabularyFileLine */
    foreach ($lines as $line) {
      $slug = $line->getSlug();
      if ($slug) {
        $line->setSlug($slug);
      }
      
      $this->updateVocabularyValue($type, $lang, $line->getSlug(), $line->getValue(), $line->getParams());
    }
    
    $count = count($this->vocabulary[$type]);
    $newCount = $count - $oldCount;
    
    $newDocumentCount = count($this->documents);
    $updatedDocuments = $newDocumentCount - $oldDocumentsCount - $newCount;
    
    $this->output->writeln("{$newCount} new values and {$updatedDocuments} updated for type '{$type}' and language '{$lang}'.");
  }
  
  private function updateVocabularyValue($type, $lang, $slug, $value, $params) {
    if (!isset($this->vocabulary[$type]))
      $this->vocabulary[$type] = array();
    
    $value = trim($value);
    if (empty($slug)) {
      $slug = Vocabulary::slugify($value);
    }
    
    $voc = $this->getVocabularyRecord($type, $lang, $slug);
    
    if ($voc->getValue() != $value || $voc->getParams() != $params) {
      $voc->setValue($value);
      $voc->setParams($params);

      // Persist and flush them later!
      $this->documents[] = $voc;
    }
  }

  /**
   * @param string $type
   * @param string $lang
   * @param string $slug
   * @return \NordUa\VocabularyBundle\Document\Vocabulary
   */
  private function getVocabularyRecord($type, $lang, $slug) {
    if (isset($this->vocabulary[$type][$slug])) {
      return $this->vocabulary[$type][$slug];
    }
    
    $vocabulary = new Vocabulary();
    $vocabulary->setType($type);
    $vocabulary->setLang($lang);
    $vocabulary->setSlug($slug);
    $this->vocabulary[$type][$slug] = $vocabulary;
    
    return $vocabulary;
  }
  
  public function initVocabulary() {
    /* @var $voc \NordUa\VocabularyBundle\Document\Vocabulary */
    foreach ($this->getRepositoryVocabulary()->findAll() as $voc) {
      $type = $voc->getType();
      $slug = $voc->getSlug();
      if (!isset($this->vocabulary[$type]))
        $this->vocabulary[$type] = array();
        
      $this->vocabulary[$type][$slug] = $voc;
    }
  }
  
  /**
   * Shortcut to return the Doctrine MongoDB Document manager service.
   * @return \Doctrine\ODM\MongoDB\DocumentManager
   */
  public function getDocumentManager() {
    return $this->getContainer()->get('doctrine_mongodb')->getManager();
  }

  /** @return VocabularyRepository Description */
  public function getRepositoryVocabulary() {
    return $this->getDocumentManager()->getRepository('VocabularyBundle:Vocabulary');
  }
  
}
