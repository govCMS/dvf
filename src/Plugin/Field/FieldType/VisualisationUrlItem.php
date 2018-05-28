<?php

namespace Drupal\dvf\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\MapDataDefinition;
use Drupal\dvf\FieldTypeTrait;
use Drupal\dvf\Plugin\VisualisationItemInterface;

/**
 * Plugin implementation of the 'dvf_url' field type.
 *
 * @FieldType(
 *   id = "dvf_url",
 *   label = @Translation("Visualisation URL"),
 *   description = @Translation("Stores a URL string, and options to display a visualisation."),
 *   category = @Translation("Data Visualisation Framework"),
 *   default_widget = "dvf_url_default",
 *   default_formatter = "dvf_default"
 * )
 */
class VisualisationUrlItem extends FieldItemBase implements VisualisationItemInterface {

  use FieldTypeTrait;

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

}
