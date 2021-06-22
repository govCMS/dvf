<?php

namespace Drupal\dvf\Plugin\Visualisation\Style;

use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'dvf_radar_chart' visualisation style.
 *
 * @see https://naver.github.io/billboard.js/release/latest/doc/Options.html#.radar
 *
 * @VisualisationStyle(
 *   id = "dvf_radar_chart",
 *   label = @Translation("Radar chart")
 * )
 */
class RadarChart extends AxisChart {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'radar' => [
        'direction' => [
          'clockwise' => FALSE,
        ],
      ],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['radar'] = [
      '#type' => 'details',
      '#title' => $this->t('Radar chart settings'),
      '#tree' => TRUE,
    ];

    $form['radar']['direction']['clockwise'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Clockwise'),
      '#description' => $this->t('Draw the chart in a clockwise direction.'),
      '#default_value' => $this->config('radar', 'direction', 'clockwise'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function chartBuildSettings(array $records) {
    $settings = parent::chartBuildSettings($records);

    $settings['chart']['data']['type'] = 'radar';
    $settings['radar'] = $this->config('radar');

    return $settings;
  }

}
