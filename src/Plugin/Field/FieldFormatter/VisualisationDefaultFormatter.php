<?php

namespace Drupal\dvf\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\dvf\DvfHelpers;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'dvf_default' field formatter.
 *
 * @FieldFormatter(
 *   id = "dvf_default",
 *   label = @Translation("Visualisation"),
 *   field_types = {
 *     "dvf_url",
 *     "dvf_file"
 *   }
 * )
 */
class VisualisationDefaultFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * DVF Helpers.
   *
   * @var \Drupal\dvf\DvfHelpers
   */
  protected $dvfHelpers;

  /**
   * Constructs a FormatterBase object.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings settings.
   * @param \Drupal\dvf\DvfHelpers $dvf_helpers
   *   The DVF helpers.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, DvfHelpers $dvf_helpers) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

    $this->dvfHelpers = $dvf_helpers;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('dvf.helpers')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'chart' => [
        'palette' => '',
        'styles' => [
          'width' => '',
          'height' => '',
        ],
      ],
      'axis' => [
        'x' => [
          'styles' => [
            'label' => ['position' => ''],
            'tick' => ['centered' => TRUE],
          ],
        ],
        'y' => [
          'styles' => ['label' => ['position' => '']],
        ],
      ],
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $settings = $this->getSettings();

    $element['chart'] = [
      '#type' => 'container',
      '#tree' => TRUE,
    ];

    foreach (['width', 'height'] as $measure) {
      $element['chart']['styles'][$measure] = [
        '#title' => ucfirst($measure),
        '#type' => 'textfield',
        '#default_value' => $settings['chart']['styles'][$measure],
        '#description' => $this->t('A pixel value (without unit). Leave blank for auto sizing.'),
      ];
    }

    $element['chart']['palette'] = [
      '#title' => $this->t('Visualisation Palette'),
      '#type' => 'textfield',
      '#default_value' => $settings['chart']['palette'],
      '#description' => $this->t('Palette is a comma separated list of hex values. If not set, default palette is applied. Not all visualisations will support this.'),
    ];

    $element['axis']['x']['styles']['label']['position'] = [
      '#type' => 'select',
      '#title' => $this->t('X axis title position'),
      '#description' => $this->t('Define the title position on the X axis.'),
      '#options' => [
        'inner-right' => $this->t('Inner right'),
        'inner-center' => $this->t('Inner center'),
        'inner-left' => $this->t('Inner left'),
        'outer-right' => $this->t('Outer right'),
        'outer-center' => $this->t('Outer center'),
        'outer-left' => $this->t('Outer left'),
      ],
      '#default_value' => $settings['axis']['x']['styles']['label']['position'],
      '#empty_option' => $this->t('- Select -'),
      '#empty_value' => '',
    ];

    $element['axis']['x']['styles']['tick']['centered'] = [
      '#title' => $this->t('Tick label centered'),
      '#description' => $this->t('Check to display tick directly above the label on the X axis.'),
      '#type' => 'checkbox',
      '#default_value' => $settings['axis']['x']['styles']['tick']['centered'],
    ];

    $element['axis']['y']['styles']['label']['position'] = [
      '#type' => 'select',
      '#title' => $this->t('Y axis title position'),
      '#description' => $this->t('Define the title position on the Y axis.'),
      '#options' => [
        'inner-top' => $this->t('Inner top'),
        'inner-middle' => $this->t('Inner middle'),
        'inner-bottom' => $this->t('Inner bottom'),
        'outer-top' => $this->t('Outer top'),
        'outer-middle' => $this->t('Outer middle'),
        'outer-bottom' => $this->t('Outer bottom'),
      ],
      '#default_value' => $settings['axis']['y']['styles']['label']['position'],
      '#empty_option' => $this->t('- Select -'),
      '#empty_value' => '',
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $settings = $this->getSettings();
    $summary = [];

    $summary[] = $this->t('Visualisation palette @palette', ['@palette' => $settings['chart']['palette']]);
    $summary[] = $this->t('Visualisation width @width', ['@width' => $settings['chart']['styles']['width']]);
    $summary[] = $this->t('Visualisation width @height', ['@height' => $settings['chart']['styles']['height']]);

    $summary[] = $this->t('X axis title position @x', ['@x' => $settings['axis']['x']['styles']['label']['position']]);
    $summary[] = $this->t('X Tick label centered @label', ['@label' => $settings['axis']['x']['styles']['tick']['centered'] ? 'True' : 'False']);
    $summary[] = $this->t('Y axis title position @y', ['@y' => $settings['axis']['y']['styles']['label']['position']]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    $formatter_settings = $this->getSettings();

    /** @var \Drupal\dvf\Plugin\VisualisationItemInterface $item */
    foreach ($items as $delta => $item) {
      $visualisation_style_options = $item->getVisualisationPlugin()->getStyleConfiguration();

      if (!empty($visualisation_style_options['options']) && !empty($formatter_settings)) {
        $formatter_settings = array_replace_recursive(
          $this->dvfHelpers->filterArrayRecursive($formatter_settings),
          $this->dvfHelpers->filterArrayRecursive($visualisation_style_options['options'])
        );
      }

      $element[$delta] = $item
        ->getVisualisationPlugin([], ['options' => $formatter_settings])
        ->render();
    }

    return $element;
  }

}
