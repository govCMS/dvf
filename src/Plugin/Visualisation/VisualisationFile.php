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
    $configuration = parent::getSourceConfiguration();
    $configuration['uri'] = $this->configuration['uri'];

    return $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function getStyleConfiguration() {
    $configuration = parent::getStyleConfiguration();
    $configuration += $this->configuration['options'];

    return $configuration;
  }

}
