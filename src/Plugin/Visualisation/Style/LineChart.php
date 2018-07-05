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

    $form['line_chart']['stacked'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Stacked'),
      '#description' => $this->t('Check to stack the lines on top of each other. (E.g. Lines will not overlap if this option is enabled.)'),
      '#default_value' => $this->config('line_chart', 'stacked'),
    ];

    $form['line_chart']['data']['order'] = [
      '#type' => 'select',
      '#title' => $this->t('Stacked data order'),
      '#description' => $this->t('Define the order of the stacked data.'),
      '#options' => [
        'asc' => $this->t('Ascending'),
        'desc' => $this->t('Descending'),
        '' => $this->t('Order defined in the dataset'),
      ],
      '#default_value' => $this->config('line_chart', 'data', 'order'),
    ];

    $form['line_chart']['data']['points']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show data points'),
      '#description' => $this->t('Check to show the data-value points along the lines.'),
      '#default_value' => $this->config('line_chart', 'data', 'points', 'show'),
    ];

    $form['line_chart']['area']['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable area'),
      '#description' => $this->t('Check to fill in the area between the X axis and the lines with colour.'),
      '#default_value' => $this->config('line_chart', 'area', 'enabled'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public static function afterBuildSettingsForm(array $element, FormStateInterface $form_state) {
    $element = parent::afterBuildSettingsForm($element, $form_state);

    $selectors = [
      'stacked' => self::formElementSelector($element['line_chart']['stacked'], 'input'),
    ];

    $element['line_chart']['data']['order']['#states'] = [
      'visible' => [$selectors['stacked'] => ['checked' => TRUE]],
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function chartBuildSettings(array $records) {
    $settings = parent::chartBuildSettings($records);

    $settings['chart']['data']['type'] = $this->config('line_chart', 'area', 'enabled') ? 'area' : 'line';
    $settings['chart']['data']['stacked'] = $this->config('line_chart', 'stacked');
    $settings['chart']['data']['groups'] = $this->config('data', 'fields');
    $settings['point']['show'] = $this->config('line_chart', 'data', 'points', 'show');

    return $settings;
  }

}
