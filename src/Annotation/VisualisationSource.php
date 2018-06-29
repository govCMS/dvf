<?php

namespace Drupal\dvf\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a VisualisationSource annotation object.
 *
 * Plugin Namespace: Plugin\Visualisation\Source.
 *
 * @see \Drupal\dvf\Plugin\VisualisationSourceInterface
 * @see \Drupal\dvf\Plugin\VisualisationSourceManagerInterface
 *
 * @Annotation
 */
class VisualisationSource extends Plugin {

  /**
   * The unique identifier.
   *
   * @var string
   */
  public $id;

  /**
   * The label.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The category.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $category;

  /**
   * The visualisation types this plugins supports.
   *
   * @var string
   */
  public $visualisation_types;

}
