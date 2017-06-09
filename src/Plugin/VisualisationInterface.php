<?php

namespace Drupal\dvf\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Provides an interface defining a Visualisation plugin.
 */
interface VisualisationInterface extends PluginInspectionInterface {

  /**
   * Returns the source plugin.
   *
   * @return \Drupal\dvf\Plugin\VisualisationSourceInterface
   *   The source plugin.
   */
  public function getSourcePlugin();

  /**
   * Returns the style plugin.
   *
   * @return \Drupal\dvf\Plugin\VisualisationStyleInterface
   *   The style plugin.
   */
  public function getStylePlugin();

  /**
   * Gets the source configuration, with at least a 'plugin_id' key.
   *
   * @return array
   *   The source configuration.
   */
  public function getSourceConfiguration();

  /**
   * Gets the style configuration, with at least a 'plugin_id' key.
   *
   * @return array
   *   The style configuration.
   */
  public function getStyleConfiguration();

}
