<?php

namespace Drupal\dvf\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a VisualisationSource annotation object.
 *
 * Plugin Namespace: Plugin\Visualisation\Source
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
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * The category.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $category;

  /**
   * The type.
   *
   * @var string
   */
  public $type;

}
