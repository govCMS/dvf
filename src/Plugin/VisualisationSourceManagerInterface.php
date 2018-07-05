<?php

namespace Drupal\dvf\Plugin;

/**
 * Provides an interface defining a VisualisationSourceManager.
 */
interface VisualisationSourceManagerInterface extends VisualisationPluginManagerInterface {

  /**
   * Returns a list of plugin definitions for a visualisation type.
   *
   * @param string $visualisation_type
   *   The visualisation type.
   *
   * @return array
   *   An array of plugin definitions.
   */
  public function getDefinitionsByType($visualisation_type);

}
