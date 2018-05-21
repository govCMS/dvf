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
   * @return \Drupal\dvf\Plugin\VisualisationInterface
   *   The visualisation plugin.
   */
  public function getVisualisationPlugin();

}
