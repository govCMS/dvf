<?php

namespace Drupal\dvf\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\MapDataDefinition;
use Drupal\file\Plugin\Field\FieldType\FileItem;
use Drupal\dvf\FieldTypeTrait;
use Drupal\file\Entity\File;
use Drupal\dvf\Plugin\VisualisationItemInterface;

/**
 * Plugin implementation of the 'dvf_file' field type.
 *
 * @FieldType(
 *   id = "dvf_file",
 *   label = @Translation("Visualisation File"),
 *   description = @Translation("Saves a file locally, and options to display a visualisation."),
 *   category = @Translation("Data Visualisation Framework"),
 *   default_widget = "dvf_file_default",
 *   default_formatter = "dvf_file_default",
 *   list_class = "\Drupal\file\Plugin\Field\FieldType\FileFieldItemList"
 * )
 */
class VisualisationFileItem extends FileItem implements VisualisationItemInterface {

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
    $properties = parent::propertyDefinitions($field_definition);

    $properties['options'] = MapDataDefinition::create()
      ->setLabel(t('Options'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = parent::schema($field_definition);

    $schema['columns']['options'] = [
      'description' => 'Serialized array of style options.',
      'type' => 'blob',
      'size' => 'big',
      'serialize' => TRUE,
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $element = [
      'source_type' => [
        '#type' => 'select',
        '#title' => $this->t('Source type'),
        '#description' => $this->t('Specify the source type allowed in this field.'),
        '#options' => $this->getVisualisationSourceOptions(),
        '#default_value' => $this->getSetting('source_type'),
        '#required' => TRUE,
      ],
    ] + parent::fieldSettingsForm($form, $form_state);

    return $element;
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
   * {@inheritdoc}
   */
  public function getValue() {
    $values = parent::getValue();

    $values['uri'] = '';
    $values['file']['fids'] = [];

    if (!empty($values['target_id'])) {
      $values['uri'] = $this->getFileUrl($values['target_id']);
      $values['file']['fids'] = [$values['target_id']];
    }

    return $values;
  }

  /**
   * Return the full url to the file.
   *
   * @param int $fid
   *   The entity id for the file.
   *
   * @return string
   *   URL srting or NULL if no fid.
   */
  public function getFileUrl($fid = NULL) {
    if (empty($fid)) {
      return NULL;
    }

    $file = File::load($fid);
    return !empty($file) ? file_create_url($file->getFileUri()) : NULL;
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
