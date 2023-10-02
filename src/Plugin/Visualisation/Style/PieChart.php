<?php

namespace Drupal\dvf\Plugin\Visualisation\Style;

use Drupal\Core\Form\FormStateInterface;
use Drupal\dvf\FormElementAttributesTrait;

/**
 * Plugin implementation of the 'dvf_pie_chart' visualisation style.
 *
 * @VisualisationStyle(
 *   id = "dvf_pie_chart",
 *   label = @Translation("Pie chart")
 * )
 */
class PieChart extends AxisChart {

  use FormElementAttributesTrait;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'pie_chart' => [],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    $form['chart']['#title'] = $this->t('Pie chart settings');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function chartBuildSettings(array $records) {
    $settings = parent::chartBuildSettings($records);

    $settings['chart']['data']['type'] = 'pie';
    $settings['chart']['data']['groups'] = $this->fieldsSorted();

    return $settings;
  }

}
