<?php

namespace Drupal\dvf\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a VisualisationStyle annotation object.
 *
 * Plugin Namespace: Plugin\Visualisation\Style
 *
 * @see \Drupal\dvf\Plugin\VisualisationStyleInterface
 * @see \Drupal\dvf\Plugin\VisualisationStyleManagerInterface
 *
 * @Annotation
 */
class VisualisationStyle extends Plugin {

  /**
   * The unique identifier.
   *
   * @var string
   */
  public $id;

  /**
   * The label.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

}
