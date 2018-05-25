<?php

namespace Drupal\dvf\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'dvf_url_default' field formatter.
 *
 * @FieldFormatter(
 *   id = "dvf_url_default",
 *   label = @Translation("URL to visualisation"),
 *   field_types = {
 *     "dvf_url"
 *   }
 * )
 */
class VisualisationUrlFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'chart' => [
          'palette' => ''
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
      '#title' => t('Visualisation Palette'),
      '#type' => 'textfield',
      '#default_value' => $settings['chart']['palette'],
      '#description' => t('Palette is a comma separated list of hex values. If not set, default palette is applied. Not all visualisations will support this.'),
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $settings = $this->getSettings();
    $summary = [];
    $summary[] = t('Visualisation palette @palette', ['@palette' => $settings['chart']['palette']]);
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
        ->getVisualisationPlugin($this->getSettings())
        ->getStylePlugin()
        ->build();
    }
    return $element;
  }

}
