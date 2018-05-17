<?php

namespace Drupal\dvf;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides common methods for field widgets.
 */
trait FieldWidgetTrait {

  /**
   * Gets the options for a single field widget.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   Array of default values for this field.
   * @param int $delta
   *   The order of this item in the array of sub-elements (0, 1, 2, etc.).
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param string $key
   *   The key.
   * @param string $default
   *   The default value.
   *
   * @return mixed
   *   The option, $option_default otherwise.
   */
  protected function getElementOptions(FieldItemListInterface $items, $delta, array $form, FormStateInterface $form_state, $key, $default = NULL) {
    $values = $this->getFieldValue($items, $delta, $form, $form_state);

    if (isset($values[$delta]['options'][$key])) {
      $option = $values[$delta]['options'][$key];
    }
    else {
      $option = $default;
    }

    return $option;
  }

  /**
   * Updates the visualisation options.
   *
   * @param array $form
   *   The form structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The updated form element.
   */
  public function updateVisualisationOptions(array $form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $elements = NestedArray::getValue($form, array_slice($triggering_element['#array_parents'], 0, -1));

    return $elements['visualisation_options'];
  }

  /**
   * Returns a list of visualisation style plugin options.
   *
   * @return array
   *   An array of visualisation style plugin options.
   */
  protected function getVisualisationStyleOptions() {
    $options = [];

    /** @var \Drupal\dvf\Plugin\VisualisationStyleManagerInterface $plugin_manager */
    $plugin_manager = \Drupal::service('plugin.manager.visualisation.style');

    foreach ($plugin_manager->getDefinitions() as $plugin_id => $plugin) {
      $options[$plugin_id] = Html::escape($plugin['label']);
    }

    return $options;
  }

  /**
   * Get the values for a field item delta.
   *
   * If values exists in form_state we assume this is more current than the
   * item so it gets preferenced.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   Array of default values for this field.
   * @param int $delta
   *   The order of this item in the array of sub-elements (0, 1, 2, etc.).
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   Array of values.
   */
  protected function getFieldValue(FieldItemListInterface $items, $delta, array $form, FormStateInterface $form_state) {
    $form_values = $form_state->getValue($items->getName());
    $item_values = $items->getValue();

    return !empty($form_values) ? $form_values : $item_values;
  }

}
