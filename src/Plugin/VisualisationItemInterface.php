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
   * @param array $default_options
   *   Provide default options for the visualisation.
   *
   * @return \Drupal\dvf\Plugin\VisualisationInterface
   *   An instance of the visualisation plugin.
   */
  public function getVisualisationPlugin(array $default_options = []);

}
