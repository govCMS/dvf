<?php

namespace Drupal\dvf\Plugin\Visualisation\Source;

use Drupal\Core\Plugin\PluginBase;
use Drupal\dvf\Plugin\VisualisationInterface;
use Drupal\dvf\Plugin\VisualisationSourceInterface;

/**
 * Provides a base class for VisualisationSource plugins.
 */
abstract class VisualisationSourceBase extends PluginBase implements VisualisationSourceInterface {

  /**
   * The visualisation.
   *
   * @var \Drupal\dvf\Plugin\VisualisationInterface
   */
  protected $visualisation;

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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, VisualisationInterface $visualisation) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->visualisation = $visualisation;
  }

  /**
   * Initializes the iterator with the source data.
   *
   * @return \Traversable
   *   An array of data for this source.
   */
  abstract protected function initializeIterator();

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

}
