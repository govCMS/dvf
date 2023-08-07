<?php

namespace Drupal\dvf\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provide information for DvfHelp page.
 */
class DvfHelpController extends ControllerBase {

  /**
   * The module extension list.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  protected $moduleExtensionList;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a new DvfHelpController object.
   *
   * @param \Drupal\Core\Extension\ModuleExtensionList $extension_list_module
   *   The module extension list.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(ModuleExtensionList $extension_list_module, RendererInterface $renderer) {
    $this->moduleExtensionList = $extension_list_module;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('extension.list.module'),
      $container->get('renderer')
    );
  }

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

    $dvf_path = $this->moduleExtensionList->getPath('dvf');
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

    $output = $this->renderer->renderRoot($build);
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
