<?php

namespace Drupal\dvf\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\dvf\FieldWidgetTrait;
use Drupal\file\Element\ManagedFile;

/**
 * Plugin implementation of the 'dvf_file_default' field widget.
 *
 * @FieldWidget(
 *   id = "dvf_file_default",
 *   label = @Translation("Visualisation File"),
 *   field_types = {
 *     "dvf_file"
 *   }
 * )
 */
class VisualisationFileWidget extends WidgetBase {

  use FieldWidgetTrait;

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $cardinality = $this->fieldDefinition->getFieldStorageDefinition()->getCardinality();
    $values = $this->getFieldValue($items, $delta, $form, $form_state);

    $element['#type'] = 'fieldset';
    $element['#tree'] = TRUE;

    // File upload.
    $element['file'] = [
      '#type' => 'managed_file',
      '#upload_location' => $items[$delta]->getUploadLocation(),
      '#upload_validators' => $items[$delta]->getUploadValidators(),
      '#value_callback' => [get_class($this), 'value'],
      '#progress_indicator' => 'throbber',
      // Allows this field to return an array instead of a single value.
      '#extended' => TRUE,
    ];

    $file_upload_help = [
      '#theme' => 'file_upload_help',
      '#upload_validators' => $element['file']['#upload_validators'],
      '#cardinality' => $cardinality,
    ];

    $element['file']['#description'] = \Drupal::service('renderer')->renderPlain($file_upload_help);

    // Default value for managed file expects an array of fids.
    if (!empty($values[$delta]['file'])) {
      $element['file']['#default_value'] = $values[$delta]['file'];
    }

    // Visualisation options.
    $element['options'] = [
      '#type' => 'details',
      '#title' => $this->t('Settings'),
      '#description' => $this->t('Visualisation settings for this field.'),
      '#tree' => TRUE,
    ];

    $element['options']['visualisation_style'] = [
      '#type' => 'select',
      '#title' => $this->t('Visualisation style'),
      '#description' => $this->t('How the data will be presented when rendered on the page.'),
      '#options' => $this->getVisualisationStyleOptions(),
      '#empty_option' => $this->t('- Select -'),
      '#empty_value' => '',
      '#default_value' => isset($items[$delta]->options['visualisation_style']) ? $items[$delta]->options['visualisation_style'] : '',
      '#required' => $element['#required'],
      '#ajax' => [
        'callback' => [$this, 'updateVisualisationOptions'],
        'wrapper' => $this->getAjaxWrapperId($form, $this->fieldDefinition->getName(), $delta),
      ],
    ];

    $element['options']['visualisation_style_options'] = [
      '#type' => 'container',
      '#attributes' => ['id' => $element['options']['visualisation_style']['#ajax']['wrapper']],
    ];

    $style_id = $this->getElementOptions($items, $delta, $form, $form_state, 'visualisation_style');

    if ($style_id) {
      $element['options']['visualisation_style_options'] += $this
        ->getVisualisationPlugin($items, $delta, $form, $form_state)
        ->getStylePlugin()
        ->settingsForm($element['options']['visualisation_style_options'], $form_state);
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    // See FileWidget::massageFormValues() for the main reason for this
    // method. It also restructures the values to suit the database schema.
    $new_values = [];
    $file_defaults = ['display' => 1, 'description' => ''];

    foreach ($values as &$value) {
      if (is_array($value) && isset($value['file'])) {
        foreach ($value['file']['fids'] as $fid) {
          $new_value = array_merge($file_defaults, $value);
          $new_value['target_id'] = $fid;
          unset($new_value['file']);
          $new_values[] = $new_value;
        }
      }
    }

    return !empty($new_values) ? $new_values : $values;
  }

  /**
   * Let ManagedFile deal with the value on the file element.
   *
   * This method is assigned as a #value_callback in formElement() method.
   *
   * @param array $element
   *   Form element.
   * @param mixed $input
   *   Form input.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   *
   * @return array
   *   An array containing the key `fids` which is an array of file entity ids.
   */
  public static function value(array $element, $input, FormStateInterface $form_state) {
    $return = ManagedFile::valueCallback($element, $input, $form_state);

    // Ensure that all the required properties are returned even if empty.
    $return += [
      'fids' => [],
    ];

    return $return;
  }

  /**
   * Gets the visualisation plugin.
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
   * @return \Drupal\dvf\Plugin\VisualisationInterface
   *   The visualisation plugin.
   */
  protected function getVisualisationPlugin(FieldItemListInterface $items, $delta, array $form, FormStateInterface $form_state) {
    $values = $this->getFieldValue($items, $delta, $form, $form_state);

    /** @var \Drupal\dvf\Plugin\VisualisationManagerInterface $plugin_manager */
    $plugin_manager = \Drupal::service('plugin.manager.visualisation');

    $plugin_id = $this->fieldDefinition->getType();
    $plugin_configuration = [
      'options' => [
        'uri' => '',
      ],
      'source' => [
        'plugin_id' => $this->fieldDefinition->getSetting('visualisation_source'),
        'options' => $this->fieldDefinition->getSetting('visualisation_source_options'),
      ],
      'style' => [
        'plugin_id' => $this->getElementOptions($items, $delta, $form, $form_state, 'visualisation_style'),
        'options' => [],
      ],
      'entity' => $items->getEntity(),
    ];

    if (!isset($values[$delta]['uri'])) {
      $values = $this->massageFormValues($values, $form, $form_state);
      $values[$delta]['uri'] = $items[$delta]->getFileUrl($values[$delta]['target_id']);
    }

    if (!empty($values[$delta]['uri'])) {
      $plugin_configuration['options']['uri'] = $values[$delta]['uri'];
    }

    if (!empty($values[$delta]['options']['visualisation_style_options'])) {
      $plugin_configuration['style']['options'] = $values[$delta]['options']['visualisation_style_options'];
    }

    /** @var \Drupal\dvf\Plugin\VisualisationInterface $plugin */
    $plugin = $plugin_manager->createInstance($plugin_id, $plugin_configuration);

    return $plugin;
  }

}
