<?php

namespace NordUa\VocabularyBundle;

use NordUa\VocabularyBundle\DependencyInjection\Compiler\FormPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;


class VocabularyBundle extends Bundle
{
  /**
   * {@inheritdoc}
   */
  public function build(ContainerBuilder $container)
  {
    parent::build($container);
    $container->addCompilerPass(new FormPass());
  }
}
