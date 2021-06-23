<?php

namespace Drupal\dvf\Plugin\Visualisation\Style;

use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'dvf_radar_chart' visualisation style.
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
        'axis' => [
          'line' => [
            'show' => TRUE,
          ],
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

    $form['radar']['axis']['line']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show axis lines'),
      '#description' => $this->t('Should the axis lines be displayed'),
      '#default_value' => $this->config('radar', 'axis', 'line', 'show'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function chartBuildSettings(array $records) {
    $settings = parent::chartBuildSettings($records);

    // The billboard.js chart type.
    $settings['chart']['data']['type'] = 'radar';

    // The overrides key gets merged into the billboard.js config object.
    // @see https://naver.github.io/billboard.js/release/latest/doc/Options.html#.radar
    $radar = [];
    $radar['direction']['clockwise'] = (bool) $this->config('radar', 'direction', 'clockwise');
    $radar['axis']['line']['show'] = (bool) $this->config('radar', 'axis', 'line', 'show');
    $settings['overrides']['radar'] = $radar;

    return $settings;
  }

}
