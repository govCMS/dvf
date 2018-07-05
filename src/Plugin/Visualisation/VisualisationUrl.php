<?php

namespace Drupal\dvf\Plugin\Visualisation;

/**
 * Plugin implementation of the 'dvf_url' visualisation.
 *
 * @Visualisation(
 *   id = "dvf_url"
 * )
 */
class VisualisationUrl extends VisualisationBase {

  /**
   * {@inheritdoc}
   */
  public function getSourceConfiguration() {
    $source = parent::getSourceConfiguration();
    $source['options']['uri'] = $this->config('options', 'uri');

    return $source;
  }

}
