<?php

namespace Drupal\dvf_csv\Plugin\Visualisation\Source;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\dvf\Plugin\VisualisationInterface;
use Drupal\dvf\Plugin\Visualisation\Source\VisualisationSourceBase;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'dvf_csv_remote' visualisation source.
 *
 * @VisualisationSource(
 *   id = "dvf_csv_remote",
 *   label = @Translation("Remote CSV file"),
 *   category = @Translation("CSV"),
 *   type = "url"
 * )
 */
class RemoteCsv extends VisualisationSourceBase implements ContainerFactoryPluginInterface {

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
   * The CKAN resource fields.
   *
   * @var array
   */
  protected $fields;

  /**
   * The CKAN resource records.
   *
   * @var array
   */
  protected $records;

  /**
   * Constructs a new CkanSource.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\dvf\Plugin\VisualisationInterface $visualisation
   *   The visualisation context in which the plugin will run.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend.
   * @param \GuzzleHttp\Client $http_client
   *   The HTTP client.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, VisualisationInterface $visualisation, CacheBackendInterface $cache, Client $http_client) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $visualisation);
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
      $container->get('cache.dvf_csv'),
      $container->get('http_client')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function initializeIterator() {
    return new \ArrayIterator($this->getRecords());
  }

  /**
   * {@inheritdoc}
   */
  public function getFields() {
    $fields = $this->getHeaders();
    $fields = array_combine($fields, $fields);

    return $fields;
  }

  /**
   * Returns the records.
   *
   * @return array
   *   An array of CKAN resource records.
   */
  public function getRecords() {
    $records = [];

    foreach ($this->getHeaders() as $header_index => $header_label) {
      foreach ($this->getRows() as $row_id => $row) {
        if (!isset($records[$row_id])) {
          $records[$row_id] = new \stdClass();
        }
        $records[$row_id]->{$header_label} = array_key_exists($header_index, $row) ? $row[$header_index] : '';
      }
    }

    return $records;
  }

  /**
   * Returns the CSV headers.
   *
   * @return array
   *   An array of CSV headers.
   */
  protected function getHeaders() {
    $data = $this->getData();
    return array_shift($data);
  }

  /**
   * Returns the CSV rows.
   *
   * @return array
   *   An array of CSV rows, excluding the headers.
   */
  protected function getRows() {
    $rows = $this->getData();

    // Remove headers.
    array_shift($rows);

    return $rows;
  }

  /**
   * Returns the CSV data.
   *
   * @return array
   *   An array of CSV rows, including the headers.
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

    return $data;
  }

  /**
   * Fetches the CSV data.
   *
   * @return array
   *   An array containing the CSV file data.
   */
  protected function fetchData() {
    try {
      $response = $this->httpClient->get($this->configuration['uri'])->getBody()->getContents();
    }
    catch (\Exception $e) {
      $response = NULL;
    }

    $data = [];

    if ($response) {
      foreach (explode(PHP_EOL, $response) as $line) {
        $data[] = str_getcsv($line);
      }
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
    $uri = $this->configuration['uri'];

    return $plugin_id . ':' . $uri;
  }

}
