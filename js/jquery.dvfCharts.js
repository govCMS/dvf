;(function ($, c3) {

  'use strict';

  var pluginName = 'dvfCharts',
      defaults = {};

  /**
   * Plugin constructor.
   */
  function Plugin(element, options) {
    this.element = element;
    this.options = $.extend( {}, defaults, options);
    this.config = {};

    this._defaults = defaults;
    this._name = pluginName;

    this.init();
  }

  /**
   * Plugin prototype object.
   */
  Plugin.prototype = {

    init: function () {
      this
        .parseChartOptions()
        .parseDataOptions()
        .parseAxisOptions()
        .parseGridOptions()
        .parseLegendOptions()
        .parsePointOptions()
        .parseBarOptions()
        .parseGaugeOptions()
        .generateChart();
    },

    generateChart: function () {
      c3.generate(this.config);
    },

    parseChartOptions: function () {
      var plugin = this;

      this.config.bindto = this.element;

      $.each(['width', 'height'], function (m, measure) {
        if (plugin.options.chart.styles[measure]) {
          plugin.config.size = plugin.config.size || {};
          plugin.config.size[measure] = parseInt(plugin.options.chart.styles[measure]);
        }
      });

      $.each(['top', 'right', 'bottom', 'left'], function (e, edge) {
        if (plugin.options.chart.styles.padding[edge]) {
          plugin.config.padding = plugin.config.padding || {};
          plugin.config.padding[edge] = parseInt(plugin.options.chart.styles.padding[edge]);
        }
      });

      this.config.interaction = { enabled: this.options.chart.interaction };

      if (plugin.options.chart.palette) {
        this.config.color = {pattern: plugin.options.chart.palette.split(',')};
      }

      return this;
    },

    parseDataOptions: function () {
      var data = { columns: this.options.chart.data.columns };

      if (this.options.axis.x.tick.values.custom) {
        data.x = 'x';
        data.columns.push(['x'].concat(this.options.axis.x.tick.values.custom));
      }

      if (this.options.axis.x.type === 'timeseries' && this.options.axis.x.tick.format.timeseries.input) {
        data.xFormat = this.options.axis.x.tick.format.timeseries.input;
      }

      data.type = this.options.chart.data.type;
      data.labels = this.options.chart.data.labels.show;

      if (this.options.chart.data.stacked) {
        data.groups = [$.map(this.options.chart.data.groups, function(g, group) { return [group]; })];
        data.order = this.options.chart.data.order;
      }

      if (this.options.chart.data.names) {
        data.names = this.options.chart.data.names;
      }

      this.config.data = data;

      return this;
    },

    parseAxisOptions: function () {
      var plugin = this, axis = {};

      axis.rotated = this.options.axis.styles.rotated;

      axis.x = {
        type: this.options.axis.x.type,
        tick: {
          centered: this.options.axis.x.styles.tick.centered,
          multiline: this.options.axis.x.styles.tick.multiline
        }
      };

      if (this.options.axis.x.type === 'indexed' && this.options.axis.x.tick.format.indexed) {
        axis.x.tick.format = function (x) {
          return plugin.formatNumber(x, plugin.options.axis.x.tick.format.indexed);
        };
      }

      if (this.options.axis.x.type === 'timeseries' && this.options.axis.x.tick.format.timeseries.output) {
        axis.x.tick.format = this.options.axis.x.tick.format.timeseries.output;
      }

      if (this.options.axis.x.type === 'indexed' && this.options.axis.x.tick.culling) {
        axis.x.tick.culling = { max: parseInt(this.options.axis.x.tick.culling) };
        axis.x.tick.format = function (x) {
          return Math.round(x);
        };
      }

      if (this.options.axis.x.tick.count) {
        axis.x.tick.count = parseInt(this.options.axis.x.tick.count);
      }

      if (this.options.axis.x.styles.tick.rotate) {
        axis.x.tick.rotate = parseInt(this.options.axis.x.styles.tick.rotate);
      }

      if (this.options.axis.x.styles.tick.width) {
        axis.x.tick.width = this.options.axis.x.styles.tick.width;
      }

      if (this.options.axis.x.styles.padding.left) {
        axis.x.padding = axis.x.padding || {};
        axis.x.padding.left = parseInt(this.options.axis.x.styles.padding.left);
      }

      if (this.options.axis.x.styles.padding.right) {
        axis.x.padding = axis.x.padding || {};
        axis.x.padding.right = parseInt(this.options.axis.x.styles.padding.right);
      }

      axis.x.label = {
        text: this.options.axis.x.label.text,
        position: (this.options.axis.styles.rotated) ? this.options.axis.y.styles.label.position : this.options.axis.x.styles.label.position
      };

      axis.y = {
        type: this.options.axis.y.type,
        tick: {}
      };

      axis.y.tick.format = function (y) {
        if (plugin.options.axis.y.type !== 'indexed') {
          return y;
        }

        var number = plugin.maxRound(y, 4);

        if (plugin.options.axis.y.tick.rounding) {
          number = number.toFixed(parseInt(plugin.options.axis.y.tick.rounding));
        }

        if (plugin.options.axis.y.tick.format.indexed) {
          number = plugin.formatNumber(number, plugin.options.axis.y.tick.format.indexed);
        }

        return number;
      };

      if (this.options.axis.y.tick.count) {
        axis.y.tick.count = parseInt(this.options.axis.y.tick.count);
      }

      if (this.options.axis.y.tick.values.custom) {
        axis.y.tick.values = this.options.axis.y.tick.values.custom;
      }

      if (this.options.axis.x.styles.padding.top) {
        axis.x.padding = axis.x.padding || {};
        axis.x.padding.top = parseInt(this.options.axis.x.styles.padding.top);
      }

      if (this.options.axis.y.styles.padding.bottom) {
        axis.y.padding = axis.y.padding || {};
        axis.y.padding.bottom = parseInt(this.options.axis.y.styles.padding.bottom);
      }

      axis.y.label = {
        text: this.options.axis.y.label.text,
        position: (this.options.axis.styles.rotated ? this.options.axis.x.styles.label.position: this.options.axis.y.styles.label.position)
      };

      this.config.axis = axis;

      return this;
    },

    parseGridOptions: function () {
      var grid = {
        x: {
          show: this.options.grid.x.show,
          lines: []
        },
        y: {
          show: this.options.grid.y.show,
          lines: []
        }
      };

      $.each(this.options.grid.lines, function(l, line) {
        grid[line.axis].lines.push(line);
      });

      this.config.grid = grid;

      return this;
    },

    parseLegendOptions: function () {
      var legend = {};

      if (!this.options.chart.data.legends.interaction) {
        legend.item = {
          onclick: function () {
            return false;
          },
          onmouseover: function () {
            return false;
          },
          onmouseout: function () {
            return false;
          }
        };
      }

      this.config.legend = $.isEmptyObject(legend) ? null : legend;

      return this;
    },

    parsePointOptions: function () {
      var point = {},
          pointShow = this.getDeepProperty(this.options, 'point.show'),
          pointRadius = this.getDeepProperty(this.options, 'point.radius');

      if (pointShow) {
        point.show = pointShow;
      }

      if (pointRadius) {
        point.r = pointRadius;
      }

      this.config.point = $.isEmptyObject(point) ? null : point;

      return this;
    },

    parseBarOptions: function () {
      var bar = {},
          barRatio = this.getDeepProperty(this.options, 'bar.width.ratio'),
          barValue = this.getDeepProperty(this.options, 'bar.width.value');

      if (barRatio && barRatio !== 'manual') {
        bar.width = { ratio: parseFloat(barRatio) };
      }

      if (barValue && barRatio === 'manual') {
        bar.width = parseInt(barValue);
      }

      this.config.bar = $.isEmptyObject(bar) ? null : bar;

      return this;
    },

    /**
     * Parse user settings into object suitable for use with C3 Gauge Chart.
     *
     * @example https://c3js.org/samples/chart_gauge.html
     * @see https://c3js.org/reference.html#gauge-label-show
     *
     * @returns {Plugin}
     */
    parseGaugeOptions: function () {
      var gauge = {},
        gaugeLabelShow = this.getDeepProperty(this.options, 'gauge.label.show'),
        gaugePercentage = this.getDeepProperty(this.options, 'gauge.label.percentage'),
        gaugeMin = this.getDeepProperty(this.options, 'gauge.min'),
        gaugeMax = this.getDeepProperty(this.options, 'gauge.max'),
        gaugeUnits = this.getDeepProperty(this.options, 'gauge.units'),
        gaugeWidth = this.getDeepProperty(this.options, 'gauge.width');
        gauge.label = {
          format: false,
          show: false
        };

      if (gaugeLabelShow) {
        gauge.label.show = gaugeLabelShow;
      }

      if (!gaugePercentage) {
        gauge.label.format = function(value, ratio) { return value; }
      }

      if (gaugeMin) {
        gauge.min = gaugeMin;
      }

      if (gaugeMax) {
        gauge.max = gaugeMax;
      }

      if (gaugeUnits) {
        gauge.units = gaugeUnits;
      }

      if (gaugeWidth) {
        gauge.width = gaugeWidth;
      }

      this.config.gauge = $.isEmptyObject(gauge) ? null : gauge;

      return this;
    },

    maxRound: function (number, decimals) {
      return Math.round(number * Math.pow(10, decimals)) / Math.pow(10, decimals);
    },

    formatNumber: function (number, separator) {
      return number.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1' + separator);
    },

    getDeepProperty: function(obj, key) {
      return key.split('.').reduce(function (o, k) {
        return (typeof o === 'undefined' || o === null) ? o : o[k];
      }, obj);
    }

  };

  /**
   * Plugin wrapper.
   */
  $.fn[pluginName] = function (options) {
    return this.each(function () {
      if (!$.data(this, 'plugin_' + pluginName)) {
        $.data(this, 'plugin_' + pluginName, new Plugin(this, options));
      }
    });
  };

})(jQuery, c3);
