<?php

namespace Drupal\dvf\Plugin;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides an interface defining a VisualisationStyle plugin.
 */
interface VisualisationStyleInterface extends ConfigurablePluginInterface, PluginInspectionInterface {

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
   * Returns the URI of a DVF file or dataset (JSON|CSV file, or CKAN dataset).
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The Drupal entity that contains the dvf_url or dvf_file.
   * @param array $dvf_field_types
   *   An array of field_names that provide dataset(s) for DVF visualisations.
   *
   *   E.g. ['dvf_url', 'dvf_file'].
   *
   * @return string
   *   The URI of the file or dataset.
   */
  public function getDatasetDownloadUri(EntityInterface $entity, array $dvf_field_types);

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
