<?php

namespace Drupal\dvf\Plugin\Visualisation\Style;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'dvf_table' visualisation style.
 *
 * @VisualisationStyle(
 *   id = "dvf_table",
 *   label = @Translation("Table")
 * )
 */
class Table extends TableVisualisationStyleBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'table' => [
        'datatable' => TRUE,
        'table_header_field' => '',
        'row_header_field' => '',
        'options' => [
          'page_length' => 10,
          'searching' => TRUE,
        ],
      ],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['table'] = [
      '#type' => 'details',
      '#title' => $this->t('Table settings'),
      '#tree' => TRUE,
    ];

    $form['table']['datatable'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display as datatable'),
      '#description' => $this->t('Render table using datatables.js. This may reduce accessibility as requires Javascript enabled to view data.'),
      '#default_value' => $this->config('table', 'datatable'),
    ];

    $form['table']['table_header_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Table header field'),
      '#description' => $this->t("Optionally select a field to use it's values as the table headers."),
      '#options' => $this->getSourceFieldOptions(),
      '#empty_option' => $this->t('- None -'),
      '#empty_value' => '',
      '#default_value' => $this->config('table', 'table_header_field'),
    ];

    $form['table']['row_header_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Row header field'),
      '#description' => $this->t("Optionally select a field to use it's values as the row headers. This option is ignored if the above table header field is selected."),
      '#options' => $this->getSourceFieldOptions(),
      '#empty_option' => $this->t('- None -'),
      '#empty_value' => '',
      '#default_value' => $this->config('table', 'row_header_field'),
    ];

    $form['table']['options'] = [
      '#type' => 'details',
      '#title' => $this->t('Datatable options'),
      '#tree' => TRUE,
    ];

    $form['table']['options']['page_length'] = [
      '#type' => 'select',
      '#title' => $this->t('Page length'),
      '#options' => $this->getPageLengthOptions(),
      '#description' => $this->t('Number of rows per page.'),
      '#default_value' => $this->config('table', 'options', 'page_length'),
    ];

    $form['table']['options']['searching'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable searching'),
      '#description' => $this->t('Allows the search abilities of DataTables.'),
      '#default_value' => $this->config('table', 'options', 'searching'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $datatable = (bool) $this->config('table', 'datatable');

    foreach ($this->getSourceRecords() as $group_key => $group_records) {
      $group_id = strtolower(Html::cleanCssIdentifier($group_key));

      $build[$group_id] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['dvf--wrapper', 'dvf-table--wrapper', 'dvf-table--wrapper--' . $group_id],
        ],
      ];

      $build[$group_key]['table'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['dvf-table']],
        'heading' => $this->buildSplitHeading($group_key),
        'content' => $this->buildTable($group_records, $datatable),
      ];
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function tableHeaderField() {
    return $this->config('table', 'table_header_field');
  }

  /**
   * {@inheritdoc}
   */
  protected function rowHeaderField() {
    return $this->config('table', 'row_header_field');
  }

  /**
   * {@inheritdoc}
   */
  protected function tableBuildSettings(array $records) {
    $settings = parent::tableBuildSettings($records);

    $settings['tableOptions']['pageLength'] = (int) $this->config('table', 'options', 'page_length');
    $settings['tableOptions']['searching'] = $this->config('table', 'options', 'searching');

    return $settings;
  }

  /**
   * Returns page length options.
   *
   * @return array
   *   An array of page length options.
   */
  protected function getPageLengthOptions() {
    return [10 => 10, 25 => 25, 50 => 50, 100 => 100];
  }

}
