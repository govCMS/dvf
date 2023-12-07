<?php

namespace Drupal\Tests\dvf\Functional;

/**
 * Class DvfFieldBasicConfigTest.
 *
 * Functional tests for basic configuration of dvf fields.
 * Validates that a dvf field can be configured with minimum settings,
 * without errors and configured chart renders on the frontend.
 *
 * For more advanced configuration create a more specific test file.
 *
 * All tests use the default visualisation style
 * set on parent class DvfFieldTestBase.
 *
 * @group dvf
 * @package Drupal\Tests\dvf\Functional
 */
class DvfFieldBasicConfigTest extends DvfFieldTestBase {

  /**
   * Tests the configuration of a visualisation files.
   */
  public function testConfigureFieldVisualisationFiles() {
    $fileTypes = [
      'csv',
      'json',
    ];

    foreach ($fileTypes as $fileType) {
      $this->configureFieldVisualisationFile($fileType);
    }
  }

  /**
   * Test that the visualisation style config properties are correct.
   */
  public function testVisualisationStyleProperties() {
    $visualisation_styles = [
      'bar_chart',
      'bubble_chart',
      'donut_chart',
      'gauge_chart',
      'line_chart',
      'pie_chart',
      'radar_chart',
      'scatter_plot_chart',
      'spline_chart',
      'table',
    ];

    foreach ($visualisation_styles as $visualisation_style) {
      $settingsText = ucfirst(str_replace('_', ' ', $visualisation_style)) . ' settings';
      $this->configureVisualisationStyle('csv', "dvf_$visualisation_style", $settingsText);
    }
  }

  /**
   * Configure the visualisation style dropdown properties.
   */
  public function configureVisualisationStyle($fileType = 'csv', $style = 'dvf_bar_chart', $settingsText = '') {
    $assert_session = $this->assertSession();
    // Create a new file field json and attach to page bundle.
    $field_name = "field_{$fileType}_{$style}";
    $field_settings = [
      'visualisation_source' => "dvf_{$fileType}_file",
    ];
    $this->createDvfFileField($field_name, $field_settings);

    // Create new page node.
    $nid = $this->createTestNode();
    $edit_page_path = "node/$nid/edit";
    // Confirm that field displays on node config page.
    $this->visitAndAssertText($edit_page_path, $field_name);

    // Upload a csv sample file to node page.
    $csv_sample_filename = "fruits.$fileType";
    $this->uploadSampleFileToNode($csv_sample_filename, $field_name);

    // Confirm that file name is displayed on page.
    $assert_session->pageTextNotContains('could not be uploaded');

    // Select a visualisation style.
    $assert_session->fieldExists($field_name . '[0][options][visualisation_style]')
      ->setValue($style);
    $this->submitForm([], $this->t('Save'));
    $this->drupalGet($edit_page_path);
    $assert_session->pageTextContains($settingsText);

    return [
      'field_name' => $field_name,
      'assert_session' => $assert_session,
      'nid' => $nid,
    ];
  }

  /**
   * Common test for the configuration of a visualisation file.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function configureFieldVisualisationFile($fileType = 'csv') {
    $response = $this->configureVisualisationStyle($fileType,'dvf_bar_chart', 'Bar chart settings');

    $field_name = $response['field_name'];
    $nid = $response['nid'];
    $assert_session = $response['assert_session'];

    // Select visualisation style options.
    // @codingStandardsIgnoreStart - ignore line exceed warning.
    $base_field_name_style_options = $field_name . '[0][options][visualisation_style_options][data]';
    // @codingStandardsIgnoreEnd
    // Select all fields.
    // (if these appear then headers have been correctly parsed.)
    $assert_session->fieldExists($base_field_name_style_options . '[fields][]')
      ->setValue($this->sampleFileFields);

    // Save visualisation style options.
    $this->submitForm([], $this->t('Save'));

    // Visit page in frontend and confirm display of all chart headers.
    $this->drupalGet("node/$nid");
    foreach ($this->sampleFileFields as $field) {
      $assert_session->pageTextContains($field);
    }
  }

}
