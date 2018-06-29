<?php

namespace Drupal\dvf\Plugin\Visualisation\Source;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\dvf\ConfigurablePluginTrait;
use Drupal\dvf\Plugin\VisualisationInterface;
use Drupal\dvf\Plugin\VisualisationSourceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a base class for VisualisationSource plugins.
 */
abstract class VisualisationSourceBase extends PluginBase implements VisualisationSourceInterface, ContainerFactoryPluginInterface {

  use ConfigurablePluginTrait;

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
   * The iterator.
   *
   * @var \Iterator
   */
  protected $iterator;

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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, VisualisationInterface $visualisation = NULL, ModuleHandlerInterface $module_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->visualisation = $visualisation;
    $this->moduleHandler = $module_handler;
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
      $container->get('module_handler')
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

}
