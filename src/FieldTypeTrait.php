<?php

namespace Drupal\dvf;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides common methods for field types.
 */
trait FieldTypeTrait {

  /**
   * Returns a form base for the field-level settings.
   *
   * @param array $form
   *   The form where the settings form is being included in.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state of the (entire) configuration form.
   *
   * @return array
   *   The form definition for the field settings.
   */
  public function fieldSettingsFormBase(array $form, FormStateInterface $form_state) {
    $element = [];

    $element['visualisation_source'] = [
      '#type' => 'select',
      '#title' => $this->t('Visualisation source'),
      '#description' => $this->t('Specify the visualisation source allowed in this field.'),
      '#options' => $this->getVisualisationSourceOptions(),
      '#empty_option' => '- Select -',
      '#empty_value' => '',
      '#default_value' => $this->getSetting('visualisation_source'),
      '#required' => TRUE,
      '#ajax' => [
        'callback' => [$this, 'updateVisualisationSourceOptions'],
        'wrapper' => 'dvf-source-options',
      ],
    ];

    $element['visualisation_source_options'] = [
      '#type' => 'container',
      '#attributes' => ['id' => $element['visualisation_source']['#ajax']['wrapper']],
      '#tree' => TRUE,
    ];

    $source = [
      'plugin_id' => $this->getSetting('visualisation_source'),
      'options' => $this->getSetting('visualisation_source_options'),
    ];

    $values = $form_state->getValues();

    if (!empty($values['settings']['visualisation_source'])) {
      $source['plugin_id'] = $values['settings']['visualisation_source'];
    }

    if (!empty($values['settings']['visualisation_source_options'])) {
      $source['options'] = $values['settings']['visualisation_source_options'];
    }

    if (!empty($source['plugin_id'])) {
      $element['visualisation_source_options'] += $this
        ->getVisualisationPlugin($source, [])
        ->getSourcePlugin()
        ->settingsForm($element['visualisation_source_options'], $form_state);
    }

    return $element;
  }

  /**
   * Gets the visualisation plugin.
   *
   * @param array $default_source
   *   The default visualisation source configuration.
   * @param array $default_style
   *   The default visualisation style configuration.
   *
   * @return \Drupal\dvf\Plugin\VisualisationInterface
   *   An instance of the visualisation plugin.
   */
  public function getVisualisationPlugin(array $default_source = [], array $default_style = []) {
    $item = $this->getValue();

    /** @var \Drupal\dvf\Plugin\VisualisationManagerInterface $plugin_manager */
    $plugin_manager = \Drupal::service('plugin.manager.visualisation');

    $plugin_id = $this->getFieldDefinition()->getType();
    $plugin_configuration = [
      'options' => $item,
      'source' => [
        'plugin_id' => $this->getFieldDefinition()->getSetting('visualisation_source'),
        'options' => $this->getFieldDefinition()->getSetting('visualisation_source_options'),
      ],
      'style' => [
        'plugin_id' => '',
        'options' => [],
      ],
      'entity' => $this->getEntity(),
    ];

    if (!empty($item['options']['visualisation_style'])) {
      $plugin_configuration['style']['plugin_id'] = $item['options']['visualisation_style'];
    }

    if (!empty($item['options']['visualisation_style_options'])) {
      $plugin_configuration['style']['options'] = $item['options']['visualisation_style_options'];
    }

    if ($default_source) {
      $plugin_configuration['source'] = NestedArray::mergeDeep($plugin_configuration['source'], $default_source);
    }

    if ($default_style) {
      $plugin_configuration['style'] = NestedArray::mergeDeep($plugin_configuration['style'], $default_style);
    }

    /** @var \Drupal\dvf\Plugin\VisualisationInterface $plugin */
    $plugin = $plugin_manager->createInstance($plugin_id, $plugin_configuration);

    return $plugin;
  }

  /**
   * Returns a list of visualisation source plugin options.
   *
   * @return array
   *   An array of visualisation source plugin options. Keyed by plugin_id and
   *   value being the plugin label.
   */
  protected function getVisualisationSourceOptions() {
    $plugin_options = [];
    /** @var \Drupal\dvf\Plugin\VisualisationSourceManagerInterface $plugin_manager */
    $plugin_manager = \Drupal::service('plugin.manager.visualisation.source');
    $plugin_definitions = $plugin_manager->getDefinitionsByType($this->getFieldDefinition()->getType());

    foreach ($plugin_definitions as $plugin_id => $plugin) {
      $plugin_options[(string) $plugin['category']][$plugin_id] = Html::escape($plugin['label']);
    }

    return $plugin_options;
  }

  /**
   * Updates the visualisation source options.
   *
   * @param array $form
   *   The form structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The updated form element.
   */
  public function updateVisualisationSourceOptions(array $form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $elements = NestedArray::getValue($form, array_slice($triggering_element['#array_parents'], 0, -1));

    return $elements['visualisation_source_options'];
  }

}
