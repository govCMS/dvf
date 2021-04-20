<?php

namespace Drupal\dvf;

use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Component\Render\FormattableMarkup;

/**
 * Class DvfHelpers.
 */
class DvfHelpers {

  /**
   * The help page base path.
   *
   * @var string
   */
  protected $helpPageBasePath;

  /**
   * Constructs a new DvfHelpers object.
   */
  public function __construct() {
    $this->helpPageBasePath = '/dvf/help/';
  }

  /**
   * Transforms a regular string into a machine_name.
   *
   * @param string $string
   *   The regular string.
   *
   * @return string
   *   The machine name version of the string.
   */
  public function transformMachineName($string) {
    return preg_replace('/[^A-Za-z0-9\-\_]/', '', strtolower(trim(str_replace(' ', '_', $string))));
  }

  /**
   * Returns a help page link, using the base path defined above.
   *
   * @param string $template_name
   *   The name of the template to link to.
   *
   *   E.g. "label-overrides" loads templates/help/label-overrides.html.twig.
   *
   * @return string
   *   The link.
   */
  public function getHelpPageLink($template_name) {
    $link = Link::fromTextAndUrl('Help', Url::fromUserInput($this->helpPageBasePath . $template_name));
    return new FormattableMarkup('<span class="dvf-admin-popup">' . $link->toString() . ' &#x29c9;</span>', []);
  }

  /**
   * Check if a value is correctly formatted JSON.
   *
   * @param string $raw_json
   *   The JSON string.
   *
   * @return bool
   *   True if JSON, false if not.
   */
  public function validateJson($raw_json) {
    return (json_decode($raw_json, TRUE) == NULL) ? FALSE : TRUE;
  }

  /**
   * Recursively removes empty elements from nested array.
   *
   * @param array $array
   *   The array to remove the empty elements from.
   *
   * @return array
   *   The filtered array.
   */
  public function filterArrayRecursive(array $array) {
    foreach ($array as $key => $value) {
      if (is_array($value)) {
        $array[$key] = self::filterArrayRecursive($array[$key]);
      }

      if (empty($array[$key])) {
        unset($array[$key]);
      }
    }

    return $array;
  }

}
