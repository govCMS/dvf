<?php

namespace Drupal\dvf\Plugin\Visualisation\Style;

use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'dvf_line_chart' visualisation style.
 *
 * @VisualisationStyle(
 *   id = "dvf_line_chart",
 *   label = @Translation("Line chart")
 * )
 */
class LineChart extends AxisChart {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'line_chart' => [
        'stacked' => FALSE,
        'data' => [
          'points' => [
            'show' => TRUE,
          ],
        ],
        'area' => [
          'enabled' => FALSE,
        ],
      ],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['line_chart'] = [
      '#type' => 'details',
      '#title' => $this->t('Line chart settings'),
      '#tree' => TRUE,
    ];

    $form['line_chart']['data']['points']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show data points'),
      '#description' => $this->t('Check to show the points along the lines.'),
      '#default_value' => $this->config('line_chart', 'data', 'points', 'show'),
    ];

    $form['line_chart']['area']['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable area'),
      '#description' => $this->t('Check to fill in the area between the X axis and the lines.'),
      '#default_value' => $this->config('line_chart', 'area', 'enabled'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function chartBuildSettings(array $records) {
    $settings = parent::chartBuildSettings($records);

    $settings['chart']['data']['type'] = $this->config('line_chart', 'area', 'enabled') ? 'area' : 'line';
    $settings['point']['show'] = $this->config('line_chart', 'data', 'points', 'show');

    return $settings;
  }

}
