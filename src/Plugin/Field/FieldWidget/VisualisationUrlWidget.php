<?php

namespace Drupal\dvf\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'dvf_url_default' field widget.
 *
 * @FieldWidget(
 *   id = "dvf_url_default",
 *   label = @Translation("Visualisation"),
 *   field_types = {
 *     "dvf_url"
 *   }
 * )
 */
class VisualisationUrlWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['#type'] = 'fieldset';
    $element['#tree'] = TRUE;

    $element['uri'] = [
      '#type' => 'url',
      '#title' => $this->t('URL'),
      '#description' => $this->t('This must be an external URL such as %url.', ['%url' => 'http://example.com']),
      '#default_value' => isset($items[$delta]->uri) ? $items[$delta]->uri : NULL,
      '#maxlength' => 2048,
      '#required' => $element['#required'],
    ];

    $element['options'] = [
      '#type' => 'details',
      '#title' => $this->t('Settings'),
      '#description' => $this->t('Visualisation settings for this field.'),
      '#tree' => TRUE,
    ];

    $element['options']['visualisation_style'] = [
      '#type' => 'select',
      '#title' => $this->t('Visualisation style'),
      '#description' => $this->t('How the data will be presented when rendered on the page.'),
      '#options' => $this->getVisualisationStyleOptions(),
      '#empty_option' => $this->t('- Select -'),
      '#empty_value' => '',
      '#default_value' => isset($items[$delta]->options['visualisation_style']) ? $items[$delta]->options['visualisation_style'] : '',
      '#required' => $element['#required'],
      '#ajax' => [
        'callback' => [$this, 'updateVisualisationOptions'],
        'wrapper' => Html::cleanCssIdentifier('dvf-visualisation-options-' . $this->fieldDefinition->getName() . '-' . $delta),
      ],
    ];

    $element['options']['visualisation_options'] = [
      '#type' => 'container',
      '#attributes' => ['id' => $element['options']['visualisation_style']['#ajax']['wrapper']],
    ];

    $style_id = $this->getElementOptions($items, $delta, $form, $form_state, 'visualisation_style');

    if ($style_id) {
      $element['options']['visualisation_options'] += $this
        ->getVisualisationPlugin($items, $delta, $form, $form_state)
        ->getStylePlugin()
        ->settingsForm($element['options']['visualisation_options'], $form_state);
    }

    return $element;
  }

  /**
   * Gets the options for a single field widget.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   Array of default values for this field.
   * @param int $delta
   *   The order of this item in the array of sub-elements (0, 1, 2, etc.).
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param string $key
   *   The key.
   * @param string $default
   *   The default value.
   *
   * @return mixed
   *   The option, $option_default otherwise.
   */
  protected function getElementOptions(FieldItemListInterface $items, $delta, array $form, FormStateInterface $form_state, $key, $default = NULL) {
    $field_name = $this->fieldDefinition->getName();
    $user_input = $form_state->getUserInput();

    if (isset($user_input[$field_name][$delta]['options'][$key])) {
      $option = $user_input[$field_name][$delta]['options'][$key];
    }
    elseif (isset($items[$delta]->options[$key])) {
      $option = $items[$delta]->options[$key];
    }
    else {
      $option = $default;
    }

    return $option;
  }

  /**
   * Updates the visualisation options.
   *
   * @param array $form
   *   The form structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The updated form element.
   */
  public function updateVisualisationOptions(array $form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $elements = NestedArray::getValue($form, array_slice($triggering_element['#array_parents'], 0, -1));

    return $elements['visualisation_options'];
  }

  /**
   * Returns a list of visualisation style plugin options.
   *
   * @return array
   *   An array of visualisation style plugin options.
   */
  protected function getVisualisationStyleOptions() {
    $options = [];

    /** @var \Drupal\dvf\Plugin\VisualisationStyleManagerInterface $plugin_manager */
    $plugin_manager = \Drupal::service('plugin.manager.visualisation.style');

    foreach ($plugin_manager->getDefinitions() as $plugin_id => $plugin) {
      $options[$plugin_id] = Html::escape($plugin['label']);
    }

    return $options;
  }

  /**
   * Gets the visualisation plugin.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   Array of default values for this field.
   * @param int $delta
   *   The order of this item in the array of sub-elements (0, 1, 2, etc.).
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Drupal\dvf\Plugin\VisualisationInterface
   *   The visualisation plugin.
   */
  protected function getVisualisationPlugin(FieldItemListInterface $items, $delta, array $form, FormStateInterface $form_state) {
    /** @var \Drupal\dvf\Plugin\VisualisationManagerInterface $plugin_manager */
    $plugin_manager = \Drupal::service('plugin.manager.visualisation');

    $plugin_id = $this->fieldDefinition->getType();
    $plugin_configuration = [
      'uri' => '',
      'options' => [],
      'source' => [
        'plugin_id' => $this->fieldDefinition->getSetting('source_type'),
      ],
      'style' => [
        'plugin_id' => $this->getElementOptions($items, $delta, $form, $form_state, 'visualisation_style'),
      ],
    ];

    if (!empty($items[$delta]->uri)) {
      $plugin_configuration['uri'] = $items[$delta]->uri;
    }

    if (!empty($items[$delta]->options['visualisation_options'])) {
      $plugin_configuration['options'] += $items[$delta]->options['visualisation_options'];
    }

    /** @var \Drupal\dvf\Plugin\VisualisationInterface $plugin */
    $plugin = $plugin_manager->createInstance($plugin_id, $plugin_configuration);

    return $plugin;
  }

}
