<?php

namespace Drupal\dvf\Plugin\Field\FieldFormatter;

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
class VisualisationFileFormatter extends VisualisationUrlFormatter { }
