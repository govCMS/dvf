;(function ($, Drupal, drupalSettings) {

  'use strict';

  /**
   * Attaches the dvfCharts behavior to visualisations.
   */
  Drupal.behaviors.dvfCharts = {
    attach: function (context) {
      $('[data-dvfcharts]', context).each(function () {
        var $chart = $(this),
            chartId = $chart.data('dvfcharts');
        $chart.dvfCharts(drupalSettings.dvf.charts[chartId]);
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
