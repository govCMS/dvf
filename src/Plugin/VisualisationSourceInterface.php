<?php

namespace Drupal\dvf\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Provides an interface defining a VisualisationSource plugin.
 */
interface VisualisationSourceInterface extends \Countable, \Iterator, PluginInspectionInterface {

  /**
   * Returns available fields on the source.
   *
   * @return array
   *   An array of available fields where the keys are the machine names
   *   and values are the labels.
   */
  public function getFields();

}
