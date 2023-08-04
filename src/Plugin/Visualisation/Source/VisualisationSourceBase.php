<?php

namespace Drupal\dvf\Plugin\Visualisation\Source;

use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\dvf\ConfigurablePluginTrait;
use Drupal\dvf\Plugin\VisualisationInterface;
use Drupal\dvf\Plugin\VisualisationSourceInterface;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;

/**
 * Provides a base class for VisualisationSource plugins.
 */
abstract class VisualisationSourceBase extends PluginBase implements VisualisationSourceInterface, ContainerFactoryPluginInterface {

  use ConfigurablePluginTrait;
  use MessengerTrait;
  use StringTranslationTrait;

  /**
   * The visualisation.
   *
   * @var \Drupal\dvf\Plugin\VisualisationInterface
   */
  protected $visualisation;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * The iterator.
   *
   * @var \Iterator
   */
  protected $iterator;

  /**
   * The file URL generator.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * Constructs a new VisualisationSourceBase.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\dvf\Plugin\VisualisationInterface $visualisation
   *   The visualisation context in which the plugin will run.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Psr\Log\LoggerInterface $logger
   *   Instance of the logger object.
   * @param \GuzzleHttp\Client $http_client
   *   The HTTP client.
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $file_url_generator
   *   The file URL generator.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    VisualisationInterface $visualisation = NULL,
    ModuleHandlerInterface $module_handler,
    LoggerInterface $logger,
    Client $http_client,
    FileUrlGeneratorInterface $file_url_generator = NULL
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->visualisation = $visualisation;
    $this->moduleHandler = $module_handler;
    $this->logger = $logger;
    $this->httpClient = $http_client;

    if (!$file_url_generator) {
      @trigger_error('Calling VisualisationSourceBase::__construct() without the $file_url_generator argument is deprecated in drupal:9.5.0 and the $file_url_generator argument will be required in drupal:10.0.0. See https://www.drupal.org/node/2940031', E_USER_DEPRECATED);
      $file_url_generator = \Drupal::service('file_url_generator');
    }
    $this->fileUrlGenerator = $file_url_generator;
  }

  /**
   * Creates an instance of the plugin.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container to pull out services used in the plugin.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\dvf\Plugin\VisualisationInterface $visualisation
   *   The visualisation context in which the plugin will run.
   *
   * @return static
   *   Returns an instance of this plugin.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, VisualisationInterface $visualisation = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $visualisation,
      $container->get('module_handler'),
      $container->get('file_url_generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return NestedArray::mergeDeep($this->defaultConfiguration(), $this->configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * Initializes the iterator with the source data.
   *
   * @return \Traversable
   *   An array of data for this source.
   */
  protected function initializeIterator() {
    return new \ArrayIterator($this->getRecords());
  }

  /**
   * Returns the iterator.
   *
   * @return \Iterator
   *   The iterator.
   */
  protected function getIterator() {
    if (!isset($this->iterator)) {
      $this->iterator = $this->initializeIterator();
    }

    return $this->iterator;
  }

  /**
   * {@inheritdoc}
   */
  public function current() {
    return $this->getIterator()->current();
  }

  /**
   * {@inheritdoc}
   */
  public function next() {
    $this->getIterator()->next();
  }

  /**
   * {@inheritdoc}
   */
  public function key() {
    return $this->getIterator()->key();
  }

  /**
   * {@inheritdoc}
   */
  public function valid() {
    return $this->getIterator()->valid();
  }

  /**
   * {@inheritdoc}
   */
  public function rewind() {
    $this->getIterator()->rewind();
  }

  /**
   * {@inheritdoc}
   */
  public function count() {
    $iterator = $this->getIterator();
    return ($iterator instanceof \Countable) ? $iterator->count() : iterator_count($this->initializeIterator());
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getVisualisation() {
    return $this->visualisation;
  }

  /**
   * {@inheritdoc}
   */
  public function getDownloadUrl() {
    $uri = $this->config('uri');
    return ('dvf_file' === $this->visualisation->getPluginId())
      ? $this->fileUrlGenerator->generateAbsoluteString($uri) : $uri;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheExpiry() {
    // Get the cache time set from visualisation.
    $configuration_options = $this->visualisation->getConfiguration('style');
    $cache_object_expire = FALSE;

    if (!empty($configuration_options['style']['options']['data']['cache_expiry'])) {
      $cache_object_expire = $configuration_options['style']['options']['data']['cache_expiry'];
    }

    // If not numeric (e.g. global_default, false) get the global default.
    if (!is_numeric($cache_object_expire)) {
      $cache_global_config = \Drupal::config('system.performance')->get('cache');
      $cache_object_expire = $cache_global_config['page']['max_age'];
    }

    return \Drupal::time()->getRequestTime() + $cache_object_expire;
  }

  /**
   * Abstraction for getting the content from a URI.
   *
   * @param string $uri
   *   Either a file stream wrapper (eg. public://), an absolute URL (eg http)
   *   or an internal drupal path.
   *
   * @return false|string
   *   The data content. false if no content found.
   */
  protected function getContentFromUri($uri) {
    // Check if a stream wrapper (local file) if so return file contents.
    $manager = \Drupal::service('stream_wrapper_manager');
    $wrappers = array_keys($manager->getWrappers(StreamWrapperInterface::LOCAL));
    foreach ($wrappers as $wrapper) {
      if (strpos($uri, $wrapper . '://') === 0) {
        return file_get_contents($uri);
      }
    }

    // If not external (eg a view route), create absolute url.
    if (!UrlHelper::isExternal($uri)) {
      $uri = Url::fromUserInput($uri, ['absolute' => TRUE])->toString();
    }

    return $this->httpClient->get($uri)->getBody()->getContents();
  }

}
