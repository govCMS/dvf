;(function ($) {

  'use strict';

  var pluginName = 'dvfTables',
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
        .parseData()
        .parseColumns()
        .parseTableOptions()
        .generateTable();
    },

    generateTable: function () {
      $(this.element).dataTable(this.config);
    },

    parseData: function () {
      var data = [];

      $.each(this.options.data, function (r, row) {
        $.each(row, function (c, cell) {
          data[r] = data[r] || [];
          data[r].push(cell.data);
        });
      });

      this.config.data = data;

      return this;
    },

    parseColumns: function () {
      var columns = [];

      $.each(this.options.columns, function (c, column) {
        if (column.header === false) {
          columns.push({ cellType: 'th' });
        }
        else {
          columns.push({ title: column.data });
        }
      });

      this.config.columns = columns;

      return this;
    },

    parseTableOptions: function () {
      if (this.options.tableOptions) {
        this.config = $.extend(this.config, this.options.tableOptions);
      }

      return this;
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

})(jQuery);
