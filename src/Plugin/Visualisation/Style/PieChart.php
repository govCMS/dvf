<?php

namespace Drupal\dvf\Plugin\Visualisation\Style;

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
  public function chartBuildSettings(array $records) {
    $settings = parent::chartBuildSettings($records);

    $settings['chart']['data']['type'] = 'pie';
    $settings['chart']['data']['groups'] = $this->fieldsSorted();

    return $settings;
  }

}
