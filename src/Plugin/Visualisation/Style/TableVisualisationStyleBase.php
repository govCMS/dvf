<?php

namespace Drupal\dvf\Plugin\Visualisation\Style;

/**
 * Provides a base class for table-based VisualisationStyle plugins.
 */
abstract class TableVisualisationStyleBase extends VisualisationStyleBase {

  /**
   * Builds and returns a table renderable array for this plugin.
   *
   * @param array $records
   *   The records.
   *
   * @return array
   *   A table renderable array.
   */
  protected function buildTable(array $records) {
    $table_id = hash('sha256', time() . mt_rand());
    $configuration = $this->getConfiguration();

    $table = [
      '#type' => 'container',
      '#attributes' => ['class' => 'single-table'],
    ];

    $table['table'] = [
      '#type' => 'html_tag',
      '#tag' => 'table',
      '#attributes' => ['data-dvftables' => $table_id],
    ];

    $table['#attached']['library'] = ['dvf/dvfTables'];
    $table['#attached']['drupalSettings']['dvf']['tables'][$table_id] = $this->tableBuildSettings($records);

    // If our table is not the primary visualisation, exit here.
    if (!array_key_exists('table', $configuration)) {
      return $table;
    }

    // If $file_uri is empty/false, do not display download data button to the table.
    $file_uri = $this->getDatasetDownloadUri($this->visualisation->getEntity());
    if (!empty($file_uri)) {
      $table['actions']['file_uri'] = [
        '#type' => 'html_tag',
        '#tag' => 'button',
        '#value' => $this->t('Download data'),
        '#attributes' => [
          'class' => ['download-data'],
          'data-file-uri' => $file_uri,
        ],
      ];
    }

    return $table;
  }

  /**
   * Returns the table build settings for this plugin.
   *
   * @param array $records
   *   The records.
   *
   * @return array
   *   An array of table build settings.
   */
  protected function tableBuildSettings(array $records) {
    $config = $this->getConfiguration();

    return [
      'data' => $this->getTableRows($records),
      'columns' => $this->getTableHeader(),
      'table' => !empty($config['chart']['table']) ? $config['chart']['table'] : '',
    ];
  }

  /**
   * Gets the table header field.
   *
   * @return string
   *   The table header field.
   */
  abstract protected function tableHeaderField();

  /**
   * Gets the row header field.
   *
   * @return string
   *   The row header field.
   */
  abstract protected function rowHeaderField();

  /**
   * Gets the table header.
   *
   * @return array
   *   The table header.
   */
  protected function getTableHeader() {
    $header = [];

    if ($this->tableHeaderField() || $this->rowHeaderField()) {
      $header[] = $this->createRowCell('');
    }

    $labels = $this->fieldLabels();

    if ($this->tableHeaderField()) {
      $labels = $this->getSourceFieldValues($this->tableHeaderField());
    }

    foreach ($labels as $label) {
      $header[] = $this->createHeaderCell($label, 'col');
    }

    return $header;
  }

  /**
   * Gets the table rows.
   *
   * @param array $records
   *   The records.
   *
   * @return array
   *   The table rows.
   */
  protected function getTableRows(array $records) {
    $rows = [];

    if ($this->tableHeaderField()) {
      foreach ($this->fields() as $field) {
        $row = [];
        $row[] = $this->createHeaderCell($this->fieldLabel($field), 'row');

        foreach ($records as $record) {
          $row[] = $this->createRowCell($record->{$field});
        }

        $rows[] = $row;
      }
    }
    else {
      foreach ($records as $record) {
        $row = [];

        if (property_exists($record, $this->rowHeaderField())) {
          $row[] = $this->createHeaderCell($record->{$this->rowHeaderField()}, 'row');
        }

        foreach ($this->fields() as $field) {
          if (property_exists($record, $field)) {
            $row[] = $this->createRowCell($record->{$field});
          }
        }

        $rows[] = $row;
      }
    }

    return $rows;
  }

  /**
   * Creates a table header cell.
   *
   * @param string $value
   *   The value.
   * @param string $scope
   *   The scope.
   *
   * @return array
   *   The header cell structure.
   */
  protected function createHeaderCell($value, $scope) {
    return [
      'data' => $value,
      'header' => TRUE,
      'scope' => $scope,
    ];
  }

  /**
   * Creates a table row cell.
   *
   * @param string $value
   *   The value.
   *
   * @return array
   *   The row cell structure.
   */
  protected function createRowCell($value) {
    return [
      'data' => $value,
      'header' => FALSE,
    ];
  }

}
