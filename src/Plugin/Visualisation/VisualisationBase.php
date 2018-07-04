<?php

namespace Drupal\dvf\Plugin\Visualisation;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\dvf\ConfigurablePluginTrait;
use Drupal\dvf\Plugin\VisualisationInterface;
use Drupal\dvf\Plugin\VisualisationSourceManagerInterface;
use Drupal\dvf\Plugin\VisualisationStyleManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a base class for Visualisation plugins.
 */
abstract class VisualisationBase extends PluginBase implements VisualisationInterface, ContainerFactoryPluginInterface {

  use ConfigurablePluginTrait;

  /**
   * The source configuration, with at least a 'plugin_id' key.
   *
   * Used to initialize the $sourcePlugin.
   *
   * @var array
   */
  protected $source;

  /**
   * The style configuration, with at least a 'plugin_id' key.
   *
   * Used to initialize the $stylePlugin.
   *
   * @var array
   */
  protected $style;

  /**
   * The source plugin.
   *
   * @var \Drupal\dvf\Plugin\VisualisationSourceInterface
   */
  protected $sourcePlugin;

  /**
   * The style plugin.
   *
   * @var \Drupal\dvf\Plugin\VisualisationStyleInterface
   */
  protected $stylePlugin;

  /**
   * The source plugin manager.
   *
   * @var \Drupal\dvf\Plugin\VisualisationSourceManagerInterface
   */
  protected $sourcePluginManager;

  /**
   * The style plugin manager.
   *
   * @var \Drupal\dvf\Plugin\VisualisationStyleManagerInterface
   */
  protected $stylePluginManager;

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  public $moduleHandler;

  /**
   * The entity this visualisation is attached to.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * Constructs a new VisualisationBase.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\dvf\Plugin\VisualisationSourceManagerInterface $source_plugin_manager
   *   The source plugin manager.
   * @param \Drupal\dvf\Plugin\VisualisationStyleManagerInterface $style_plugin_manager
   *   The style plugin manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Instance of the module handler.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    VisualisationSourceManagerInterface $source_plugin_manager,
    VisualisationStyleManagerInterface $style_plugin_manager,
    ModuleHandlerInterface $module_handler
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->sourcePluginManager = $source_plugin_manager;
    $this->stylePluginManager = $style_plugin_manager;
    $this->source = $configuration['source'];
    $this->style = $configuration['style'];
    $this->moduleHandler = $module_handler;
    $this->entity = isset($configuration['entity']) ? $configuration['entity'] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.visualisation.source'),
      $container->get('plugin.manager.visualisation.style'),
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
   * {@inheritdoc}
   */
  public function getSourcePlugin() {
    if (!isset($this->sourcePlugin)) {
      $configuration = $this->getSourceConfiguration();

      // Let other modules alter the source configuration.
      $this->moduleHandler->alter('dvf_source_configuration', $configuration['options'], $this);

      $this->sourcePlugin = $this->sourcePluginManager->createInstance($configuration['plugin_id'], $configuration['options'], $this);
    }

    return $this->sourcePlugin;
  }

  /**
   * {@inheritdoc}
   */
  public function getStylePlugin() {
    if (!isset($this->stylePlugin)) {
      $configuration = $this->getStyleConfiguration();

      // Let other modules alter the style configuration.
      $this->moduleHandler->alter('dvf_style_configuration', $configuration['options'], $this);

      $this->stylePlugin = $this->stylePluginManager->createInstance($configuration['plugin_id'], $configuration['options'], $this);
    }

    return $this->stylePlugin;
  }

  /**
   * {@inheritdoc}
   */
  public function getSourceConfiguration() {
    return $this->source;
  }

  /**
   * {@inheritdoc}
   */
  public function getStyleConfiguration() {
    return $this->style;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntity() {
    return $this->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function data() {
    $data = $this->getSourcePlugin()->getRecords();

    // Let other modules alter the data.
    $this->moduleHandler->alter('dvf_visualisation_data', $data, $this);

    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = $this->getStylePlugin()->build();

    // Let other modules alter the render array.
    $this->moduleHandler->alter('dvf_visualisation_build', $build, $this);

    return $build;
  }

}
