<?php

namespace Drupal\dvf\Plugin\Visualisation\Style;

use Drupal\Core\Form\FormStateInterface;
use Drupal\dvf\FormElementAttributesTrait;

/**
 * Plugin implementation of the 'dvf_bar_chart' visualisation style.
 *
 * @VisualisationStyle(
 *   id = "dvf_bar_chart",
 *   label = @Translation("Bar chart")
 * )
 */
class BarChart extends AxisChart {

  use FormElementAttributesTrait;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'bar_chart' => [
        'stacked' => FALSE,
        'data' => [
          'order' => 'desc',
        ],
        'bar' => [
          'width' => [
            'ratio' => '0.5',
            'value' => '',
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

    $form['bar_chart'] = [
      '#type' => 'details',
      '#title' => $this->t('Bar chart settings'),
      '#tree' => TRUE,
    ];

    $form['bar_chart']['stacked'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Stacked'),
      '#description' => $this->t('Check to stack the bars on top of each other (E.g. Show one bar per data-set row.)'),
      '#default_value' => $this->config('bar_chart', 'stacked'),
    ];

    $form['bar_chart']['data']['order'] = [
      '#type' => 'select',
      '#title' => $this->t('Stacked data order'),
      '#description' => $this->t('Define the order of the stacked data.'),
      '#options' => [
        'asc' => $this->t('Ascending'),
        'desc' => $this->t('Descending'),
        '' => $this->t('Order defined in the dataset'),
      ],
      '#default_value' => $this->config('bar_chart', 'data', 'order'),
    ];

    $form['bar_chart']['bar']['width']['ratio'] = [
      '#type' => 'select',
      '#title' => $this->t('Bar width ratio'),
      '#description' => $this->t('Override the default width ratio of the bars.'),
      '#options' => [
        '0.2' => $this->t('Extra thin'),
        '0.3' => $this->t('Thin'),
        '0.5' => $this->t('Standard'),
        '0.75' => $this->t('Thick'),
        '0.9' => $this->t('Extra thick'),
        'manual' => $this->t('Manually defined'),
      ],
      '#default_value' => $this->config('bar_chart', 'bar', 'width', 'ratio'),
    ];

    $form['bar_chart']['bar']['width']['value'] = [
      '#type' => 'number',
      '#title' => $this->t('Bar width'),
      '#description' => $this->t('Define the width of the bars.'),
      '#default_value' => $this->config('bar_chart', 'bar', 'width', 'value'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public static function afterBuildSettingsForm(array $element, FormStateInterface $form_state) {
    $element = parent::afterBuildSettingsForm($element, $form_state);

    $selectors = [
      'stacked' => self::formElementSelector($element['bar_chart']['stacked'], 'input'),
      'bar_width_ratio' => self::formElementSelector($element['bar_chart']['bar']['width']['ratio'], 'select'),
    ];

    $element['bar_chart']['data']['order']['#states'] = [
      'visible' => [$selectors['stacked'] => ['checked' => TRUE]],
    ];

    $element['bar_chart']['bar']['width']['value']['#states'] = [
      'visible' => [$selectors['bar_width_ratio'] => ['value' => 'manual']],
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  protected function chartBuildSettings(array $records) {
    $settings = parent::chartBuildSettings($records);

    $settings['chart']['data']['type'] = 'bar';
    $settings['chart']['data']['stacked'] = $this->config('bar_chart', 'stacked');
    $settings['chart']['data']['groups'] = $this->config('data', 'fields');
    $settings['chart']['data']['order'] = $this->config('bar_chart', 'data', 'order');
    $settings['bar']['width']['ratio'] = $this->config('bar_chart', 'bar', 'width', 'ratio');
    $settings['bar']['width']['value'] = $this->config('bar_chart', 'bar', 'width', 'value');

    return $settings;
  }

}
