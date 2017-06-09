<?php

namespace Drupal\dvf\Plugin;

use Drupal\Component\Plugin\Factory\DefaultFactory;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Provides a Visualisation plugin manager.
 */
class VisualisationPluginManager extends DefaultPluginManager implements VisualisationPluginManagerInterface {

  /**
   * Constructs a new VisualisationPluginManager.
   *
   * @param string $type
   *   The type of the plugin: Style.
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   * @param string $annotation
   *   (optional) The annotation class name. Defaults to
   *   'Drupal\Component\Annotation\PluginID'.
   */
  public function __construct($type, \Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler, $annotation = 'Drupal\Component\Annotation\PluginID') {
    parent::__construct('Plugin/Visualisation/' . $type, $namespaces, $module_handler, NULL, $annotation);
    $this->setCacheBackend($cache_backend, 'dvf_plugins_visualisation_' . strtolower($type));
  }

  /**
   * {@inheritdoc}
   */
  public function createInstance($plugin_id, array $configuration = [], VisualisationInterface $visualisation = NULL) {
    $plugin_definition = $this->getDefinition($plugin_id);
    $plugin_class = DefaultFactory::getPluginClass($plugin_id, $plugin_definition);

    if (is_subclass_of($plugin_class, 'Drupal\Core\Plugin\ContainerFactoryPluginInterface')) {
      $plugin = $plugin_class::create(\Drupal::getContainer(), $configuration, $plugin_id, $plugin_definition, $visualisation);
    }
    else {
      $plugin = new $plugin_class($configuration, $plugin_id, $plugin_definition, $visualisation);
    }

    return $plugin;
  }

}
