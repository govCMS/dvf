<?php

namespace Drupal\dvf\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Provides an interface defining a VisualisationSource plugin.
 */
interface VisualisationItemInterface extends PluginInspectionInterface {

  /**
   * Gets the visualisation plugin.
   *
   * @param array $default_source
   *   The default visualisation source configuration.
   * @param array $default_style
   *   The default visualisation style configuration.
   *
   * @return \Drupal\dvf\Plugin\VisualisationInterface
   *   An instance of the visualisation plugin.
   */
  public function getVisualisationPlugin(array $default_source = [], array $default_style = []);

}
