<?php

namespace Drupal\dvf\Plugin\Visualisation\Style;

use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'dvf_scatter_plot_chart' visualisation style.
 *
 * @VisualisationStyle(
 *   id = "dvf_scatter_plot_chart",
 *   label = @Translation("Scatter plot chart")
 * )
 */
class ScatterPlotChart extends AxisChart {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'scatter_plot_chart' => [
        'point' => [
          'size' => '2.5',
        ],
      ],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['scatter_plot_chart'] = [
      '#type' => 'details',
      '#title' => $this->t('Scatter plot chart settings'),
      '#tree' => TRUE,
    ];

    $form['scatter_plot_chart']['point']['size'] = [
      '#type' => 'number',
      '#title' => $this->t('Point size'),
      '#description' => $this->t('Define the size of each point on the scatter plot chart. Defaults to 2.5.'),
      '#default_value' => $this->config('scatter_plot_chart', 'point', 'size'),
      '#step' => '0.1',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function chartBuildSettings(array $records) {
    $settings = parent::chartBuildSettings($records);

    $settings['chart']['data']['type'] = 'scatter';
    $settings['point']['radius'] = $this->config('scatter_plot_chart', 'point', 'size');

    return $settings;
  }

}
