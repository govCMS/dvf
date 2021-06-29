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

    /**
     * Initialises the jQuery datatable plugin with parsed configurations.
     */
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

    /**
     * Generate an array of column headers.
     *
     * @returns {Plugin}
     */
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

    /**
     * Uses the set options to generate the datatables settings configuration.
     *
     * @returns {Plugin}
     */
    parseTableOptions: function () {
      if (this.options.tableOptions) {
        this.config = $.extend(this.config, this.options.tableOptions);
      }

      return this;
    },

    /**
     * Add download data button, this may also get added by charts so .once()
     * is important.
     *
     * @returns {Plugin}
     */
    downloadDataButton: function() {
      var $buttonWrapper = $(this.element).closest('.dvf--wrapper')
        .find('.table-chart--actions');

      // Set download data click listener.
      $('.download-data', $buttonWrapper).once('download-data').each(function(i, dlEl) {
        $(this).on('click', function() {
          window.open($(this).data('file-uri'));
        });
      })

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
