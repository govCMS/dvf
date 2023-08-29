;(function ($, Drupal, once, drupalSettings) {

  'use strict';

  /**
   * Attaches the dvfCharts behavior to visualisations.
   */
  Drupal.behaviors.dvfCharts = {
    attach: function (context) {
      once('dvf-charts', '[data-dvfcharts]', context).forEach(element => {
        var $chart = $(element),
            chartId = $chart.data('dvfcharts');
        $chart.dvfCharts(drupalSettings.dvf.charts[chartId]);
      });
    }
  };

})(jQuery, Drupal, once, drupalSettings);
