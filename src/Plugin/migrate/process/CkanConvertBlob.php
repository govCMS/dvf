<?php

namespace Drupal\dvf\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate\ProcessPluginBase;

/**
 * Converts D7 ckan blob settings into D8 settings. Single purpose plugin.
 *
 * @MigrateProcessPlugin(
 *   id = "ckan_convert_blob"
 * )
 *
 * @code
 * process:
 *   field_d7_ckan_visualisation/0/options:
 *     -
 *       plugin: ckan_convert_blob
 *       source: ckan_visualisation_d7_configuration
 * @endcode
 */
class CkanConvertBlob extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    if (empty($value)) {
      return $value;
    }

    $ckan_values = unserialize($value);
    $ckan_config = $ckan_values['visualisation_config'];

    $visualisation_style = [
      'visualisation_style' => 'dvf_' . $ckan_values['visualisation'],
      'visualisation_style_options' => [

        // Data settings.
        'data' => [
          'fields' => count($ckan_config['keys']) ? array_filter($ckan_config['keys']) : [],
          'field_labels' => $ckan_config['label_settings']['overrides'],
          'split_field' => $ckan_config['split'],
          'cache_expiry' => $ckan_config['cache_ttl'],
          'column_overrides' => $ckan_config['column_overrides'],
          'data_filters' => [
            'q' => $ckan_config['ckan_filters']['search'],
            'filters' => $ckan_config['ckan_filters']['filters'],
          ],
        ],

        // Axis settings.
        'axis' => [
          'styles' => ['rotated' => $ckan_config['rotated'] === 'true' ? TRUE : FALSE],

          'x' => [
            'type' => $ckan_config['axis_settings']['x_type'],
            'label' => ['text' => $ckan_config['x_label']],
            'x_axis_grouping' => $ckan_config['x_axis_grouping'],
            'tick' => [
              'count' => $ckan_config['axis_settings']['x_tick_count'],
              'culling' => $ckan_config['axis_settings']['x_tick_cull'],
              'format' => ['timeseries' => $ckan_config['axis_settings']['x_date_format']],
              'values' => [
                'field' => $ckan_config['labels'],
                'custom' => $ckan_config['axis_settings']['x_tick_values'],
                'indexed' => $ckan_config['axis_settings']['x_tick_value_format'] ?: '',
              ],
            ],
            'styles' => [
              'padding' => $ckan_config['axis_settings']['x_padding'],
              'label' => [
                'position' => 'outer-center',
              ],
              'tick' => [
                'rotate' => $ckan_config['axis_settings']['x_tick_rotate'],
                'width' => $ckan_config['axis_settings']['x_width'],
                'multiline' => $ckan_config['axis_settings']['x_disable_multiline'],
              ],
            ],
          ],

          'y' => [
            'type' => is_null($ckan_config['axis_settings']['y_type']) ? '' : $ckan_config['axis_settings']['y_type'],
            'label' => ['text' => $ckan_config['y_label']],
            'tick' => [
              'count' => $ckan_config['axis_settings']['y_tick_count'],
              'culling' => $ckan_config['axis_settings']['y_tick_cull'],
              'format' => ['timeseries' => $ckan_config['axis_settings']['x_date_format']],
              'values' => [
                'field' => $ckan_config['labels'],
                'custom' => $ckan_config['axis_settings']['y_tick_values'],
                'indexed' => $ckan_config['axis_settings']['y_tick_value_format'] ?: '',
              ],
            ],
            'styles' => [
              'padding' => $ckan_config['axis_settings']['y_padding'],
              'label' => [
                'position' => 'outer-middle',
              ],
              'tick' => [
                'rotate' => $ckan_config['axis_settings']['y_tick_rotate'],
                'width' => $ckan_config['axis_settings']['y_width'],
                'multiline' => $ckan_config['axis_settings']['y_disable_multiline'],
              ],
            ],
          ],
        ],

        // Grid settings.
        'grid' => [
          'x' => ['show' => $ckan_config['grid'] === 'x' ? TRUE : FALSE],
          'y' => ['show' => $ckan_config['grid'] === 'y' ? TRUE : FALSE],
          'lines' => $ckan_config['grid_lines']['lines'],
        ],

        // Chart settings.
        'chart' => [
          'title' => ['show' => $ckan_config['show_title']],
          'interaction' => $ckan_config['disable_chart_interaction'] ? FALSE : TRUE,
          'data' => [
            'labels' => ['show' => $ckan_config['show_labels']],
            'legends' => ['interaction' => $ckan_config['disable_legend_interaction'] ? FALSE : TRUE],
          ],
          'styles' => [
            'width' => $ckan_config['chart_size']['width'],
            'height' => $ckan_config['chart_size']['height'],
            'padding' => $ckan_config['chart_padding'],
          ],
        ],
      ],
    ];

    // Individual chart type styles.
    switch ($ckan_values['visualisation']) {
      case 'bar_chart':
        $visualisation_style['visualisation_style_options']['bar_chart']['stacked'] = $ckan_config['stacked'];
        $visualisation_style['visualisation_style_options']['chart']['data']['stacked'] = $ckan_config['stacked'];

        $visualisation_style['visualisation_style_options']['bar_chart']['data']['order'] = $ckan_config['data_order'];
        $visualisation_style['visualisation_style_options']['chart']['data']['order'] = $ckan_config['data_order'];

        $visualisation_style['visualisation_style_options']['bar_chart']['bar']['width']['ratio'] = $ckan_config['bar_width'];
        $visualisation_style['visualisation_style_options']['chart']['width']['ratio'] = $ckan_config['bar_width'];

        $visualisation_style['visualisation_style_options']['bar_chart']['bar']['width']['value'] = $ckan_config['bar_width_override'];
        $visualisation_style['visualisation_style_options']['chart']['width']['value'] = $ckan_config['bar_width_override'];

        break;

      case 'line_chart':
        $visualisation_style['visualisation_style_options']['chart']['data']['type'] = $ckan_config['area'] ? 'area' : $visualisation_style['chart']['data']['type'];
        $visualisation_style['visualisation_style_options']['line_chart']['area']['enabled'] = $ckan_config['area'] ? TRUE : FALSE;

        $visualisation_style['visualisation_style_options']['point']['show'] = $ckan_config['hide_points'] ? FALSE : TRUE;
        $visualisation_style['visualisation_style_options']['line_chart']['data']['points']['show'] = $ckan_config['hide_points'] ? FALSE : TRUE;
        break;

      case 'scatter_plot_chart':
        $visualisation_style['visualisation_style_options']['scatter_plot_chart']['point']['size'] = $ckan_config['point_size'];
        $visualisation_style['visualisation_style_options']['point']['radius'] = $ckan_config['point_size'];
        break;

      case 'spline_chart':
        $visualisation_style['visualisation_style_options']['point']['show'] = $ckan_config['hide_points'] ? FALSE : TRUE;
        $visualisation_style['visualisation_style_options']['spline_chart']['data']['points']['show'] = $ckan_config['hide_points'] ? FALSE : TRUE;
        break;
    }

    return $visualisation_style;

  }

}
