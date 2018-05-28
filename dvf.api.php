<?php

/**
 * @file
 * Hooks provided by the DVF module.
 *
 * @ingroup svf
 */

use Drupal\dvf\Plugin\VisualisationStyleInterface;

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter the configuration settings prior to building visualisation.
 *
 * @param array $configuration
 *   An array of configuration options.
 * @param \Drupal\dvf\Plugin\VisualisationStyleInterface $visualisation_style
 *   Visualisation style instance.
 */
function hook_dvf_style_configuration_alter(array &$configuration, VisualisationStyleInterface $visualisation_style) {
  // Show a title only on a 'page' bundle.
  if ($visualisation_style->getVisualisation()->getEntity()->bundle() == 'page') {
    $configuration['chart']['title']['show'] = TRUE;
  }
}

/**
 * Alter the built visualisation array pre render.
 *
 * @param array $build
 *   The built visualisation array pre render..
 * @param \Drupal\dvf\Plugin\VisualisationStyleInterface $visualisation_style
 *   Visualisation style instance.
 */
function hook_dvf_build_alter(array &$build, VisualisationStyleInterface $visualisation_style) {
  // Add a custom class to all tables.
  foreach ($build as $group_id => &$item) {
    $item['table']['#attributes']['class'][] = 'my-table-' . $group_id;
  }
}

/**
 * Alter the records returned from the source plugin.
 *
 * @param array $records
 *   The parsed records from the data set.
 * @param \Drupal\dvf\Plugin\VisualisationStyleInterface $visualisation_style
 *   Visualisation style instance.
 */
function hook_dvf_records_alter(array &$records, VisualisationStyleInterface $visualisation_style) {
  // Count all rows.
  $count = 0;
  foreach ($records as $record) {
    foreach ($record as $value) {
      $count++;
    }
  }
}

/**
 * @} End of "addtogroup hooks".
 */
