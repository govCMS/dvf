<?php

namespace Drupal\dvf\Plugin\Visualisation\Style;

use Drupal\Core\Form\FormStateInterface;
use Drupal\dvf\FormElementAttributesTrait;

/**
 * Plugin implementation of the 'dvf_donut_chart' visualisation style.
 *
 * @VisualisationStyle(
 *   id = "dvf_donut_chart",
 *   label = @Translation("Donut chart")
 * )
 */
class DonutChart extends PieChart {

  use FormElementAttributesTrait;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'donut_chart' => [
        'label' => [
          'show' => TRUE,
        ],
      ],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['donut_chart'] = [
      '#type' => 'details',
      '#title' => $this->t('Donut chart settings'),
      '#tree' => TRUE,
    ];

    $form['donut_chart']['label']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show labels'),
      '#description' => $this->t('Show or hide label on each donut piece.'),
      '#default_value' => $this->config('donut_chart', 'label', 'show'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function chartBuildSettings(array $records) {
    $settings = parent::chartBuildSettings($records);

    $settings['chart']['data']['type'] = 'donut';
    $settings['overrides']['donut']['label']['show'] = (bool) $this->config('donut_chart', 'label', 'show');

    $title = $this->config('chart', 'title', 'text');
    if (!empty($title)) {
      $settings['chart']['title']['show'] = FALSE;
      $settings['overrides']['donut']['title'] = $title;
    }

    return $settings;
  }

}
