<?php

namespace Drupal\dvf\Plugin\Field\FieldType;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\MapDataDefinition;

/**
 * Plugin implementation of the 'dvf_url' field type.
 *
 * @FieldType(
 *   id = "dvf_url",
 *   label = @Translation("Visualisation URL"),
 *   description = @Translation("Stores a URL string, and options to display a visualisation."),
 *   category = @Translation("Data Visualisation Framework"),
 *   default_widget = "dvf_url_default",
 *   default_formatter = "dvf_url_default"
 * )
 */
class VisualisationUrlItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
      'source_type' => '',
    ] + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['uri'] = DataDefinition::create('uri')
      ->setLabel(t('URI'));

    $properties['options'] = MapDataDefinition::create()
      ->setLabel(t('Options'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'uri' => [
          'description' => 'The URI of the source.',
          'type' => 'varchar',
          'length' => 2048,
        ],
        'options' => [
          'description' => 'Serialized array of style options.',
          'type' => 'blob',
          'size' => 'big',
          'serialize' => TRUE,
        ],
      ],
      'indexes' => [
        'uri' => [['uri', 30]],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $element = [];

    $element['source_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Source type'),
      '#description' => $this->t('Specify the source type allowed in this field.'),
      '#options' => $this->getVisualisationSourceOptions(),
      '#default_value' => $this->getSetting('source_type'),
      '#required' => TRUE,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('uri')->getValue();
    return ($value === NULL || $value === '');
  }

  /**
   * {@inheritdoc}
   */
  public static function mainPropertyName() {
    return 'uri';
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($values, $notify = TRUE) {
    if (isset($values) && !is_array($values)) {
      $values = [static::mainPropertyName() => $values];
    }

    if (isset($values)) {
      $values += [
        'options' => [],
      ];
    }

    if (is_string($values['options'])) {
      $values['options'] = unserialize($values['options']);
    }

    parent::setValue($values, $notify);
  }

  /**
   * Returns a list of visualisation source plugin options.
   *
   * @return array
   *   An array of visualisation source plugin options.
   */
  protected function getVisualisationSourceOptions() {
    $plugin_options = [];
    /** @var \Drupal\dvf\Plugin\VisualisationSourceManagerInterface $plugin_manager */
    $plugin_manager = \Drupal::service('plugin.manager.visualisation.source');

    foreach ($plugin_manager->getDefinitionsByType('url') as $plugin_id => $plugin) {
      $plugin_options[(string) $plugin['category']][$plugin_id] = Html::escape($plugin['label']);
    }

    return $plugin_options;
  }

  /**
   * Gets the visualisation plugin.
   *
   * @return \Drupal\dvf\Plugin\VisualisationInterface
   *   The visualisation plugin.
   */
  public function getVisualisationPlugin() {
    $item = $this->getValue();

    /** @var \Drupal\dvf\Plugin\VisualisationManagerInterface $plugin_manager */
    $plugin_manager = \Drupal::service('plugin.manager.visualisation');

    $plugin_id = $this->getFieldDefinition()->getType();
    $plugin_configuration = [
      'uri' => $item['uri'],
      'options' => $item['options']['visualisation_options'],
      'source' => ['plugin_id' => $this->getFieldDefinition()->getSetting('source_type')],
      'style' => ['plugin_id' => $item['options']['visualisation_style']],
    ];

    /** @var \Drupal\dvf\Plugin\VisualisationInterface $plugin */
    $plugin = $plugin_manager->createInstance($plugin_id, $plugin_configuration);

    return $plugin;
  }

}
