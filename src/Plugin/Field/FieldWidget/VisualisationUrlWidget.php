<?php

namespace Drupal\dvf\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\dvf\FieldWidgetTrait;

/**
 * Plugin implementation of the 'dvf_url_default' field widget.
 *
 * @FieldWidget(
 *   id = "dvf_url_default",
 *   label = @Translation("Visualisation URL"),
 *   field_types = {
 *     "dvf_url"
 *   }
 * )
 */
class VisualisationUrlWidget extends WidgetBase {

  use FieldWidgetTrait;

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['#type'] = 'fieldset';
    $element['#tree'] = TRUE;

    $element['uri'] = [
      '#type' => 'url',
      '#title' => $this->t('URL'),
      '#description' => $this->t('External url to dataset.'),
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
        'wrapper' => $this->getAjaxWrapperId($form, $this->fieldDefinition->getName(), $delta),
      ],
    ];

    $element['options']['visualisation_style_options'] = [
      '#type' => 'container',
      '#attributes' => ['id' => $element['options']['visualisation_style']['#ajax']['wrapper']],
    ];

    $style_id = $this->getElementOptions($items, $delta, $form, $form_state, 'visualisation_style');

    if ($style_id) {
      $element['options']['visualisation_style_options'] += $this
        ->getVisualisationPlugin($items, $delta, $form, $form_state)
        ->getStylePlugin()
        ->settingsForm($element['options']['visualisation_style_options'], $form_state);
    }

    return $element;
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
    $values = $this->getFieldValue($items, $delta, $form, $form_state);

    /** @var \Drupal\dvf\Plugin\VisualisationManagerInterface $plugin_manager */
    $plugin_manager = \Drupal::service('plugin.manager.visualisation');

    $plugin_id = $this->fieldDefinition->getType();
    $plugin_configuration = [
      'options' => [
        'uri' => '',
      ],
      'source' => [
        'plugin_id' => $this->fieldDefinition->getSetting('visualisation_source'),
        'options' => $this->fieldDefinition->getSetting('visualisation_source_options'),
      ],
      'style' => [
        'plugin_id' => $this->getElementOptions($items, $delta, $form, $form_state, 'visualisation_style'),
        'options' => [],
      ],
      'entity' => $items->getEntity(),
    ];

    if (!empty($values[$delta]['uri'])) {
      $plugin_configuration['options']['uri'] = $values[$delta]['uri'];
    }

    if (!empty($values[$delta]['options']['visualisation_style_options'])) {
      $plugin_configuration['style']['options'] = $values[$delta]['options']['visualisation_style_options'];
    }

    /** @var \Drupal\dvf\Plugin\VisualisationInterface $plugin */
    $plugin = $plugin_manager->createInstance($plugin_id, $plugin_configuration);

    return $plugin;
  }

}
