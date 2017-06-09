<?php

namespace Drupal\dvf\Plugin;

/**
 * Provides an interface defining a VisualisationSourceManager.
 */
interface VisualisationSourceManagerInterface extends VisualisationPluginManagerInterface {

  /**
   * Returns a list of plugin definitions by type.
   *
   * @param string $type
   *   The type.
   *
   * @return array
   *   An array of plugin definitions.
   */
  public function getDefinitionsByType($type);

}
