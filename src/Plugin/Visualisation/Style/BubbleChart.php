<?php

namespace Drupal\dvf\Plugin\Visualisation\Style;

use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'dvf_bubble_chart' visualisation style.
 *
 * @VisualisationStyle(
 *   id = "dvf_bubble_chart",
 *   label = @Translation("Bubble chart")
 * )
 */
class BubbleChart extends AxisChart {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'bubble' => [
        'max_radius' => 35,
      ],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['bubble'] = [
      '#type' => 'details',
      '#title' => $this->t('Bubble chart settings'),
      '#tree' => TRUE,
    ];

    $form['bubble']['max_radius'] = [
      '#type' => 'number',
      '#title' => $this->t('Maximum radius'),
      '#description' => $this->t('The maximum radius of the largest bubble'),
      '#default_value' => $this->config('bubble', 'max_radius'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function chartBuildSettings(array $records) {
    $settings = parent::chartBuildSettings($records);

    // The billboard.js chart type.
    $settings['chart']['data']['type'] = 'bubble';

    // The overrides key gets merged into the billboard.js config object.
    // @see https://naver.github.io/billboard.js/release/latest/doc/Options.html#.bubble
    $settings['overrides']['bubble']['maxR'] = $this->config('bubble', 'max_radius');

    return $settings;
  }

}
