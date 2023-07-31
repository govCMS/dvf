<?php

namespace Drupal\dvf_ckan\Plugin\Visualisation\Source;

use Drupal\ckan_connect\Client\CkanClientInterface;
use Drupal\ckan_connect\Parser\CkanResourceUrlParserInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\dvf\DvfHelpers;
use Drupal\dvf\Plugin\VisualisationInterface;
use Drupal\dvf\Plugin\Visualisation\Source\VisualisationSourceBase;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
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
   * DVF Helpers.
   *
   * @var \Drupal\dvf\DvfHelpers
   */
  protected $dvfHelpers;

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
   * @param \Psr\Log\LoggerInterface $logger
   *   Instance of the logger object.
   * @param \GuzzleHttp\Client $http_client
   *   The HTTP client.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend.
   * @param \Drupal\ckan_connect\Client\CkanClientInterface $ckan_client
   *   The CKAN client.
   * @param \Drupal\ckan_connect\Parser\CkanResourceUrlParserInterface $ckan_resource_url_parser
   *   The CKAN resource URL parser.
   * @param \Drupal\dvf\DvfHelpers $dvf_helpers
   *   The DVF helpers.
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
    CacheBackendInterface $cache,
    CkanClientInterface $ckan_client,
    CkanResourceUrlParserInterface $ckan_resource_url_parser,
    DvfHelpers $dvf_helpers,
    FileUrlGeneratorInterface $file_url_generator
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $visualisation, $module_handler, $logger, $http_client, $file_url_generator);
    $this->cache = $cache;
    $this->ckanClient = $ckan_client;
    $this->ckanResourceUrlParser = $ckan_resource_url_parser;
    $this->dvfHelpers = $dvf_helpers;
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
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $file_url_generator
   *   The file URL generator.
   *
   * @return static
   *   Returns an instance of this plugin.
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition,
    VisualisationInterface $visualisation = NULL,
    FileUrlGeneratorInterface $file_url_generator = NULL
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $visualisation,
      $container->get('module_handler'),
      $container->get('logger.channel.dvf'),
      $container->get('http_client'),
      $container->get('cache.dvf_ckan'),
      $container->get('ckan_connect.client'),
      $container->get('ckan_connect.resource_url_parser'),
      $container->get('dvf.helpers'),
      $container->get('file_url_generator')
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
      $this->cache->set($cache_key, $raw_fields, $this->getCacheExpiry());
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

    $query = array_merge($query, $this->getDataFilters());

    try {
      $response = $this->ckanClient->get('action/datastore_search', $query);
      $result = ($response->success === TRUE) ? $response->result : NULL;
    }
    catch (\Exception $e) {
      $this->ckanResourceError('fields', $query['id'], $e->getMessage());
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
      $this->cache->set($cache_key, $raw_records, $this->getCacheExpiry());
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

    $query = array_merge($query, $this->getDataFilters());

    try {
      $response = $this->ckanClient->get('action/datastore_search', $query);
      $result = ($response->success === TRUE) ? $response->result : NULL;
    }
    catch (\Exception $e) {
      $this->ckanResourceError('records', $query['id'], $e->getMessage());
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

  /**
   * Gets the optional data filters and returns them in the correct format.
   *
   * @return array
   *   An array of data filters.
   */
  protected function getDataFilters() {
    $configuration_options = $this->visualisation->getConfiguration('style');
    $data_filters = [];

    if (empty($configuration_options['style']['options']['data']['data_filters'])) {
      return $data_filters;
    }

    $data_filters = $configuration_options['style']['options']['data']['data_filters'];

    if (empty($data_filters['filters']) || !$this->dvfHelpers->validateJson($data_filters['filters'])) {
      unset($data_filters['filters']);
    }

    if (empty($data_filters['q'])) {
      unset($data_filters['q']);
    }

    return array_map('trim', $data_filters);
  }

  /**
   * Generic error message for errors accessing resources.
   *
   * @param string $type
   *   Error type, eg "records" or "fields".
   * @param string $resource_id
   *   The resource ID that we attempted to access.
   * @param string $exception_message
   *   The exception message.
   */
  protected function ckanResourceError($type, $resource_id, $exception_message) {
    $this->messenger()->addError($this->t('Error displaying CKAN data'));
    $message = $this->t(
      'There was an error getting CKAN :type data for :id. Error :message',
      [
        ':type' => $type,
        ':id' => $resource_id,
        ':message' => $exception_message,
      ]
    );
    $this->logger->error($message);
  }

}
