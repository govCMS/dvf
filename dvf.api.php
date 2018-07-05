<?php

/**
 * @file
 * Hooks provided by the DVF module.
 *
 * @ingroup svf
 */

use Drupal\dvf\Plugin\VisualisationInterface;

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter the visualisation source configuration.
 *
 * @param array $configuration
 *   The configuration options.
 * @param \Drupal\dvf\Plugin\VisualisationInterface $visualisation
 *   The Visualisation instance.
 */
function hook_dvf_source_configuration_alter(array &$configuration, VisualisationInterface $visualisation) {
  // Change the JSON expression for entity whose id is '12345'.
  if ($visualisation->getEntity()->id() == '12345') {
    $configuration['json']['expression'] = '$.books[?(@.price > 10)]';
  }
}

/**
 * Alter the visualisation style configuration.
 *
 * @param array $configuration
 *   The configuration options.
 * @param \Drupal\dvf\Plugin\VisualisationInterface $visualisation
 *   The Visualisation instance.
 */
function hook_dvf_style_configuration_alter(array &$configuration, VisualisationInterface $visualisation) {
  // Show a title only on a 'page' bundle.
  if ($visualisation->getEntity()->bundle() == 'page') {
    $configuration['chart']['title']['show'] = TRUE;
  }
}

/**
 * Alter the visualisation render array pre render.
 *
 * @param array $build
 *   The built visualisation render array.
 * @param \Drupal\dvf\Plugin\VisualisationInterface $visualisation
 *   The Visualisation instance.
 */
function hook_dvf_visualisation_build_alter(array &$build, VisualisationInterface $visualisation) {
  // Add a custom class to all tables.
  foreach ($build as $group_id => &$item) {
    $item['table']['#attributes']['class'][] = 'my-table-' . $group_id;
  }
}

/**
 * Alter the visualisation data pre render.
 *
 * @param array $data
 *   The parsed records from the data set.
 * @param \Drupal\dvf\Plugin\VisualisationInterface $visualisation
 *   The Visualisation instance.
 */
function hook_dvf_visualisation_data_alter(array &$data, VisualisationInterface $visualisation) {
  // Count all rows.
  $count = 0;
  foreach ($data as $record) {
    foreach ($record as $value) {
      $count++;
    }
  }
}

/**
 * @} End of "addtogroup hooks".
 */
