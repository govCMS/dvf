<?php

namespace Drupal\Tests\dvf\Functional\Traits;

use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;

/**
 * Trait DvfFieldCreationTrait.
 *
 * Provides methods for creating dvf fields.
 *
 * @package Drupal\Tests\dvf\Functional\Traits
 */
trait DvfFieldCreationTrait {

  /***
   * Default field used by dvf fields.
   *
   * @var string
   */
  public $defaultFieldFormatter = 'dvf_default';

  /**
   * Default subdirectory where test files uploaded via field will be stored.
   *
   * @var string
   */
  public $defaultFileSubdir = 'dvf_tests_files';

  /**
   * List of default extensions to be supported by file field.
   *
   * @var string
   */
  public $defaultFileExtensions = 'csv txt json';

  /**
   * Default visualisation source to be used when creating a new field.
   *
   * @var string
   */
  public $defaultVisualisationSource = 'dvf_csv_file';

  /**
   * Creates a new dvf file field and attach to entity.
   *
   * @param string $field_name
   *   Name of new field to created.
   * @param array $field_settings
   *   A list of instance settings that will be added to the instance defaults.
   * @param array $widget_settings
   *   A list of widget settings that will be added to the widget defaults.
   * @param string $entity_type
   *   The entity type.
   * @param string $bundle
   *   The bundle that this field will be added to.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   Created field.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createDvfFileField($field_name, array $field_settings = [], array $widget_settings = [], $entity_type = 'node', $bundle = 'page') {
    $field_storage_settings = [
      'display_field' => '1',
      'display_default' => '1',
    ];

    $default_field_settings = [
      'description_field' => 'A dvf file field for testing purposes',
      'visualisation_source' => $this->defaultVisualisationSource,
      'file_directory' => $this->defaultFileSubdir,
      'file_extensions' => $this->defaultFileExtensions,
    ];
    $field_settings = array_merge($default_field_settings, $field_settings);

    return $this->createDvfField('dvf_file', $field_name, $entity_type, $bundle, $field_storage_settings, $field_settings, $widget_settings);
  }

  /**
   * Creates a new DVF field (file or URL)
   *
   * @param string $field_type
   *   Type of dvf field to be created (dvf_file or dvf_url).
   * @param string $field_name
   *   Name of new field to created.
   * @param string $entity_type
   *   The entity type.
   * @param string $bundle
   *   The bundle that this field will be added to.
   * @param array $storage_settings
   *   A list of field storage settings that will be added to the defaults.
   * @param array $field_settings
   *   A list of instance settings that will be added to the instance defaults.
   * @param array $widget_settings
   *   A list of widget settings that will be added to the widget defaults.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The entity interface.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createDvfField($field_type, $field_name, $entity_type, $bundle, array $storage_settings = [], array $field_settings = [], array $widget_settings = []) {
    $field_storage = FieldStorageConfig::create([
      'entity_type' => $entity_type,
      'field_name' => $field_name,
      'type' => $field_type,
      'settings' => $storage_settings,
      'cardinality' => !empty($storage_settings['cardinality']) ? $storage_settings['cardinality'] : 1,
    ]);
    $field_storage->save();

    $this->attachDvfFieldToBundle($field_type, $field_name, $entity_type, $bundle, $field_settings, $widget_settings);

    return $field_storage;
  }

  /**
   * Attaches a dvf field to an entity.
   *
   * @param string $field_type
   *   Type of dvf field to be created (dvf_file or dvf_url).
   * @param string $field_name
   *   The name of the new field (all lowercase), exclude the "field_" prefix.
   * @param string $entity_type
   *   The entity type this field will be added to.
   * @param string $bundle
   *   The bundle this field will be added to.
   * @param array $field_settings
   *   A list of field settings that will be added to the defaults.
   * @param array $widget_settings
   *   A list of widget settings that will be added to the widget defaults.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function attachDvfFieldToBundle($field_type, $field_name, $entity_type, $bundle, array $field_settings = [], array $widget_settings = []) {
    $field = [
      'field_name' => $field_name,
      'label' => $field_name,
      'entity_type' => $entity_type,
      'bundle' => $bundle,
      'required' => !empty($field_settings['required']),
      'settings' => $field_settings,
    ];
    FieldConfig::create($field)->save();

    // Enable form field display on entity.
    $widget_name = $field_type . '_default';
    \Drupal::service('entity_display.repository')->getFormDisplay($entity_type, $bundle)
      ->setComponent($field_name, [
        'type' => $widget_name,
        'settings' => $widget_settings,
      ])
      ->save();
    // Enable field display on entity.
    \Drupal::service('entity_display.repository')->getViewDisplay($entity_type, $bundle)
      ->setComponent($field_name, [
        'label' => 'hidden',
        'type' => $this->defaultFieldFormatter,
      ])
      ->save();
  }

}
