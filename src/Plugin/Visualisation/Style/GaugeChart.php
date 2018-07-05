<?php

namespace Drupal\dvf\Plugin\Visualisation\Style;

use Drupal\Core\Form\FormStateInterface;
use Drupal\dvf\FormElementAttributesTrait;

/**
 * Plugin implementation of the 'dvf_gauge_chart' visualisation style.
 *
 * @VisualisationStyle(
 *   id = "dvf_gauge_chart",
 *   label = @Translation("Gauge chart")
 * )
 */
class GaugeChart extends AxisChart {

  use FormElementAttributesTrait;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'gauge_chart' => [
        'gauge' => [
          'label' => [
            'percentage' => FALSE,
            'show' => FALSE,
          ],
          'units' => '',
          'width' => '39',
          'min' => '0',
          'max' => '100',
        ],
      ],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['gauge_chart'] = [
      '#type' => 'details',
      '#title' => $this->t('Gauge chart settings'),
      '#tree' => TRUE,
    ];

    $form['gauge_chart']['gauge']['label']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show min/max labels'),
      '#description' => $this->t('Check to show the min, max and unit labels.'),
      '#default_value' => $this->config('gauge_chart', 'gauge', 'label', 'show'),
    ];

    $form['gauge_chart']['gauge']['units'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Unit label'),
      '#description' => $this->t('Enter a unit type, eg %, mm, kg etc'),
      '#default_value' => $this->config('gauge_chart', 'gauge', 'units'),
    ];

    $form['gauge_chart']['gauge']['label']['percentage'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show default inline %'),
      '#description' => $this->t('Check to show the default inline percentage symbol.'),
      '#default_value' => $this->config('gauge_chart', 'gauge', 'label', 'percentage'),
    ];

    $form['gauge_chart']['gauge']['width'] = [
      '#type' => 'number',
      '#title' => $this->t('Width'),
      '#description' => $this->t('Adjust arc thickness, eg 39'),
      '#default_value' => $this->config('gauge_chart', 'gauge', 'width'),
    ];

    $form['gauge_chart']['gauge']['min'] = [
      '#type' => 'number',
      '#title' => $this->t('Min'),
      '#description' => $this->t('0 is default, can handle negative min, eg vacuum / voltage / current flow etc'),
      '#default_value' => $this->config('gauge_chart', 'gauge', 'min'),
    ];

    $form['gauge_chart']['gauge']['max'] = [
      '#type' => 'number',
      '#title' => $this->t('Max'),
      '#description' => $this->t('Enter max value, 100 is default.'),
      '#default_value' => $this->config('gauge_chart', 'gauge', 'max'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function chartBuildSettings(array $records) {
    $settings = parent::chartBuildSettings($records);

    $settings['chart']['data']['type'] = 'gauge';
    $settings['chart']['data']['groups'] = $this->config('data', 'fields');
    $settings['gauge']['label']['show'] = $this->config('gauge_chart', 'gauge', 'label', 'show');
    $settings['gauge']['label']['percentage'] = $this->config('gauge_chart', 'gauge', 'label', 'percentage');
    $settings['gauge']['units'] = $this->config('gauge_chart', 'gauge', 'units');
    $settings['gauge']['width'] = $this->config('gauge_chart', 'gauge', 'width');
    $settings['gauge']['min'] = $this->config('gauge_chart', 'gauge', 'min');
    $settings['gauge']['max'] = $this->config('gauge_chart', 'gauge', 'max');

    return $settings;
  }

}
