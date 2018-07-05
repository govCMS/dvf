<?php

namespace Drupal\dvf\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;

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
class VisualisationDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'chart' => [
        'palette' => '',
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

    $element['chart']['palette'] = [
      '#title' => $this->t('Visualisation Palette'),
      '#type' => 'textfield',
      '#default_value' => $settings['chart']['palette'],
      '#description' => $this->t('Palette is a comma separated list of hex values. If not set, default palette is applied. Not all visualisations will support this.'),
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

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    /** @var \Drupal\dvf\Plugin\VisualisationItemInterface $item */
    foreach ($items as $delta => $item) {
      $element[$delta] = $item
        ->getVisualisationPlugin([], ['options' => $this->getSettings()])
        ->render();
    }

    return $element;
  }

}
