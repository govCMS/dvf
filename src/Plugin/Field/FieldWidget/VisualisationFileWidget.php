<?php

namespace Drupal\dvf\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\dvf\FieldWidgetTrait;
use Drupal\dvf\Plugin\VisualisationManager;
use Drupal\file\Element\ManagedFile;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * The visualisation manager.
   *
   * @var \Drupal\dvf\Plugin\VisualisationManager
   */
  protected $visualisationManager;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, VisualisationManager $visualisationManager, RendererInterface $renderer) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->visualisationManager = $visualisationManager;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($plugin_id, $plugin_definition, $configuration['field_definition'], $configuration['settings'], $configuration['third_party_settings'], $container->get('plugin.manager.visualisation'), $container->get('renderer'));
  }

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

    $element['file']['#description'] = $this->renderer->renderPlain($file_upload_help);

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

    $ajaxWrapperId = $this->getAjaxWrapperId($form, $this->fieldDefinition->getName(), $delta);

    $element['options']['visualisation_style'] = [
      '#type' => 'select',
      '#title' => $this->t('Visualisation style'),
      '#description' => $this->t('How the data will be presented when rendered on the page.'),
      '#options' => $this->getVisualisationStyleOptions(),
      '#empty_option' => $this->t('- Select -'),
      '#empty_value' => '',
      '#default_value' => $items[$delta]->options['visualisation_style'] ?? '',
      '#required' => $element['#required'],
      '#ajax' => [
        'callback' => [$this, 'updateVisualisationOptions'],
        'wrapper' => $ajaxWrapperId,
      ],
    ];

    $element['options']['visualisation_style_options'] = [
      '#type' => 'container',
      '#attributes' => ['id' => $ajaxWrapperId],
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

    return $this->visualisationManager->createInstance($plugin_id, $plugin_configuration);
  }

}
