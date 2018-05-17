<?php

namespace Drupal\dvf\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\dvf\FieldFormatterTrait;

/**
 * Plugin implementation of the 'dvf_file_default' field formatter.
 *
 * @FieldFormatter(
 *   id = "dvf_file_default",
 *   label = @Translation("File to visualisation"),
 *   field_types = {
 *     "dvf_file"
 *   }
 * )
 */
class VisualisationFileFormatter extends FormatterBase {

  use FieldFormatterTrait;

}
