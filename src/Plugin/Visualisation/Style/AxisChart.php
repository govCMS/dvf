<?php

namespace Drupal\dvf\Plugin\Visualisation\Style;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\dvf\FormElementAttributesTrait;

/**
 * Provides a base class for visualisation plugins with axis.
 */
abstract class AxisChart extends TableVisualisationStyleBase {

  use FormElementAttributesTrait;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'axis' => [
        'styles' => [
          'rotated' => FALSE,
        ],
        'x' => [
          'type' => '',
          'label' => [
            'text' => '',
          ],
          'tick' => [
            'count' => '',
            'culling' => '',
            'values' => [
              'field' => '',
              'custom' => '',
            ],
            'format' => [
              'indexed' => '',
              'timeseries' => [
                'input' => '',
                'output' => '',
              ],
            ],
          ],
          'styles' => [
            'padding' => [
              'left' => '',
              'right' => '',
            ],
            'label' => [
              'position' => '',
            ],
            'tick' => [
              'rotate' => '',
              'width' => '',
              'centered' => FALSE,
              'multiline' => TRUE,
            ],
          ],
        ],
        'y' => [
          'type' => '',
          'label' => [
            'text' => '',
          ],
          'tick' => [
            'count' => '',
            'values' => [
              'custom' => '',
            ],
            'rounding' => '',
            'format' => [
              'indexed' => '',
            ],
          ],
          'styles' => [
            'padding' => [
              'top' => '',
              'bottom' => '',
            ],
            'label' => [
              'position' => '',
            ],
          ],
        ],
      ],
      'grid' => [
        'x' => [
          'show' => FALSE,
        ],
        'y' => [
          'show' => FALSE,
        ],
        'lines' => [],
      ],
      'chart' => [
        'title' => [
          'show' => TRUE,
          'text' => '',
        ],
        'interaction' => TRUE,
        'table' => [],
        'data' => [
          'labels' => [
            'show' => FALSE,
          ],
          'legends' => [
            'interaction' => TRUE,
          ],
        ],
        'styles' => [
          'width' => '',
          'height' => '',
          'padding' => [
            'top' => '',
            'right' => '',
            'bottom' => '',
            'left' => '',
          ],
        ],
        'component' => 'table-chart',
      ],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['axis'] = [
      '#type' => 'details',
      '#title' => $this->t('Axis settings'),
      '#tree' => TRUE,
    ];

    $form['axis']['styles'] = [
      '#type' => 'details',
      '#title' => $this->t('Axis styles'),
      '#tree' => TRUE,
    ];

    $form['axis']['styles']['rotated'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Rotate orientation'),
      '#description' => $this->t('Check to switch the X and Y axis position.'),
      '#default_value' => $this->config('axis', 'styles', 'rotated'),
    ];

    $form['axis']['x'] = [
      '#type' => 'details',
      '#title' => $this->t('X axis settings'),
      '#tree' => TRUE,
    ];

    $form['axis']['x']['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Axis type'),
      '#description' => $this->t('Set the data type for the X axis values. E.g. If the X axis contains numbers, select "Indexed (numeric)".'),
      '#options' => [
        '' => $this->t('Automatic (Indexed if numeric, category if not)'),
        'indexed' => $this->t('Indexed (numeric)'),
        'category' => $this->t('Category (non-numeric)'),
        'timeseries' => $this->t('Date or time'),
      ],
      '#default_value' => $this->config('axis', 'x', 'type'),
    ];

    $form['axis']['x']['label']['text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Axis title'),
      '#description' => $this->t('Add a X axis title to describe what the data values relate to.'),
      '#default_value' => $this->config('axis', 'x', 'label', 'text'),
    ];

    $form['axis']['x']['tick']['count'] = [
      '#type' => 'number',
      '#title' => $this->t('Tick count'),
      '#description' => $this->t('Limit the number of ticks on the X axis.'),
      '#default_value' => $this->config('axis', 'x', 'tick', 'count'),
    ];

    $form['axis']['x']['tick']['culling'] = [
      '#type' => 'number',
      '#title' => $this->t('Tick culling'),
      '#description' => $this->t('Limit the number of ticks on the X axis by rounding to the nearest whole number.'),
      '#default_value' => $this->config('axis', 'x', 'tick', 'culling'),
    ];

    $form['axis']['x']['tick']['values']['field'] = [
      '#type' => 'select',
      '#title' => $this->t('Tick values field'),
      '#description' => $this->t('Override the individual X axis data-label values using data from a field.'),
      '#options' => $this->getSourceFieldOptions(),
      '#empty_option' => $this->t('- None -'),
      '#empty_value' => '',
      '#default_value' => $this->config('axis', 'x', 'tick', 'values', 'field'),
      '#ajax' => [
        'callback' => [$this, 'updateAxisGrouping'],
        'wrapper' => 'x-axis-grouping',
      ],
    ];

    $form['axis']['x']['tick']['values']['custom'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tick values'),
      '#description' => $this->t('Override the individual X axis data-label values manually. Separate values with a comma.'),
      '#default_value' => $this->config('axis', 'x', 'tick', 'values', 'custom'),
    ];

    $form['axis']['x']['x_axis_grouping'] = [
      '#prefix' => '<div id="x-axis-grouping">',
      '#suffix' => '</div>',
      '#type' => 'select',
      '#title' => $this->t('X Axis Grouping'),
      '#options' => [
        'keys' => $this->t('Group by keys'),
        'values' => $this->t('Group by label values'),
      ],
      '#default_value' => $this->config('axis', 'x', 'x_axis_grouping'),
      '#description' => t(
        'The X axis can use <em>keys</em> or the <em>label key value</em> as tick values. Changing this swaps what is displayed on the X axis (or table header if viewed as a table). <em>Tick values field</em> must be set for "labels" options to work correctly. @help.',
        ['@help' => $this->dvfHelpers->getHelpPageLink('x-axis-grouping')]),
      '#ajax' => [
        'callback' => [$this, 'updateColumnOverrides'],
        'wrapper' => 'column-overrides',
      ],
    ];

    if (!$this->config('axis', 'x', 'tick', 'values', 'field')) {
      $form['axis']['x']['x_axis_grouping']['#attributes']['disabled'] = 'disabled';
    }

    $form['axis']['x']['tick']['format']['indexed'] = [
      '#type' => 'select',
      '#title' => $this->t('Tick label format'),
      '#description' => $this->t('Format the X axis tick label.'),
      '#options' => [
        '' => $this->t('None'),
        ' ' => $this->t('Space separated Eg. 10 000'),
        ',' => $this->t('Comma separated Eg. 10,000'),
      ],
      '#default_value' => $this->config('axis', 'x', 'tick', 'format', 'indexed'),
    ];

    $timeseries_legend = '%d = day, %m = month, %Y = year, %H = hour, %M = min, %S = second';

    $form['axis']['x']['tick']['format']['timeseries']['input'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tick label input format'),
      '#description' => $this->t('How the date is formatted in the dataset. Replacements: %replacements', ['%replacements' => $timeseries_legend]),
      '#default_value' => $this->config('axis', 'x', 'tick', 'format', 'timeseries', 'input'),
    ];

    $form['axis']['x']['tick']['format']['timeseries']['output'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tick label output format'),
      '#description' => $this->t('How the date is formatted on the X axis. Replacements: %replacements', ['%replacements' => $timeseries_legend]),
      '#default_value' => $this->config('axis', 'x', 'tick', 'format', 'timeseries', 'output'),
    ];

    $form['axis']['x']['styles'] = [
      '#type' => 'details',
      '#title' => $this->t('X axis styles'),
      '#description' => t('Enter whole numbers (E.g. 320). Values are set as pixels unless otherwise indicated.<br/><br/>'),
      '#tree' => TRUE,
    ];

    foreach (['left', 'right'] as $edge) {
      $form['axis']['x']['styles']['padding'][$edge] = [
        '#type' => 'number',
        '#title' => $this->t('Axis padding @edge', ['@edge' => $edge]),
        '#description' => $this->t('Define the padding at the @edge end of the X axis.', ['@edge' => $edge]),
        '#default_value' => $this->config('axis', 'x', 'styles', 'padding', $edge),
      ];
    }

    $form['axis']['x']['styles']['label']['position'] = [
      '#type' => 'select',
      '#title' => $this->t('Axis title position'),
      '#description' => $this->t('Define the title position on the X axis.'),
      '#options' => [
        'inner-right' => $this->t('Inner right'),
        'inner-center' => $this->t('Inner center'),
        'inner-left' => $this->t('Inner left'),
        'outer-right' => $this->t('Outer right'),
        'outer-center' => $this->t('Outer center'),
        'outer-left' => $this->t('Outer left'),
      ],
      '#default_value' => $this->config('axis', 'x', 'styles', 'label', 'position'),
      '#empty_option' => $this->t('- Select -'),
      '#empty_option_value' => '',
    ];

    $form['axis']['x']['styles']['tick']['rotate'] = [
      '#type' => 'number',
      '#title' => $this->t('Tick label rotation'),
      '#description' => $this->t('Optionally rotate the X axis labels, by setting how many degrees the X axis tick label should be rotated here.'),
      '#default_value' => $this->config('axis', 'x', 'styles', 'tick', 'rotate'),
    ];

    $form['axis']['x']['styles']['tick']['width'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tick label width'),
      '#description' => $this->t('Define the width of the X axis tick labels.'),
      '#size' => 20,
      '#default_value' => $this->config('axis', 'x', 'styles', 'tick', 'width'),
    ];

    $form['axis']['x']['styles']['tick']['centered'] = [
      '#title' => $this->t('Tick label centered'),
      '#description' => $this->t('Check to display tick directly above the label on the X axis.'),
      '#type' => 'checkbox',
      '#default_value' => $this->config('axis', 'x', 'styles', 'tick', 'centered'),
    ];

    $form['axis']['x']['styles']['tick']['multiline'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Tick label multiline'),
      '#description' => $this->t('Check to allow the X axis label to be wrapped onto multiple lines.'),
      '#default_value' => $this->config('axis', 'x', 'styles', 'tick', 'multiline'),
    ];

    $form['axis']['y'] = [
      '#type' => 'details',
      '#title' => $this->t('Y axis settings'),
      '#tree' => TRUE,
    ];

    $form['axis']['y']['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Axis type'),
      '#description' => $this->t('Set the data type for the Y axis values. E.g. If the Y axis contains numbers, select "Indexed (numeric)".'),
      '#options' => [
        '' => $this->t('Automatic (Indexed if numeric, category if not)'),
        'indexed' => $this->t('Indexed (numeric)'),
        'category' => $this->t('Category (non-numeric)'),
        'timeseries' => $this->t('Date or time'),
      ],
      '#default_value' => $this->config('axis', 'y', 'type'),
    ];

    $form['axis']['y']['label']['text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Axis title'),
      '#description' => $this->t('Add a Y axis title to describe what the data values relate to.'),
      '#default_value' => $this->config('axis', 'y', 'label', 'text'),
    ];

    $form['axis']['y']['tick']['count'] = [
      '#type' => 'number',
      '#title' => $this->t('Tick count'),
      '#description' => $this->t('Limit the number of ticks on the Y axis.'),
      '#default_value' => $this->config('axis', 'y', 'tick', 'count'),
    ];

    $form['axis']['y']['tick']['values']['custom'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tick values'),
      '#description' => $this->t('Override the individual Y axis data-label values manually. Separate values with a comma.'),
      '#default_value' => $this->config('axis', 'y', 'tick', 'values', 'custom'),
    ];

    $form['axis']['y']['tick']['rounding'] = [
      '#type' => 'select',
      '#title' => $this->t('Tick label rounding'),
      '#description' => $this->t('Format the Y axis tick label by rounding to a specific decimal place.'),
      '#options' => [
        '' => $this->t('Automatic'),
        '1' => $this->t('1 place'),
        '2' => $this->t('2 places'),
        '3' => $this->t('3 places'),
        '4' => $this->t('4 places'),
      ],
      '#default_value' => $this->config('axis', 'y', 'tick', 'rounding'),
    ];

    $form['axis']['y']['tick']['format']['indexed'] = [
      '#type' => 'select',
      '#title' => $this->t('Tick label format'),
      '#description' => $this->t('Format the Y axis tick label.'),
      '#options' => [
        '' => $this->t('None'),
        ' ' => $this->t('Space separated Eg. 10 000'),
        ',' => $this->t('Comma separated Eg. 10,000'),
      ],
      '#default_value' => $this->config('axis', 'y', 'tick', 'format', 'indexed'),
    ];

    $form['axis']['y']['styles'] = [
      '#type' => 'details',
      '#title' => $this->t('Y axis styles'),
      '#description' => t('Enter whole numbers (E.g. 320). Values are set as pixels unless otherwise indicated.<br/><br/>'),
      '#tree' => TRUE,
    ];

    foreach (['top', 'bottom'] as $edge) {
      $form['axis']['y']['styles']['padding'][$edge] = [
        '#type' => 'number',
        '#title' => $this->t('Axis padding @edge', ['@edge' => $edge]),
        '#description' => $this->t('Define the padding at the @edge end of the Y axis.', ['@edge' => $edge]),
        '#default_value' => $this->config('axis', 'y', 'styles', 'padding', $edge),
      ];
    }

    $form['axis']['y']['styles']['label']['position'] = [
      '#type' => 'select',
      '#title' => $this->t('Axis title position'),
      '#description' => $this->t('Define the title position on the Y axis.'),
      '#options' => [
        'inner-top' => $this->t('Inner top'),
        'inner-middle' => $this->t('Inner middle'),
        'inner-bottom' => $this->t('Inner bottom'),
        'outer-top' => $this->t('Outer top'),
        'outer-middle' => $this->t('Outer middle'),
        'outer-bottom' => $this->t('Outer bottom'),
      ],
      '#default_value' => $this->config('axis', 'y', 'styles', 'label', 'position'),
      '#empty_option' => $this->t('- Select -'),
      '#empty_option_value' => '',
    ];

    $form['grid'] = [
      '#type' => 'details',
      '#title' => $this->t('Grid settings'),
      '#tree' => TRUE,
    ];

    $form['grid']['x']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show X axis grid'),
      '#description' => $this->t('Check to show thin dashed lines along the X axis grid points.'),
      '#default_value' => $this->config('grid', 'x', 'show'),
    ];

    $form['grid']['y']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show Y axis grid'),
      '#description' => $this->t('Check to show thin dashed lines along the Y axis grid points.'),
      '#default_value' => $this->config('grid', 'y', 'show'),
    ];

    $grid_lines_count = $form_state->get('grid_lines_count');

    if (is_null($grid_lines_count)) {
      $grid_lines_values = $this->config('grid', 'lines');
      $grid_lines_count = count($grid_lines_values);
    }

    $form_state->set('grid_lines_count', $grid_lines_count);

    $form['grid']['lines'] = [
      '#type' => 'details',
      '#title' => $this->t('Grid lines'),
      '#description' => t('Show additional grid lines along X or Y axis. @help',
        ['@help' => $this->dvfHelpers->getHelpPageLink('grid-lines')]),
      '#open' => ($grid_lines_count > 0),
      '#tree' => TRUE,
      '#attributes' => [
        'id' => Html::getUniqueId('dvf-options-grid-lines'),
        'title' => t('E.g. If a dataset contains values between 1 & 40, indicate the average numeric value by adding a Y axis line with a value of 20, and a label "Average number".'),
      ],
    ];

    for ($i = 0; $i < $grid_lines_count; $i++) {
      $form['grid']['lines'][$i] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Grid line @num', ['@num' => ($i + 1)]),
        '#tree' => TRUE,
        '#attributes' => ['class' => ['container-inline']],
      ];

      $form['grid']['lines'][$i]['axis'] = [
        '#type' => 'select',
        '#title' => $this->t('Axis'),
        '#options' => [
          'x' => $this->t('X axis'),
          'y' => $this->t('Y axis'),
        ],
        '#default_value' => $this->config('grid', 'lines', $i, 'axis'),
      ];

      $form['grid']['lines'][$i]['value'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Value'),
        '#size' => 20,
        '#default_value' => $this->config('grid', 'lines', $i, 'value'),
      ];

      $form['grid']['lines'][$i]['text'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Label'),
        '#size' => 20,
        '#default_value' => $this->config('grid', 'lines', $i, 'text'),
      ];

      $form['grid']['lines'][$i]['position'] = [
        '#title' => $this->t('Label position'),
        '#type' => 'select',
        '#options' => [
          'start' => $this->t('Start'),
          'middle' => $this->t('Middle'),
          'end' => $this->t('End'),
        ],
        '#default_value' => $this->config('grid', 'lines', $i, 'position'),
      ];

      $form['grid']['lines'][$i]['class'] = [
        '#title' => $this->t('CSS Class (optional)'),
        '#type' => 'textfield',
        '#size' => 20,
        '#default_value' => $this->config('grid', 'lines', $i, 'class'),
      ];
    }

    $form['grid']['lines']['actions'] = [
      '#type' => 'actions',
      '#tree' => FALSE,
    ];

    $form['grid']['lines']['actions']['add'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Add line'),
      '#submit' => [[$this, 'addGridLine']],
      '#ajax' => [
        'callback' => [$this, 'updateGridLines'],
        'wrapper' => $form['grid']['lines']['#attributes']['id'],
      ],
    ];

    $form['grid']['lines']['actions']['remove'] = [
      '#type' => 'submit',
      '#value' => $this->t('Remove line'),
      '#submit' => [[$this, 'removeGridLine']],
      '#ajax' => [
        'callback' => [$this, 'updateGridLines'],
        'wrapper' => $form['grid']['lines']['#attributes']['id'],
      ],
      '#access' => ($grid_lines_count > 0),
    ];

    $form['chart'] = [
      '#type' => 'details',
      '#title' => $this->t('Chart settings'),
      '#tree' => TRUE,
    ];

    $form['chart']['title']['text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Chart title'),
      '#description' => $this->t('NOTE: if this is used with a split chart, the same title will display on each chart'),
      '#default_value' => $this->config('chart', 'title', 'text'),
    ];

    $form['chart']['interaction'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable chart interaction'),
      '#description' => $this->t('Check to enable all of interactions (E.g. Showing / hiding the tooltip when hovering on labels, etc).'),
      '#default_value' => $this->config('chart', 'interaction'),
    ];

    $form['chart']['table']['disable'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable table'),
      '#description' => $this->t('Disable the "Show table" toggle button on a chart / graph.'),
      '#default_value' => $this->config('chart', 'table', 'disable'),
    ];

    $form['chart']['data']['labels']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show data labels'),
      '#description' => $this->t('Check to show the data labels above the points.'),
      '#default_value' => $this->config('chart', 'data', 'labels', 'show'),
    ];

    $form['chart']['data']['legends']['interaction'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable legends interaction'),
      '#description' => $this->t('Check to enable interactions for the legend (E.g. Hover on legend labels).'),
      '#default_value' => $this->config('chart', 'data', 'legends', 'interaction'),
    ];

    $form['chart']['styles'] = [
      '#type' => 'details',
      '#title' => $this->t('Chart styles'),
      '#description' => t('Enter whole numbers (E.g. 320). Values are set as pixels unless otherwise indicated.<br/><br/>'),
      '#tree' => TRUE,
    ];

    foreach (['width', 'height'] as $measure) {
      $form['chart']['styles'][$measure] = [
        '#type' => 'number',
        '#title' => ucwords($measure),
        '#description' => $this->t('Define the @measure of the chart.', ['@measure' => $measure]),
        '#default_value' => $this->config('chart', 'styles', $measure),
      ];
    }

    foreach (['top', 'right', 'bottom', 'left'] as $edge) {
      $form['chart']['styles']['padding'][$edge] = [
        '#type' => 'number',
        '#title' => $this->t('Padding @edge', ['@edge' => $edge]),
        '#description' => $this->t('Define the amount of padding that will be applied to the @edge of the chart.', ['@edge' => $edge]),
        '#default_value' => $this->config('chart', 'styles', 'padding', $edge),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public static function afterBuildSettingsForm(array $element, FormStateInterface $form_state) {
    $element = parent::afterBuildSettingsForm($element, $form_state);

    $selectors = [
      'axis_x_type' => self::formElementSelector($element['axis']['x']['type'], 'select'),
      'axis_x_tick_values_field' => self::formElementSelector($element['axis']['x']['tick']['values']['field'], 'select'),
      'axis_y_type' => self::formElementSelector($element['axis']['y']['type'], 'select'),
    ];

    $element['axis']['x']['tick']['culling']['#states'] = [
      'visible' => [$selectors['axis_x_type'] => ['value' => 'indexed']],
    ];

    $element['axis']['x']['tick']['values']['custom']['#states'] = [
      'visible' => [$selectors['axis_x_tick_values_field'] => ['value' => '']],
    ];

    $element['axis']['x']['tick']['format']['indexed']['#states'] = [
      'visible' => [$selectors['axis_x_type'] => ['value' => 'indexed']],
    ];

    $element['axis']['x']['tick']['format']['timeseries']['input']['#states'] = [
      'visible' => [$selectors['axis_x_type'] => ['value' => 'timeseries']],
    ];

    $element['axis']['x']['tick']['format']['timeseries']['output']['#states'] = [
      'visible' => [$selectors['axis_x_type'] => ['value' => 'timeseries']],
    ];

    $element['axis']['y']['tick']['rounding']['#states'] = [
      'visible' => [$selectors['axis_y_type'] => ['value' => 'indexed']],
    ];

    $element['axis']['y']['tick']['format']['indexed']['#states'] = [
      'visible' => [$selectors['axis_y_type'] => ['value' => 'indexed']],
    ];

    return $element;
  }

  /**
   * Updates the grid lines options.
   *
   * @param array $form
   *   The form structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The updated form element.
   */
  public function updateGridLines(array $form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $elements = NestedArray::getValue($form, array_slice($triggering_element['#array_parents'], 0, -3));

    return $elements['lines'];
  }

  /**
   * Submit handler for adding a new grid line.
   *
   * @param array $form
   *   The form structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function addGridLine(array &$form, FormStateInterface $form_state) {
    $form_state->set('grid_lines_count', $form_state->get('grid_lines_count') + 1);
    $form_state->setRebuild();
  }

  /**
   * Submit handler for removing a grid line.
   *
   * @param array $form
   *   The form structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function removeGridLine(array &$form, FormStateInterface $form_state) {
    $grid_lines_count = $form_state->get('grid_lines_count');

    if ($grid_lines_count > 0) {
      $form_state->set('grid_lines_count', $grid_lines_count - 1);
    }

    $form_state->setRebuild();
  }

  /**
   * Ajax callback that updates the column overrides options.
   *
   * @param array $form
   *   The form structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The updated form element.
   */
  public function updateColumnOverrides(array $form, FormStateInterface $form_state) {
    $field_name = self::formElementCallbackParentName($form_state);
    return $form[$field_name]['widget'][0]['options']['visualisation_style_options']['data']['column_overrides'];
  }

  /**
   * Ajax callback that updates the x axis grouping options.
   *
   * @param array $form
   *   The form structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The updated form element.
   */
  public function updateAxisGrouping(array $form, FormStateInterface $form_state) {
    $field_name = self::formElementCallbackParentName($form_state);
    return $form[$field_name]['widget'][0]['options']['visualisation_style_options']['axis']['x']['x_axis_grouping'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    foreach ($this->getSourceRecords() as $group_key => $group_records) {
      $build[$group_key]['chart'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['dvf-chart']],
        'heading' => $this->buildSplitHeading($group_key),
        'content' => $this->buildChart($group_records),
      ];

      // Accessible version of the chart.
      $build[$group_key]['table'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['dvf-table', 'visually-hidden']],
        'content' => $this->buildTable($group_records),
      ];

      // A wrapper for the action buttons (toggle, download etc).
      $build[$group_key]['actions'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['table-chart--actions']],
      ];

      // If $file_uri is empty/false, do not display download data button.
      $file_uri = $this->getDatasetDownloadUri($this->visualisation->getEntity());
      if (!empty($file_uri)) {
        $build[$group_key]['actions']['file_uri'] = [
          '#type' => 'html_tag',
          '#tag' => 'button',
          '#value' => $this->t('Download data'),
          '#attributes' => [
            'class' => ['download-data'],
            'data-file-uri' => $file_uri,
          ],
        ];
      }

    }

    return $build;
  }

  /**
   * Builds and returns a chart renderable array for this plugin.
   *
   * @param array $records
   *   The records.
   *
   * @return array
   *   A chart renderable array.
   */
  protected function buildChart(array $records) {
    $chart_id = hash('sha256', time() . mt_rand());

    $chart = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => ['data-dvfcharts' => $chart_id],
    ];

    $chart['#attached']['library'] = ['dvf/dvfCharts'];
    $chart['#attached']['drupalSettings']['dvf']['charts'][$chart_id] = $this->chartBuildSettings($records);

    return $chart;
  }

  /**
   * Returns the chart build settings for this plugin.
   *
   * @param array $records
   *   The records.
   *
   * @return array
   *   An array of chart build settings.
   */
  protected function chartBuildSettings(array $records) {
    $settings = [
      'axis' => $this->config('axis'),
      'grid' => $this->config('grid'),
      'chart' => $this->config('chart'),
    ];

    if (empty($records)) {
      $this->messenger->addError(t('Invalid records.'));
      return [];
    }

    // Axis X and Y tick values from user input.
    foreach (['x', 'y'] as $axis) {
      if ($this->config('axis', $axis, 'tick', 'values', 'custom')) {
        $settings['axis'][$axis]['tick']['values']['custom'] = array_map('trim', explode(',', $this->config('axis', $axis, 'tick', 'values', 'custom')));
      }
    }

    // Axis X tick values from source data, override user input.
    if ($this->config('axis', 'x', 'tick', 'values', 'field')) {
      $settings['axis']['x']['tick']['values']['custom'] = [];

      foreach ($records as $record) {
        if (property_exists($record, $this->config('axis', 'x', 'tick', 'values', 'field'))) {
          $settings['axis']['x']['tick']['values']['custom'][] = trim($record->{$this->config('axis', 'x', 'tick', 'values', 'field')});
        }
      }
    }

    // Get row headers
    $ticks_custom = $settings['axis']['x']['tick']['values']['custom'] ?? [];

    // Data columns.
    foreach ($this->fields() as $field) {
      $settings['chart']['data']['columns'][] = array_merge([$field], $this->getSourceFieldValues($field));
    }

    // Override fields labels if set in chart options.
    $settings['chart']['data']['names'] = $this->fieldLabels();

    // Set the chart title.
    $settings['chart']['title']['text'] = $this->config('chart', 'title', 'text');

    // Column overrides.
    $settings['chart']['data']['column_overrides'] = $this->getColumnOverrides();

    // If X axis grouping occurs on labels, flip the values and the labels.
    if ($this->config('axis', 'x', 'x_axis_grouping') === 'values') {
      $column_labels = array_map('reset', $settings['chart']['data']['columns']);
      $flipped_columns = [];

      foreach ($records as $i => $record) {
        $flipped_columns[] = array_merge(
          [$settings['axis']['x']['tick']['values']['custom'][$i]],
          array_values(array_intersect_key((array) $record, array_flip($column_labels)))
        );
      }

      $settings['chart']['data']['columns'] = $flipped_columns;
      $settings['axis']['x']['tick']['values']['custom'] = $column_labels;
    }

    // Check if values are auto.
    $tick_values_field = $this->config('axis', 'x', 'tick', 'values', 'field');

    if ($settings['axis']['x']['type'] === '' && !empty($record) && property_exists($record, $tick_values_field)) {
      is_numeric($record->{$tick_values_field}) && !in_array($record->{$tick_values_field}, $ticks_custom) ?
        $settings['axis']['x']['type'] = 'indexed' :
        $settings['axis']['x']['type'] = 'category';
    }

    if ($settings['axis']['y']['type'] === '' && !empty($record) && property_exists($record, $field)) {
      is_numeric($record->{$field}) ?
        $settings['axis']['y']['type'] = 'indexed' :
        $settings['axis']['y']['type'] = 'category';
    }

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  protected function tableHeaderField() {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  protected function rowHeaderField() {
    return $this->config('axis', 'x', 'tick', 'values', 'field');
  }

  /**
   * Gets the column overrides settings in a nicely formatted array.
   *
   * @return array
   *   An array of column override settings.
   */
  protected function getColumnOverrides() {
    $allowed_overrides = [
      'color',
      'type',
      'legend',
      'style',
      'weight',
      'class',
    ];
    $column_overrides_sorted = [];
    $pair = 2;

    foreach ($this->config('data', 'column_overrides') as $field_name => $column_override) {
      $field_overrides = explode(PHP_EOL, $column_override);

      foreach ($field_overrides as $field_override) {
        $field_override_array = explode('|', trim($field_override), $pair);

        if (count($field_override_array) === $pair && in_array($field_override_array[0], $allowed_overrides)) {
          $column_overrides_sorted[$field_name][$field_override_array[0]] = $field_override_array[1];
        }
      }
    }

    $column_overrides_sorted = array_merge(array_fill_keys($this->fieldLabelsOriginal(), []), $column_overrides_sorted);
    return $this->setArrayOrder($column_overrides_sorted);
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
    foreach ($array_to_order as $key => $value) {
      $array_to_order[$key][$weight_key] = isset($value[$weight_key]) ? (int) $value[$weight_key] : $i;
      $i++;
    }

    $weights = [];
    foreach ($array_to_order as $key => $row) {
      $weights[$key] = $row[$weight_key];
    }
    array_multisort($weights, SORT_ASC, $array_to_order);

    return $array_to_order;

  }

}
