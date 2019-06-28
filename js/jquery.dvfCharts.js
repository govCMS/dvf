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
        .parseColumnOverrideOptions()
        .generateChart()
        .addDownloadButtons();
    },

    /**
     * Calls the C3 third party plugin with the parsed config.
     *
     * @returns {Plugin}
     */
    generateChart: function () {
      c3.generate(this.config);
      return this;
    },

    /**
     * Parses the chart options and sets them to the config.
     *
     * @returns {Plugin}
     */
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
        this.config.color = {
          pattern: plugin.options.chart.palette.split(',').map(function(color) {
            return color.trim();
          })
        };
      }

      // Display chart title is title.show is true.
      if (plugin.options.chart.title.show) {
        this.config.title = { text: plugin.options.chart.title.text };
      }

      return this;
    },

    /**
     * Parses the data options and sets them to the config.
     *
     * @returns {Plugin}
     */
    parseDataOptions: function () {
      var data = { columns: this.options.chart.data.columns };

      if (this.options.axis.x.type === 'timeseries' && this.options.axis.x.tick.format.timeseries.input) {
        data.xFormat = this.options.axis.x.tick.format.timeseries.input;
      }

      data.type = this.options.chart.data.type;

      if (this.options.chart.data.stacked) {

        // Assign data groups depending on x axis grouping value.
        data.groups =
          this.options.axis.x.x_axis_grouping === 'values' ?
          [$.map(data.columns, function(group) { return group[0]; })] :
          [$.map(this.options.chart.data.groups, function(g, group) { return [group]; })];

        data.order = this.options.chart.data.order;
      }

      if (this.options.axis.x.tick.values.custom) {
        data.x = 'x';
        data.columns.unshift(['x'].concat(this.options.axis.x.tick.values.custom));
      }

      if (this.options.chart.data.names) {
        data.names = this.options.chart.data.names;
      }

      data.labels = !!this.options.chart.data.labels.show;

      this.config.data = data;

      return this;
    },

    /**
     * Parses the axis options and sets them to the config.
     *
     * @returns {Plugin}
     */
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

      if (this.options.axis.y.styles.padding && typeof(this.options.axis.y.styles.padding) == 'object') {
        axis.y.padding = this.options.axis.y.styles.padding;
      }

      if (this.options.axis.x.styles.padding && typeof(this.options.axis.x.styles.padding) == 'object') {
        axis.x.padding = this.options.axis.x.styles.padding;
      }

      axis.y.label = {
        text: this.options.axis.y.label.text,
        position: (this.options.axis.styles.rotated ? this.options.axis.x.styles.label.position: this.options.axis.y.styles.label.position)
      };

      this.config.axis = axis;

      return this;
    },

    /**
     * Parses the grid options and sets them to the config.
     *
     * @returns {Plugin}
     */
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

    /**
     * Parses the legend options and sets them to the config.
     *
     * @returns {Plugin}
     */
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

    /**
     * Parses the point options and sets them to the config.
     *
     * @returns {Plugin}
     */
    parsePointOptions: function () {
      var point = {},
          pointShow = this.getDeepProperty(this.options, 'point.show'),
          pointRadius = this.getDeepProperty(this.options, 'point.radius');

      point.show = !!pointShow;

      if (pointRadius) {
        point.r = pointRadius;
      }

      this.config.point = $.isEmptyObject(point) ? null : point;

      return this;
    },

    /**
     * Parses the bar chart options and sets them to the config.
     *
     * @returns {Plugin}
     */
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

    /**
     * Adds the various download buttons below the chart.
     *
     * @returns {Plugin}
     */
    addDownloadButtons: function () {
      if (typeof $.fn.chartExport !== 'function') {
        return this;
      }
      var buttonTypes = ['png', 'svg'],
          processedClass = 'processed-download-buttons',
          $buttonWrapper = $(this.element)
            .closest('.dvf-chart')
            .nextAll('.table-chart--actions');

      if ($buttonWrapper.hasClass(processedClass)) {
        return this;
      }

      $(buttonTypes).each(function (i, format) {

        $('<button/>')
          .html('Download as ' + format)
          .addClass(this.options.chart.component + '--download')
          .chartExport($.extend({
            format: format,
            svg: $(this.element).is('svg') ? $(this.element) : $(this.element).find('svg'),
          }, {
            // Add optional settings if they exist.
            width: this.options.chart.styles.width || undefined,
            height: this.options.chart.styles.height || undefined,
            filename: (this.config.title && this.config.title.text) ? this.config.title.text : undefined,
          }))
          .appendTo($buttonWrapper);
      }.bind(this));

      // Set download data click listener.
      $('.download-data', $buttonWrapper).on('click', function() {
        window.open($(this).data('file-uri'));
      });

      $buttonWrapper.addClass(processedClass);

      return this;
    },

    /**
     * Parses the column override settings and applies them accordingly.
     *
     * @returns {Plugin}
     */
    parseColumnOverrideOptions: function () {
      // If column overrides do not exist, exit without processing.
      if (this.options.chart.data.column_overrides === undefined) {
        return this;
      }

      var column_overrides = this.options.chart.data.column_overrides;

      // Colors.
      this.overrideDataSetting(column_overrides, 'color');

      // Types.
      this.overrideDataSetting(column_overrides, 'type');

      // Styles.
      this.overrideDataSetting(column_overrides, 'style', 'regions');

      // Classes.
      this.overrideDataSetting(column_overrides, 'class', 'classes');

      // Weight.
      var ordered_columns = [];
      $.each(column_overrides, function(name, overrides) {
        if (overrides.weight !== undefined) {
          $.each(this.config.data.columns, function(key, column) {
            if (name === column[0]) {
              ordered_columns.push(column);
            }
          });
        }
      }.bind(this));

      if (this.config.data.columns.length && this.config.data.columns[0].length) {
        if (this.config.data.columns[0][0] === 'x' && this.config.data.columns.length !== ordered_columns.length) {
          ordered_columns.unshift(this.config.data.columns[0]);
        }
      }

      if (this.config.data.columns.length === ordered_columns.length) {
        this.config.data.columns = ordered_columns;
      }

      // Legend.
      $.each(column_overrides, function(key, overrides) {
        if (overrides.legend) {
          this.config.legend = this.config.legend || [];
          this.config.legend[overrides.legend] = this.config.legend[overrides.legend] || [];
          this.config.legend[overrides.legend].push(key);
        }
      }.bind(this));

      return this;
    },

    /**
     * Sets custom settings or overrides for config.data.X
     *
     * @param column_overrides
     *   The array of settings to get the data from.
     * @param override_key
     *   The key to look for in the above array.
     * @param config_key
     *   The key to set the new settings to in the config array.
     *
     * @returns {Plugin}
     */
    overrideDataSetting: function (column_overrides, override_key, config_key) {

      config_key = config_key || override_key + 's';

      $.each(column_overrides, function(key, overrides) {
        if (overrides[override_key]) {
          this.config.data[config_key] = this.config.data[config_key] || [];
          this.config.data[config_key][key] = overrides[override_key];
        }
      }.bind(this));

      return this;
    },

    /**
     * Returns a rounded up number to a set amount of decimal points.
     *
     * @param number
     *   The number to be rounded.
     * @param decimals
     *   The number of decimal places.
     * @returns {number}
     *   The rounded number.
     */
    maxRound: function (number, decimals) {
      return Math.round(number * Math.pow(10, decimals)) / Math.pow(10, decimals);
    },

    /**
     * Returns a number with a separator format applied (e.g. 12 345 vs 12,345).
     *
     * @param number
     *   The number to format.
     * @param separator
     *   The separator.
     * @returns {string}
     *   The formatted number.
     */
    formatNumber: function (number, separator) {
      return number.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1' + separator);
    },

    /**
     * Gets a property nested in a JS object.
     *
     * @param obj
     *   The object to traverse.
     * @param key
     *   The key buried in the object.
     * @returns {*}
     *   The result of the search.
     */
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
