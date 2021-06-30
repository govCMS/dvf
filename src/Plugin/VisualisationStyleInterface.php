<?php

namespace Drupal\dvf\Plugin;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Component\Plugin\DependentPluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides an interface defining a VisualisationStyle plugin.
 */
interface VisualisationStyleInterface extends ConfigurableInterface, DependentPluginInterface, PluginInspectionInterface {

  /**
   * Returns a form to configure settings for this plugin.
   *
   * @param array $form
   *   The form where the settings form is being included in.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   An array of form elements.
   */
  public function settingsForm(array $form, FormStateInterface $form_state);

  /**
   * Builds and returns the renderable array for this plugin.
   *
   * @return array
   *   A renderable array representing this plugin.
   */
  public function build();

  /**
   * Returns the current visualisation.
   *
   * @return \Drupal\dvf\Plugin\VisualisationInterface
   *   Current visualisation instance.
   */
  public function getVisualisation();

  /**
   * Returns the download URL for a dataset.
   *
   * @return string|null
   *   The URI of the file or dataset. NULL if not available.
   */
  public function getDatasetDownloadUri();

  /**
   * Confirms if a URI or file download link is valid.
   *
   * @param string $uri
   *   The URI to test if valid.
   *
   * @return string|bool
   *   Returns the URI is valid, or false if not.
   */
  public function isValidDownloadUri($uri);

}
