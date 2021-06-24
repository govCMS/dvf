<?php

namespace Drupal\dvf\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

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
   * @return \Symfony\Component\HttpFoundation\Response
   *   Raw response (not using site theme).
   */
  public function helpPage($topic) {

    $dvf_path = drupal_get_path('module', 'dvf');
    $file_path = $dvf_path . '/templates/help/';
    $css_path = base_path() . $dvf_path . '/css/help.css';

    if (!file_exists($file_path . $topic . '.html.twig')) {
      $topic = FALSE;
    }

    $build = [
      '#theme' => 'help_page',
      '#description' => $this->t('DVF help page'),
      '#attributes' => ['class' => ['dvf-help-page']],
      '#topic' => $topic,
      '#title' => $this->getHelpPageTitle($topic),
      '#css' => $css_path,
    ];

    $output = \Drupal::service('renderer')->renderRoot($build);
    $response = new Response();

    return $response->setContent($output);
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
