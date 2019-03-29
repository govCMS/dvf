<?php

namespace Drupal\dvf\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class DvfHelpController.
 */
class DvfHelpController extends ControllerBase {

  /**
   * Returns the help page information ready to load in partial twig template.
   *
   * @param string $topic
   *   The topic or help page subject to load.
   *
   * @return array
   *   An array that renders the help page in the twig template.
   */
  public function helpPage($topic) {

    $file_path = drupal_get_path('module', 'dvf') . '/templates/help/';

    if (!file_exists($file_path . $topic . '.html.twig')) {
      $topic = FALSE;
    }

    return [
      '#theme' => 'help_page',
      '#description' => $this->t('DVF help page'),
      '#attributes' => ['class' => ['dvf-help-page']],
      '#topic' => $topic,
    ];
  }

  /**
   * Returns the title for a help page.
   *
   * @param string $topic
   *   The topic of the help page.
   *
   * @return string
   *   The title of the help page.
   */
  public function getHelpPageTitle($topic) {
    return ucfirst(str_replace('-', ' ', $topic));
  }

}
