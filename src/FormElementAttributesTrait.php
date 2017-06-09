<?php

namespace Drupal\dvf;

/**
 * Provides methods to manipulate form elements.
 */
trait FormElementAttributesTrait {

  /**
   * Gets the name of a form element.
   *
   * @param array $element
   *   The form element.
   *
   * @return string
   *   The name.
   *
   * @see \Drupal\Core\Form\FormBuilder::handleInputElement()
   */
  protected static function formElementName(array $element) {
    $name = array_shift($element['#parents']);

    if (count($element['#parents'])) {
      $name .= '[' . implode('][', $element['#parents']) . ']';
    }

    return $name;
  }

  /**
   * Gets the CSS selector of a form element.
   *
   * @param array $element
   *   The form element.
   * @param string $tag
   *   The HTML tag.
   *
   * @return string
   *   The selector.
   */
  protected static function formElementSelector(array $element, $tag) {
    $name = self::formElementName($element);
    $selector = $tag . '[name="' . $name . '"]';

    return $selector;
  }

}
