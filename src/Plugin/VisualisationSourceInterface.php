<?php

namespace Drupal\dvf\Plugin;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides an interface defining a VisualisationSource plugin.
 */
interface VisualisationSourceInterface extends \Countable, \Iterator, ConfigurablePluginInterface, PluginInspectionInterface {

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
   * Returns available fields on the source.
   *
   * @return array
   *   An array of available fields where the keys are the machine names
   *   and values are the labels.
   */
  public function getFields();

  /**
   * Returns available records on the source.
   *
   * @return array
   *   An array of records.
   */
  public function getRecords();

  /**
   * Returns the current visualisation.
   *
   * @return \Drupal\dvf\Plugin\VisualisationInterface
   *   Current visualisation instance.
   */
  public function getVisualisation();

}
