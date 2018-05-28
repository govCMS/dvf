<?php

namespace Drupal\dvf\Plugin\Visualisation\Style;

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
        'table_header_field' => '',
        'row_header_field' => '',
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

    $form['table']['table_header_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Table header field'),
      '#description' => $this->t('What field contains the table header.'),
      '#options' => $this->getSourceFieldOptions(),
      '#empty_option' => $this->t('- None -'),
      '#empty_value' => '',
      '#default_value' => $this->config('table', 'table_header_field'),
    ];

    $form['table']['row_header_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Row header field'),
      '#description' => $this->t('What field contains the row header. This option is ignored if table header field is selected.'),
      '#options' => $this->getSourceFieldOptions(),
      '#empty_option' => $this->t('- None -'),
      '#empty_value' => '',
      '#default_value' => $this->config('table', 'row_header_field'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    foreach ($this->getSourceRecords() as $group_key => $group_records) {
      $build[$group_key]['table'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['dvf-table']],
        'content' => $this->buildTable($group_records),
      ];
    }

    $this->postBuild($build);

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

}
