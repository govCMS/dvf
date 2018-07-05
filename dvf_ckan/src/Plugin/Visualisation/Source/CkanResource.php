<?php

namespace Drupal\dvf_ckan\Plugin\Visualisation\Source;

use Drupal\ckan_connect\Client\CkanClientInterface;
use Drupal\ckan_connect\Parser\CkanResourceUrlParserInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\dvf\Plugin\VisualisationInterface;
use Drupal\dvf\Plugin\Visualisation\Source\VisualisationSourceBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'dvf_ckan_resource' visualisation source.
 *
 * @VisualisationSource(
 *   id = "dvf_ckan_resource",
 *   label = @Translation("CKAN resource"),
 *   category = @Translation("CKAN"),
 *   visualisation_types = {
 *     "dvf_file",
 *     "dvf_url"
 *   }
 * )
 */
class CkanResource extends VisualisationSourceBase implements ContainerFactoryPluginInterface {

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * The CKAN client.
   *
   * @var \Drupal\ckan_connect\Client\CkanClientInterface
   */
  protected $ckanClient;

  /**
   * The CKAN resource URL parser.
   *
   * @var \Drupal\ckan_connect\Parser\CkanResourceUrlParserInterface
   */
  protected $ckanResourceUrlParser;

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
   * Constructs a new CkanResource.
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
   * @param \Drupal\ckan_connect\Client\CkanClientInterface $ckan_client
   *   The CKAN client.
   * @param \Drupal\ckan_connect\Parser\CkanResourceUrlParserInterface $ckan_resource_url_parser
   *   The CKAN resource URL parser.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, VisualisationInterface $visualisation = NULL, ModuleHandlerInterface $module_handler, CacheBackendInterface $cache, CkanClientInterface $ckan_client, CkanResourceUrlParserInterface $ckan_resource_url_parser) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $visualisation, $module_handler);
    $this->cache = $cache;
    $this->ckanClient = $ckan_client;
    $this->ckanResourceUrlParser = $ckan_resource_url_parser;
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
      $container->get('cache.dvf_ckan'),
      $container->get('ckan_connect.client'),
      $container->get('ckan_connect.resource_url_parser')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFields() {
    $cache_key = $this->getCacheKey('fields');
    $cache_object = $this->cache->get($cache_key);

    if (is_object($cache_object)) {
      $raw_fields = $cache_object->data;
    }
    else {
      $raw_fields = $this->fetchFields();
      $this->cache->set($cache_key, $raw_fields);
    }

    $fields = [];

    foreach ($raw_fields as $field) {
      $fields[$field->id] = $field->id;
    }

    return $fields;
  }

  /**
   * Fetches the fields.
   *
   * @return array
   *   An array of CKAN resource fields.
   */
  protected function fetchFields() {
    $query = [
      'id' => $this->getResourceId(),
      'limit' => 1,
    ];

    try {
      $response = $this->ckanClient->get('action/datastore_search', $query);
      $result = ($response->success === TRUE) ? $response->result : NULL;
    }
    catch (\Exception $e) {
      $result = NULL;
    }

    return ($result) ? $result->fields : [];
  }

  /**
   * {@inheritdoc}
   */
  public function getRecords() {
    $cache_key = $this->getCacheKey('records');
    $cache_object = $this->cache->get($cache_key);

    if (is_object($cache_object)) {
      $raw_records = $cache_object->data;
    }
    else {
      $raw_records = $this->fetchRecords();
      $this->cache->set($cache_key, $raw_records);
    }

    $records = [];

    foreach ($this->getFields() as $field_id => $field_label) {
      foreach ($raw_records as $record) {
        if (!isset($records[$record->_id])) {
          $records[$record->_id] = new \stdClass();
        }
        $records[$record->_id]->{$field_id} = $record->{$field_id};
      }
    }

    return $records;
  }

  /**
   * Fetches the records.
   *
   * @param array $records
   *   (optional) The records.
   * @param int $limit
   *   (optional) The maximum number of records per request.
   * @param int $offset
   *   (optional) The number of records to skip.
   *
   * @return array
   *   An array of records.
   */
  protected function fetchRecords(array $records = [], $limit = 100, $offset = 0) {
    $query = [
      'id' => $this->getResourceId(),
      'limit' => $limit,
      'offset' => $offset,
    ];

    try {
      $response = $this->ckanClient->get('action/datastore_search', $query);
      $result = ($response->success === TRUE) ? $response->result : NULL;
    }
    catch (\Exception $e) {
      $result = NULL;
    }

    $records = ($result) ? array_merge($records, $result->records) : [];
    $total = ($result) ? $result->total : 0;
    $offset = count($records);

    if ($total > $offset) {
      return $this->fetchRecords($records, $limit, $offset);
    }
    else {
      return $records;
    }
  }

  /**
   * Gets the CKAN resource ID for this plugin.
   *
   * @return string
   *   The CKAN resource ID.
   */
  protected function getResourceId() {
    return $this->ckanResourceUrlParser->getResourceId($this->config('uri'));
  }

  /**
   * Gets a cache key for a given type.
   *
   * @param string $object_type
   *   The object type.
   *
   * @return string
   *   The cache key.
   */
  protected function getCacheKey($object_type) {
    $plugin_id = hash('sha256', $this->getPluginId());
    $resource_id = $this->getResourceId();

    return $plugin_id . ':' . $resource_id . ':' . $object_type;
  }

}
