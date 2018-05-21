<?php

namespace Drupal\dvf;

use Drupal\Component\Utility\Html;

/**
 * Provides common methods for field types.
 */
trait FieldTypeTrait {

  /**
   * Returns a list of visualisation source plugin options.
   *
   * @return array
   *   An array of visualisation source plugin options.
   */
  protected function getVisualisationSourceOptions() {
    $plugin_options = [];
    /** @var \Drupal\dvf\Plugin\VisualisationSourceManagerInterface $plugin_manager */
    $plugin_manager = \Drupal::service('plugin.manager.visualisation.source');

    foreach ($plugin_manager->getDefinitionsByType('url') as $plugin_id => $plugin) {
      $plugin_options[(string) $plugin['category']][$plugin_id] = Html::escape($plugin['label']);
    }

    return $plugin_options;
  }

}
