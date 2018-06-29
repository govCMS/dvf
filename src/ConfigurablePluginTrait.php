<?php

namespace Drupal\dvf;

/**
 * Provides common methods for configurable plugins.
 *
 * @see \Drupal\Component\Plugin\ConfigurablePluginInterface;
 */
trait ConfigurablePluginTrait {

  /**
   * Gets deeply nested configuration for this plugin.
   *
   * @param string ...
   *   The ordered set of keys (e.g. 'key1, key2, key3').
   *
   * @return mixed
   *   The value of the last element in the key path if found, NULL otherwise.
   */
  protected function config() {
    $key_path = func_get_args();
    $haystack = $this->getConfiguration();

    while ($key_path) {
      $key = array_shift($key_path);

      if (!array_key_exists($key, $haystack)) {
        return NULL;
      }

      if (empty($key_path)) {
        return $haystack[$key];
      }

      $haystack = $haystack[$key];
    }

    return NULL;
  }

}
