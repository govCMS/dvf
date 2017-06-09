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
