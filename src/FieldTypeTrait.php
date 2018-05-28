<?php

namespace Drupal\dvf;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;

/**
 * Provides common methods for field types.
 */
trait FieldTypeTrait {

  /**
   * Gets the visualisation plugin.
   *
   * @param array $default_options
   *   Provide default options for the visualisation.
   *
   * @return \Drupal\dvf\Plugin\VisualisationInterface
   *   An instance of the visualisation plugin.
   */
  public function getVisualisationPlugin(array $default_options = []) {
    $item = $this->getValue();
    $options = NestedArray::mergeDeep($default_options, $item['options']['visualisation_options']);

    /** @var \Drupal\dvf\Plugin\VisualisationManagerInterface $plugin_manager */
    $plugin_manager = \Drupal::service('plugin.manager.visualisation');

    $plugin_id = $this->getFieldDefinition()->getType();
    $plugin_configuration = [
      'uri' => $item['uri'],
      'options' => $options,
      'source' => ['plugin_id' => $this->getFieldDefinition()->getSetting('source_type')],
      'style' => ['plugin_id' => $item['options']['visualisation_style']],
      'entity' => $this->getEntity(),
    ];

    /** @var \Drupal\dvf\Plugin\VisualisationInterface $plugin */
    $plugin = $plugin_manager->createInstance($plugin_id, $plugin_configuration);

    return $plugin;
  }

  /**
   * Returns a list of visualisation source plugin options.
   *
   * @return array
   *   An array of visualisation source plugin options. Keyed by plugin_id and
   *   value being the plugin label.
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
