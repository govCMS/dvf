<?php

namespace Drupal\dvf\Plugin;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Provides an interface defining a Visualisation plugin.
 */
interface VisualisationInterface extends ConfigurablePluginInterface, PluginInspectionInterface {

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

  /**
   * Gets the entity the DVF field is attached to.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   Entity object or null if not available.
   */
  public function getEntity();

  /**
   * Returns the data records used by this visualisation.
   *
   * @return array
   *   An array of data records.
   */
  public function data();

  /**
   * Renders this visualisation.
   *
   * @return array
   *   A render array as expected by
   *   \Drupal\Core\Render\RendererInterface::render().
   */
  public function render();

}
