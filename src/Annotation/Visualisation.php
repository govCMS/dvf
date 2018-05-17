<?php

namespace Drupal\dvf\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Visualisation annotation object.
 *
 * Plugin Namespace: Plugin\Visualisation.
 *
 * @see \Drupal\dvf\Plugin\VisualisationInterface
 * @see \Drupal\dvf\Plugin\VisualisationManager
 *
 * @Annotation
 */
class Visualisation extends Plugin {

  /**
   * The unique identifier.
   *
   * @var string
   */
  public $id;

}
