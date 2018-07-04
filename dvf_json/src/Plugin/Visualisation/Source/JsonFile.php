<?php

namespace Drupal\dvf_json\Plugin\Visualisation\Source;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\dvf\Plugin\VisualisationInterface;
use Drupal\dvf\Plugin\Visualisation\Source\VisualisationSourceBase;
use Flow\JSONPath\JSONPath;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'dvf_json_file' visualisation source.
 *
 * @VisualisationSource(
 *   id = "dvf_json_file",
 *   label = @Translation("JSON file"),
 *   category = @Translation("JSON"),
 *   visualisation_types = {
 *     "dvf_file",
 *     "dvf_url"
 *   }
 * )
 */
class JsonFile extends VisualisationSourceBase implements ContainerFactoryPluginInterface {

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * The JSON file fields.
   *
   * @var array
   */
  protected $fields;

  /**
   * The JSON file records.
   *
   * @var array
   */
  protected $records;

  /**
   * Constructs a new JsonFile.
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
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend.
   * @param \GuzzleHttp\Client $http_client
   *   The HTTP client.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, VisualisationInterface $visualisation = NULL, ModuleHandlerInterface $module_handler, CacheBackendInterface $cache, Client $http_client) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $visualisation, $module_handler);
    $this->cache = $cache;
    $this->httpClient = $http_client;
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
      $container->get('cache.dvf_json'),
      $container->get('http_client')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'json' => [
        'expression' => '',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form['json'] = [
      '#type' => 'details',
      '#title' => $this->t('JSON file settings'),
      '#open' => TRUE,
      '#tree' => TRUE,
    ];

    $form['json']['expression'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Expression'),
      '#description' => $this->t('JSONPath expression used to extract the data. Visit the <a href="https://github.com/govCMS/dvf/tree/8.x-1.x/dvf_json">online documentation</a> for more information on how to use JSONPath expressions.'),
      '#default_value' => $this->config('json', 'expression'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFields() {
    $fields = [];
    $data = $this->getData();

    if (is_array($data) && !empty($data)) {
      $fields = array_keys(array_shift($data));
      $fields = array_combine($fields, $fields);
    }

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getRecords() {
    $records = [];

    try {
      $json = new JSONPath($this->getData());
      $json = $json->find($this->config('json', 'expression'));
    }
    catch (\Exception $e) {
      return $records;
    }

    foreach ($this->getFields() as $field_key => $field_label) {
      foreach ($json->data() as $record_id => $record) {
        if (!isset($records[$record_id])) {
          $records[$record_id] = new \stdClass();
        }
        $records[$record_id]->{$field_label} = array_key_exists($field_key, $record) ? $record[$field_key] : '';
      }
    }

    return $records;
  }

  /**
   * Returns the JSON data.
   *
   * @return array
   *   An array of JSON records.
   */
  public function getData() {
    $cache_key = $this->getCacheKey();
    $cache_object = $this->cache->get($cache_key);

    if (is_object($cache_object)) {
      $data = $cache_object->data;
    }
    else {
      $data = $this->fetchData();
      $this->cache->set($cache_key, $data);
    }

    return json_decode($data, TRUE);
  }

  /**
   * Fetches the JSON data.
   *
   * @return string
   *   A string containing the JSON file data.
   */
  protected function fetchData() {
    try {
      $uri = $this->config('uri');
      $response = $this->httpClient->get($uri)->getBody()->getContents();
    }
    catch (\Exception $e) {
      $response = NULL;
    }

    $data = '{}';

    if ($response) {
      $data = $response;
    }

    return $data;
  }

  /**
   * Gets a cache key for this plugin.
   *
   * @return string
   *   The cache key.
   */
  protected function getCacheKey() {
    $plugin_id = hash('sha256', $this->getPluginId());
    $uri = $this->config('uri');

    return $plugin_id . ':' . $uri;
  }

}
