<?php

namespace Drupal\dvf;

use Drupal\Core\Link;
use Drupal\Core\Url;

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
   * @param string $title
   *   The title of the link.
   *
   * @return string
   *   The link.
   */
  public function getHelpPageLink($title) {
    $link = Link::fromTextAndUrl('Help', Url::fromUserInput($this->helpPageBasePath . $title));
    return $link->toString();
  }

}
