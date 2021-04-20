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
        .addToggleButton()
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
     * Add toggle table / chart button.
     *
     * @returns {Plugin}
     */
    addToggleButton: function () {

      if (this.options.table.disable) {
        return this;
      }

      var self = this,
        processedClass = 'processed-toggle-button',
        $buttonWrapper = $(this.element)
          .closest('.dvf-table')
          .nextAll('.table-chart--actions');

      if ($buttonWrapper.hasClass(processedClass)) {
        return this;
      }

      $('<button/>')
        .html('Show table')
        .addClass('table-chart--toggle')
        .click(self.toggleView.bind(self))
        .appendTo($buttonWrapper);

      $buttonWrapper.addClass(processedClass);

      // Set download data click listener.
      if ($(this.element).is('table')) {
        $('.download-data', $(this.element).closest('.dvf-table')).on('click', function() {
          window.open($(this).data('file-uri'));
        });
      }

      return this;
    },

    /**
     * Toggles the visibility of the table / chart, and the button text.
     *
     * @returns {Plugin}
     */
    toggleView: function () {

      var $dvfParent = $(this.element).closest('.dvf-table').parent(),
        $toggleButton = $('.table-chart--toggle', $dvfParent);

      $('.dvf-chart, .dvf-table', $dvfParent).toggleClass('visually-hidden');
      $toggleButton.text($toggleButton.text().toLowerCase().trim() === 'show chart' ? 'Show table' : 'Show chart');

      return this;
    },

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
