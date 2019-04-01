/**
 * @file
 * JavaScript behaviors for DVF admin forms.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Provides the help information in a window popup.
   *
   * @type {{attach: Drupal.behaviors.dvfAdminPopup.attach}}
   */
  Drupal.behaviors.dvfAdminPopup = {
    attach: function (context) {
      $('.dvf-admin-popup a', context).on('click', function(e) {
        e.preventDefault();
        var w = window.open(this.href, 'advanced_help_window', 'width=600, height=600, scrollbars, resizable');
        w.focus();
        return false;
      });
    }
  };

})(jQuery, Drupal);
