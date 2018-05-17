<?php

namespace Drupal\dvf;

use Drupal\Core\Field\FieldItemListInterface;

/**
 * Provides common methods for field formatters.
 */
trait FieldFormatterTrait {

  /**
   * Builds a renderable array for a field value.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   The field values to be rendered.
   * @param string $langcode
   *   The language that should be used to render the field.
   *
   * @return array
   *   A renderable array for $items, as an array of child elements keyed by
   *   consecutive numeric indexes starting from 0.
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
