<?php

namespace Drupal\dvf\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate\ProcessPluginBase;

/**
 * Converts a D7 ckan url. This is a single purpose plugin.
 *
 * @MigrateProcessPlugin(
 *   id = "ckan_convert_url"
 * )
 *
 * @code
 * process:
 *   field_d7_ckan_visualisation/0/uri:
 *     -
 *       plugin: ckan_convert_url
 *       source: ckan_visualisation_d7_uri
 * @endcode
 */
class CkanConvertUrl extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $ckan_url = 'https://data.gov.au/dataset/{ID_1}/resource/{ID_2}';
    $url_parts = explode('/', $value);

    if (!empty($url_parts[3]) && !empty($url_parts[4])) {
      $ckan_url = str_replace('{ID_1}', $url_parts[3], $ckan_url);
      $ckan_url = str_replace('{ID_2}', $url_parts[4], $ckan_url);
    }
    else {
      $ckan_url = '';
    }

    return $ckan_url;
  }

}
