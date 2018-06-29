<?php

namespace Drupal\dvf\Plugin;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides a VisualisationSource plugin manager.
 */
class VisualisationSourceManager extends VisualisationPluginManager implements VisualisationSourceManagerInterface {

  /**
   * Constructs a new VisualisationSourceManager.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Source', $namespaces, $cache_backend, $module_handler, 'Drupal\dvf\Annotation\VisualisationSource');
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitionsByType($type) {
    $plugins = [];

    foreach ($this->getDefinitions() as $plugin_id => $plugin) {
      if (in_array($type, $plugin['visualisation_types'])) {
        $plugins[$plugin_id] = $plugin;
      }
    }

    return $plugins;
  }

}
