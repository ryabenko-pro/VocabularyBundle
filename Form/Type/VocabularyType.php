<?php

namespace NordUa\VocabularyBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;


class VocabularyType extends AbstractType
{

  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
      ->add('slug', 'text', ['constraints' => [new NotBlank()]])
      ->add('value', 'text', ['constraints' => [new NotBlank()]])
    ;
  }

  public function setDefaultOptions(OptionsResolverInterface $resolver)
  {
    $resolver->setDefaults([
      'data_class' => 'NordUa\VocabularyBundle\Document\Vocabulary',
    ]);
  }

  public function getName()
  {
    return "vocabulary";
  }

}