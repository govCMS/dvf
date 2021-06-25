<?php

namespace Drupal\dvf\Plugin\Visualisation\Style;

use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\dvf\ConfigurablePluginTrait;
use Drupal\dvf\DvfHelpers;
use Drupal\dvf\Plugin\VisualisationInterface;
use Drupal\dvf\Plugin\VisualisationStyleInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Render\FormattableMarkup;

/**
 * Provides a base class for VisualisationStyle plugins.
 */
abstract class VisualisationStyleBase extends PluginBase implements VisualisationStyleInterface, ContainerFactoryPluginInterface {

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
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * DVF Helpers.
   *
   * @var \Drupal\dvf\DvfHelpers
   */
  protected $dvfHelpers;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a new VisualisationStyleBase.
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
   * @param \Drupal\dvf\DvfHelpers $dvf_helpers
   *   The DVF helpers.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The Messenger service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    VisualisationInterface $visualisation = NULL,
    ModuleHandlerInterface $module_handler,
    LoggerInterface $logger,
    DvfHelpers $dvf_helpers,
    MessengerInterface $messenger
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->visualisation = $visualisation;
    $this->moduleHandler = $module_handler;
    $this->logger = $logger;
    $this->dvfHelpers = $dvf_helpers;
    $this->messenger = $messenger;
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
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition,
    VisualisationInterface $visualisation = NULL
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $visualisation,
      $container->get('module_handler'),
      $container->get('logger.channel.dvf'),
      $container->get('dvf.helpers'),
      $container->get('messenger')
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
    return [
      'data' => [
        'fields' => [],
        'field_labels' => '',
        'split_field' => '',
        'cache_expiry' => '',
        'column_overrides' => [],
        'data_filters' => [],
      ],
    ];
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
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form['#after_build'][] = [get_called_class(), 'afterBuildSettingsForm'];
    $form['#attached']['library'][] = 'dvf/dvfAdmin';

    $form['data'] = [
      '#type' => 'details',
      '#title' => $this->t('Data settings'),
      '#tree' => TRUE,
      '#open' => TRUE,
    ];

    $form['data']['fields'] = [
      '#type' => 'select',
      '#title' => $this->t('Fields'),
      '#description' => $this->t('What fields to include in the visualisation. Select at least one field to display its data. A field is typically a column in a CSV. @help',
        ['@help' => $this->dvfHelpers->getHelpPageLink('keys')]),
      '#options' => $this->getSourceFieldOptions(),
      '#multiple' => TRUE,
      '#size' => 5,
      '#default_value' => $this->config('data', 'fields'),
      '#required' => TRUE,
    ];

    $form['data']['field_labels'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Field label overrides'),
      '#description' => $this->t('Optionally override one or more field labels. Add one original_label|new_label per line and separate with a pipe. @help',
        ['@help' => $this->dvfHelpers->getHelpPageLink('label-overrides')]),
      '#rows' => 2,
      '#default_value' => $this->config('data', 'field_labels'),
      '#placeholder' => 'Old label|New label',
    ];

    $form['data']['split_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Split field'),
      '#description' => $this->t('Optionally split into multiple visualisations based on the value of this field. A new visualisation will be made for each unique value in this field. @help',
        ['@help' => $this->dvfHelpers->getHelpPageLink('split')]),
      '#options' => $this->getSourceFieldOptions(),
      '#empty_option' => $this->t('- None -'),
      '#empty_value' => '',
      '#default_value' => $this->config('data', 'split_field'),
    ];

    $form['data']['cache_expiry'] = [
      '#type' => 'select',
      '#title' => $this->t('Cache expiry'),
      '#description' => $this->t('How long the results for this dataset will be cached.'),
      '#options' => $this->getCacheOptions(),
      '#default_value' => $this->config('data', 'cache_expiry'),
    ];

    $column_override_examples = [
      'type|line', 'color|#000000', 'legend|hide', 'style|dashed', 'weight|20', 'class|hide-points', 'label|New label',
    ];
    $form['data']['column_overrides'] = [
      '#prefix' => '<div id="column-overrides">',
      '#suffix' => '</div>',
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#type' => 'details',
      '#title' => t('Column/Group overrides'),
      '#description' => '<p>' . t('Optionally override a style for a specific column, add one key|value per line and separate key value with a pipe. @help.<br />Examples: <strong>@examples</strong>.',
        [
          '@examples' => new FormattableMarkup(implode('</strong> or <strong>', $column_override_examples), []),
          '@help' => $this->dvfHelpers->getHelpPageLink('column-overrides'),
        ]) . '</p>',
    ];

    foreach ($this->getColumnOverrideValues() as $override) {
      $form['data']['column_overrides'][$override] = [
        '#type' => 'textarea',
        '#rows' => 2,
        '#title' => substr($override, 1),
        '#default_value' => $this->config('data', 'column_overrides', $override),
      ];
    }

    $form['data']['data_filters'] = [
      '#prefix' => '<div>',
      '#suffix' => '</div>',
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#access' => ($this->getVisualisation()->getSourcePlugin()->getPluginId() === 'dvf_ckan_resource'),
      '#type' => 'details',
      '#title' => t('CKAN data filters'),
      '#description' => t('Filters can be used to refine/reduce the records returned from the CKAN datasource. @help',
        ['@help' => $this->dvfHelpers->getHelpPageLink('data-filters')]),
    ];

    $form['data']['data_filters']['q'] = [
      '#type' => 'textfield',
      '#title' => t('Full text query'),
      '#description' => t('Optionally query entire dataset for any string value.'),
      '#default_value' => $this->config('data', 'data_filters', 'q'),
    ];

    $form['data']['data_filters']['filters'] = [
      '#type' => 'textfield',
      '#title' => t('Filters'),
      '#description' => t('Filter on key/value dictionary. For example: {"code": "4000", "year": "2016"} or {"year": ["2014", "2015", "2015"]}. Case sensitive.'),
      '#default_value' => $this->config('data', 'data_filters', 'filters'),
    ];

    return $form;
  }

  /**
   * Gets the options array for caching.
   *
   * @return array
   *   The options array.
   */
  protected function getCacheOptions() {

    return [
      '_global_default' => 'Global default',
      '0' => 'No cache',
      '1800' => '30 minutes',
      '3600' => '1 hour',
      '21600' => '6 hours',
      '86400' => '1 day',
      '604800' => '1 week',
      '2592000' => '1 month',
      '15552000' => '6 months',
    ];
  }

  /**
   * Settings form #after_build callback.
   *
   * @param array $element
   *   The form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The updated form element.
   */
  public static function afterBuildSettingsForm(array $element, FormStateInterface $form_state) {
    return $element;
  }

  /**
   * Gets the list of source field options.
   *
   * @return array
   *   The source field options.
   */
  protected function getSourceFieldOptions() {
    $fields = $this->visualisation->getSourcePlugin()->getFields();
    $options = array_map('\Drupal\Component\Utility\Html::escape', $fields);

    return !empty($options) ? $options : [];
  }

  /**
   * Gets the source field values.
   *
   * @param string $field_id
   *   The field ID.
   * @param array $records
   *   The set of records that we should get the values from.
   *
   * @return array
   *   The source field values.
   */
  protected function getSourceFieldValues($field_id, $records = []) {
      $values = [];
      foreach ($records as $record) {
        if (property_exists($record, $field_id)) {
          $values[] = $record->{$field_id};
        }
      }

    return $values;
  }

  /**
   * Gets the source records.
   *
   * @return array
   *   An array of source records.
   */
  public function getSourceRecords() {
    $records = [];

    foreach ($this->getVisualisation()->data() as $record) {
      if ($this->splitField() && property_exists($record, $this->splitField())) {
        $records[$record->{$this->splitField()}][] = $record;
      }
      else {
        $records['all'][] = $record;
      }
    }

    return $records;
  }

  /**
   * Gets the fields.
   *
   * @return array
   *   The fields.
   */
  protected function fields() {
    return array_unique(array_filter($this->config('data', 'fields')));
  }

  /**
   * Gets the field labels.
   *
   * @return array
   *   The field labels.
   */
  protected function fieldLabels() {
    $labels = array_intersect(array_values($this->getSourceFieldOptions()), $this->fields());
    $labels = array_combine($labels, $labels);

    $label_overrides = $this->dvfHelpers->configStringToArray($this->config('data', 'field_labels'));
    foreach ($label_overrides as $key => $val) {
      if (isset($labels[$key])) {
        $labels[$key] = strval($val);
      }
    }

    return $labels;
  }

  /**
   * Gets the original field labels without any overrides applied.
   *
   * @return array
   *   The unique field labels.
   */
  protected function fieldLabelsOriginal() {
    $labels = $this->getSourceFieldOptions();

    if (!empty($labels['_id'])) {
      unset($labels['_id']);
    }

    $keys = array_keys($labels);
    return array_map('strval', $keys);
  }

  /**
   * Gets a field label.
   *
   * @param string $field_id
   *   The field ID.
   *
   * @return string
   *   The field label.
   */
  protected function fieldLabel($field_id) {
    $label = '';
    $labels = $this->fieldLabels();

    if (array_key_exists($field_id, $labels)) {
      $label = $labels[$field_id];
    }

    return $label;
  }

  /**
   * Gets the split field.
   *
   * @return string
   *   The split field.
   */
  protected function splitField() {
    return $this->config('data', 'split_field');
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
  public function getDatasetDownloadUri() {
    try {
      return $this->getVisualisation()->getSourcePlugin()->getDownloadUrl();
    } catch (\Exception $e) {
      $this->logger->error($this->t('Unable to get download url for visualisation :message',
        [':message' => $e->getMessage()]));
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function isValidDownloadUri($uri) {
    return (UrlHelper::isValid($uri) || filter_var($uri, FILTER_VALIDATE_URL)) ? $uri : FALSE;
  }

  /**
   * Returns the column override values to make a form array.
   *
   * @return array
   *   The array of column override values. NOTE: Each value has a underscore
   *   prependend to ensure it is a string. Without this the field name gets
   *   converted to a int/float during form submission.
   */
  protected function getColumnOverrideValues() {

    if ($this->config('axis', 'x', 'x_axis_grouping') === 'values' && $this->config('axis', 'x', 'tick', 'values', 'field')) {
      $x_tick_field = $this->config('axis', 'x', 'tick', 'values', 'field');
      $columns = array_map(function ($e) use ($x_tick_field) {
        return $e->{$x_tick_field};
      }, $this->getVisualisation()->data());
    }
    else {
      $columns = $this->fieldLabelsOriginal();
    }

    return array_map(function($item) {
      return '_' . $item;
    }, $columns);
  }

  /**
   * Gets the column overrides settings in a nicely formatted array.
   *
   * @return array
   *   An array of column override settings.
   */
  protected function getColumnOverrides() {
    $columns = array_map(function($item) {
      return substr($item, 1);
    }, $this->getColumnOverrideValues());

    $column_overrides = array_fill_keys($columns, []);

    foreach ($this->config('data', 'column_overrides') as $field_name => $column_override) {
      if (empty($column_override)) {
        continue;
      }

      $real_field_name = substr($field_name, 1);
      $column_overrides[$real_field_name] = $this->dvfHelpers->configStringToArray($column_override);
    }

    return $this->setArrayOrder($column_overrides);
  }

  /**
   * Re-orders the keys as per provided order array.
   *
   * @param array $array_to_order
   *   An array keyed by the key (original) name, the value for each should be
   *   an array containing a weight key. The lower the weight the higher it
   *   appears in the list. If no weight found, default order is used.
   * @param string $weight_key
   *   The key that contains the weight.
   *
   * @return array
   *   An ordered array.
   */
  protected function setArrayOrder(array $array_to_order, $weight_key = 'weight') {
    $i = 0;

    // Set default weights if does not exist.
    foreach ($array_to_order as $key => $value) {
      $array_to_order[$key][$weight_key] = isset($value[$weight_key]) ? (int) $value[$weight_key] : $i;
      $array_to_order[$key]['key'] = strval($key);
      $i++;
    }

    // Sort by weight and return.
    uasort($array_to_order, function ($a, $b) use ($weight_key) { return $a[$weight_key] - $b[$weight_key]; });
    return $array_to_order;
  }

  /**
   * Get fields that will be displayed ordered correctly by weight.
   *
   * @return array
   *   Array of field values.
   */
  public function fieldsSorted() {
    return array_map('strval', array_keys($this->getColumnOverrides()));
  }

  /**
   * Checks to see if columns are numeric.
   *
   * @param array $array
   *   The array of column values.
   *
   * @return bool
   *   True if numeric, false if not.
   */
  public function columnsAreNumeric(array $array) {
    $array = reset($array);
    array_shift($array);

    if (count($array) === count(array_filter($array, 'is_numeric'))) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Return markup for a split field heading.
   *
   * @param string $label
   *   Label for the heading.
   *
   * @return array
   *   A heading tag for the label, if label is "all" (ungrouped), return empty.
   */
  public function buildSplitHeading($label) {
    if ('all' === $label) {
      return [];
    }

    return [
      '#type' => 'html_tag',
      '#tag' => 'h3',
      '#value' => htmlentities($label),
      '#attributes' => ['class' => 'dvf-split-heading'],
    ];
  }

}
