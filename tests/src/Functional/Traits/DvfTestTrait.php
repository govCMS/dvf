<?php

namespace Drupal\Tests\dvf\Functional\Traits;

/**
 * General trait providing helper methods for the dvf test suite.
 */
trait DvfTestTrait {

  /**
   * Visits a page and asserts that text exists on page.
   *
   * @param string $page_path
   *   Path of page to visit.
   * @param string $text
   *   Text to be asserted on page.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  public function visitAndAssertText($page_path, $text) {
    $this->drupalGet($page_path);
    $this->assertSession()
      ->pageTextContains($text);
  }

}
