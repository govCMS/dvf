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
