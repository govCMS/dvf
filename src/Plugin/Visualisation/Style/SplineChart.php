<?php

namespace Drupal\dvf\Plugin\Visualisation\Style;

use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'dvf_spline_chart' visualisation style.
 *
 * @VisualisationStyle(
 *   id = "dvf_spline_chart",
 *   label = @Translation("Spline chart")
 * )
 */
class SplineChart extends AxisChart {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'spline_chart' => [
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

    $form['spline_chart'] = [
      '#type' => 'details',
      '#title' => $this->t('Spline chart settings'),
      '#tree' => TRUE,
    ];

    $form['spline_chart']['data']['points']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show data points'),
      '#description' => $this->t('Check to show the data-value points along the lines.'),
      '#default_value' => $this->config('spline_chart', 'data', 'points', 'show'),
    ];

    $form['spline_chart']['area']['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable area'),
      '#description' => $this->t('Check to fill in the area between the X axis and the lines with colour.'),
      '#default_value' => $this->config('spline_chart', 'area', 'enabled'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function chartBuildSettings(array $records) {
    $settings = parent::chartBuildSettings($records);

    $settings['chart']['data']['type'] = $this->config('spline_chart', 'area', 'enabled') ? 'area-spline' : 'spline';
    $settings['point']['show'] = $this->config('spline_chart', 'data', 'points', 'show');

    return $settings;
  }

}
