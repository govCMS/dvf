<?php

namespace Drupal\dvf\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\d7\FieldableEntity;

/**
 * Drupal 7 managed CKAN source from database.
 *
 * @MigrateSource(
 *   id = "d7_ckan_entity_migration",
 *   source_module = "file_entity"
 * )
 *
 * @code
 * source:
 *   plugin: d7_ckan_entity_migration
 *   constants:
 *     source_type: ckan
 * @endcode
 */
class CkanEntityMigration extends FieldableEntity {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('file_managed', 'fm')
      ->fields('fm', [
        'fid',
        'filename',
        'type',
        'uid',
        'uri',
      ])
      ->fields('fd', [
        'ckan_visualisation_config',
      ]);

    $query->leftJoin('field_data_ckan_visualisation', 'fd', 'fm.fid = fd.entity_id');

    if (isset($this->configuration['constants']['source_type'])) {
      $query->condition('fm.type', $this->configuration['constants']['source_type']);
    }

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'fid' => $this->t('File Id.'),
      'filename' => $this->t('File label.'),
      'type' => $this->t('File type.'),
      'uid' => $this->t('The user that uploaded the file.'),
      'uri' => $this->t('The uri of the CKAN entity.'),
      'ckan_visualisation_config' => $this->t('The CKAN visualisation config.'),
    ];

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['fid']['type'] = 'integer';

    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {

    $fid = $row->getSourceProperty('fid');
    $type = $row->getSourceProperty('type');

    $entity_fields = $this->getFields('file', $type);

    foreach ($entity_fields as $field_name => $field) {
      $value = $this->getFieldValues('file', $field_name, $fid);
      $row->setSourceProperty($field_name, $value);
    }

    return parent::prepareRow($row);
  }

}
