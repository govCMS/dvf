<?php

namespace Drupal\dvf\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'dvf_url_default' field formatter.
 *
 * @FieldFormatter(
 *   id = "dvf_url_default",
 *   label = @Translation("URL to visualisation"),
 *   field_types = {
 *     "dvf_url"
 *   }
 * )
 */
class VisualisationUrlFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    /** @var \Drupal\dvf\Plugin\Field\FieldType\VisualisationUrlItem $item */
    foreach ($items as $delta => $item) {
      $element[$delta] = $item
        ->getVisualisationPlugin()
        ->getStylePlugin()
        ->build();
    }

    return $element;
  }

}
