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
   * @param bool $data_table
   *   If set to TRUE the table will be outputted as a JS datatable, if FALSE
   *   the table will be outputted as a more accessible HTML table.
   *
   * @return array
   *   A table renderable array.
   */
  protected function buildTable(array $records, $data_table = FALSE) {
    $table_id = hash('sha256', time() . mt_rand());

    $table = [
      '#type' => 'container',
      '#attributes' => ['class' => 'single-table'],
    ];

    if ($data_table) {
      // Displayed as a JS datatable.
      $table['table'] = [
        '#type' => 'html_tag',
        '#tag' => 'table',
        '#attributes' => ['data-dvftables' => $table_id],
      ];

      $table['#attached']['library'] = ['dvf/dvfTables'];
      $table['#attached']['drupalSettings']['dvf']['tables'][$table_id] = $this->tableBuildSettings($records);
    }
    else {
      // Displayed as an accessible html table.
      $table['table'] = [
        '#type' => 'table',
        '#attributes' => ['data-html-dvftables' => $table_id],
      ];

      $table['table']['#header'] = $this->getTableHeader($records);
      $table['table']['#rows'] = $this->getTableRows($records);
    }

    // If our table is not the primary visualisation, exit here so we don't get
    // a duplicate of download data buttons.
    if ('dvf_table' !== $this->getPluginId()) {
      return $table;
    }

    // If $file_uri is empty/false, do not display download data button to the table.
    $file_uri = $this->getDatasetDownloadUri();
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
      'columns' => $this->getTableHeader($records),
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
   * @param array $records
   *   The set of records that we should get the values from.
   *
   * @return array
   *   The table header.
   */
  protected function getTableHeader(array $records = []) {
    $header = [];

    if ($this->tableHeaderField() || $this->rowHeaderField()) {
      $header[] = $this->createRowCell('');
    }

    $labels = $this->fieldLabels();

    if ($this->tableHeaderField()) {
      $labels = $this->getSourceFieldValues($this->tableHeaderField(), $records);
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
