;(function ($, Drupal, drupalSettings) {

  'use strict';

  /**
   * Attaches the dvfTables behavior to tables.
   */
  Drupal.behaviors.dvfTables = {
    attach: function (context) {
      $('[data-dvftables]', context).once('dvf-tables').each(function () {
        var $table = $(this),
            tableId = $table.data('dvftables');
        $table.dvfTables(drupalSettings.dvf.tables[tableId]);
      });
    }
  };

  /**
   * Attaches the dvfTablesDownloadData behavior to download buttons.
   *
   * Applies to HTML tables and datatables.
   */
  Drupal.behaviors.dvfTablesDownloadData = {
    attach: function (context) {
      $('.dvf-table--wrapper .download-data', context).once('download-data').each(function () {
        $(this).click(function() {
          window.open($(this).data('file-uri'));
        });
      });
    }
  }

})(jQuery, Drupal, drupalSettings);
