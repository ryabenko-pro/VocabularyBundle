<?php

namespace NordUa\VocabularyBundle\Form\Type;


use Doctrine\ODM\MongoDB\DocumentManager;
use NordUa\VocabularyBundle\Document\Vocabulary;
use NordUa\VocabularyBundle\Service\VocabularyService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\Form\Extension\Core\ChoiceList\LazyChoiceList;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;


class VocabularyChoiceType extends ChoiceType
{

  /** @var VocabularyService */
  protected $vs;

  public function __construct($vs) {
    $this->vs = $vs;
  }

  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $values = [];
    $labels = [];
    /* @var $doc Vocabulary */
    foreach ((array)$this->vs->getDocs($options['vocabulary']) as $doc) {
      $values[] = $doc->getSlug();
      $labels[] = $doc->getValue();
    }

    $options['choice_list'] = new ChoiceList($values, $labels);
//    $options['choice_list'] = new MyChoiceList($this->vs, $this);
    parent::buildForm($builder, $options);
  }

  public function setDefaultOptions(OptionsResolverInterface $resolver)
  {
    parent::setDefaultOptions($resolver);

    $resolver->setRequired(['vocabulary']);
    $resolver->setDefaults([
//      'choice_list' => new MyChoiceList($this->vs, $this),
    ]);
  }

  public function getName()
  {
    return "vocabulary_choice";
  }

}


//class MyChoiceList extends LazyChoiceList {
//
//  /* @var VocabularyChoiceType */
//  protected $form;
//
//  /** @var VocabularyService */
//  protected $vs;
//
//  public function __construct($vs, $form) {
//    $this->vs = $vs;
//    $this->form = $form;
//  }
//
//  protected function loadChoiceList()
//  {
//    $values = [];
//    $labels = [];
//    /* @var $doc Vocabulary */
//    foreach ((array)$this->vs->getDocs($this->form->builder->getOption('vocabulary')) as $doc) {
//      $values[] = $doc->getSlug();
//      $labels[] = $doc->getValue();
//    }
//
//    return new ChoiceList($values, $labels);
//  }
//}