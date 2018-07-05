<?php

namespace Drupal\dvf\Plugin;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
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

}
