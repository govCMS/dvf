<?php

namespace Drupal\dvf\Plugin\Visualisation;

/**
 * Plugin implementation of the 'dvf_file' visualisation.
 *
 * @Visualisation(
 *   id = "dvf_file"
 * )
 */
class VisualisationFile extends VisualisationBase {

  /**
   * {@inheritdoc}
   */
  public function getSourceConfiguration() {
    $source = parent::getSourceConfiguration();
    $source['options']['uri'] = $this->config('options', 'uri');

    return $source;
  }

}
