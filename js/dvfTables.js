;(function ($, Drupal, drupalSettings) {

  'use strict';

  /**
   * Attaches the dvfTables behavior to tables.
   */
  Drupal.behaviors.dvfTables = {
    attach: function (context) {
      $('[data-dvftables]', context).each(function () {
        var $table = $(this),
            tableId = $table.data('dvftables');
        $table.dvfTables(drupalSettings.dvf.tables[tableId]);
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
