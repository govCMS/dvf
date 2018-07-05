<?php

namespace Drupal\dvf\Plugin\Visualisation\Style;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\dvf\ConfigurablePluginTrait;
use Drupal\dvf\Plugin\VisualisationInterface;
use Drupal\dvf\Plugin\VisualisationStyleInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a base class for VisualisationStyle plugins.
 */
abstract class VisualisationStyleBase extends PluginBase implements VisualisationStyleInterface, ContainerFactoryPluginInterface {

  use ConfigurablePluginTrait;

  /**
   * The visualisation.
   *
   * @var \Drupal\dvf\Plugin\VisualisationInterface
   */
  protected $visualisation;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a new VisualisationStyleBase.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\dvf\Plugin\VisualisationInterface $visualisation
   *   The visualisation context in which the plugin will run.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, VisualisationInterface $visualisation = NULL, ModuleHandlerInterface $module_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->visualisation = $visualisation;
    $this->moduleHandler = $module_handler;
  }

  /**
   * Creates an instance of the plugin.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container to pull out services used in the plugin.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\dvf\Plugin\VisualisationInterface $visualisation
   *   The visualisation context in which the plugin will run.
   *
   * @return static
   *   Returns an instance of this plugin.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, VisualisationInterface $visualisation = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $visualisation,
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return NestedArray::mergeDeep($this->defaultConfiguration(), $this->configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'data' => [
        'fields' => [],
        'field_labels' => '',
        'split_field' => '',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form['#after_build'][] = [get_called_class(), 'afterBuildSettingsForm'];

    $form['data'] = [
      '#type' => 'details',
      '#title' => $this->t('Data settings'),
      '#tree' => TRUE,
    ];

    $form['data']['fields'] = [
      '#type' => 'select',
      '#title' => $this->t('Fields'),
      '#description' => $this->t("What fields to include in the visualisation. Select at least one field to display it's data. A field is typically a column in a CSV."),
      '#options' => $this->getSourceFieldOptions(),
      '#multiple' => TRUE,
      '#size' => 5,
      '#default_value' => $this->config('data', 'fields'),
    ];

    $form['data']['field_labels'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Field label overrides'),
      '#description' => $this->t('Optionally override one or more field labels. Add one original_label|new_label per line and separate with a pipe.'),
      '#rows' => 2,
      '#default_value' => $this->config('data', 'field_labels'),
      '#placeholder' => 'Old label|New label',
    ];

    $form['data']['split_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Split field'),
      '#description' => $this->t('Optionally split into multiple visualisations based on the value of this field. A new visualisation will be made for each unique value in this field.'),
      '#options' => $this->getSourceFieldOptions(),
      '#empty_option' => $this->t('- None -'),
      '#empty_value' => '',
      '#default_value' => $this->config('data', 'split_field'),
    ];

    return $form;
  }

  /**
   * Settings form #after_build callback.
   *
   * @param array $element
   *   The form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The updated form element.
   */
  public static function afterBuildSettingsForm(array $element, FormStateInterface $form_state) {
    return $element;
  }

  /**
   * Gets the list of source field options.
   *
   * @return array
   *   The source field options.
   */
  protected function getSourceFieldOptions() {
    $fields = $this->visualisation->getSourcePlugin()->getFields();
    $options = array_map('\Drupal\Component\Utility\Html::escape', $fields);

    return !empty($options) ? $options : [];
  }

  /**
   * Gets the source field values.
   *
   * @param string $field_id
   *   The field ID.
   *
   * @return array
   *   The source field values.
   */
  protected function getSourceFieldValues($field_id) {
    $values = [];

    foreach ($this->getSourceRecords() as $group_records) {
      foreach ($group_records as $record) {
        if (property_exists($record, $field_id)) {
          $values[] = $record->{$field_id};
        }
      }
    }

    return $values;
  }

  /**
   * Gets the source records.
   *
   * @return array
   *   An array of source records.
   */
  public function getSourceRecords() {
    $records = [];

    foreach ($this->getVisualisation()->data() as $record) {
      if ($this->splitField() && property_exists($record, $this->splitField())) {
        $records[$record->{$this->splitField()}][] = $record;
      }
      else {
        $records['all'][] = $record;
      }
    }

    return $records;
  }

  /**
   * Gets the fields.
   *
   * @return array
   *   The fields.
   */
  protected function fields() {
    return array_filter($this->config('data', 'fields'));
  }

  /**
   * Gets the field labels.
   *
   * @return array
   *   The field labels.
   */
  protected function fieldLabels() {
    $labels = array_intersect_key($this->getSourceFieldOptions(), $this->fields());

    $label_overrides = $this->config('data', 'field_labels');
    $label_overrides = preg_split('/\r\n|[\r\n]/', $label_overrides);

    foreach ($label_overrides as $label_override) {
      $label_parts = explode('|', $label_override, 2);

      if (count($label_parts) === 2 && array_key_exists($label_parts[0], $labels)) {
        $labels[$label_parts[0]] = $label_parts[1];
      }
    }

    return $labels;
  }

  /**
   * Gets a field label.
   *
   * @param string $field_id
   *   The field ID.
   *
   * @return string
   *   The field label.
   */
  protected function fieldLabel($field_id) {
    $label = '';
    $labels = $this->fieldLabels();

    if (array_key_exists($field_id, $labels)) {
      $label = $labels[$field_id];
    }

    return $label;
  }

  /**
   * Gets the split field.
   *
   * @return string
   *   The split field.
   */
  protected function splitField() {
    return $this->config('data', 'split_field');
  }

  /**
   * {@inheritdoc}
   */
  public function getVisualisation() {
    return $this->visualisation;
  }

}
