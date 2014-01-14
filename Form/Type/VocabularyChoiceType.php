<?php

namespace NordUa\VocabularyBundle\Form\Type;


use NordUa\VocabularyBundle\Document\Vocabulary;
use NordUa\VocabularyBundle\Service\VocabularyService;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


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
    parent::buildForm($builder, $options);
  }

  public function setDefaultOptions(OptionsResolverInterface $resolver)
  {
    parent::setDefaultOptions($resolver);

    $resolver->setRequired(['vocabulary']);
  }

  public function getName()
  {
    return "vocabulary_choice";
  }

}
