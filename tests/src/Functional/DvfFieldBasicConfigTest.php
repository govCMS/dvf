<?php

namespace Drupal\Tests\dvf\Functional;

/**
 * Class DvfFieldBasicConfigTest
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
class DvfFieldBasicConfigTest extends DvfFieldTestBase
{
  /**
   * Tests the configuration of a visualisation file with csv source.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testConfigureFieldVisualisationFileCsv() {
    $assert_session = $this->assertSession();
    // Create a new file field csv and attach to page bundle.
    $field_name = 'testing_field_vis_file_csv';
    $field_settings = [
      'visualisation_source' => 'dvf_csv_file',
    ];
    $this->createDvfFileField($field_name, $field_settings);

    // Create new page node.
    $nid = $this->createTestNode();
    $edit_page_path = "node/$nid/edit";
    // Confirm that field displays on node config page.
    $this->visitAndAssertText($edit_page_path, $field_name);

    // Upload a csv sample file to node page.
    $csv_sample_filename = 'fruits.csv';
    $this->uploadSampleFileToNode($csv_sample_filename, $field_name);

    // Confirm that file name is displayed on page.
    $assert_session->pageTextNotContains('could not be uploaded');

    // Select a visualisation style.
    $assert_session->fieldExists($field_name . '[0][options][visualisation_style]')
      ->setValue($this->default_visualisation_style);
    $this->submitForm([],$this->t('Save'));
    $this->drupalGet($edit_page_path);

    // Select visualisation style options.
    $base_field_name_style_options = $field_name . '[0][options][visualisation_style_options][data]';
    // Select all fields - if these appear then headers have been correctly parsed.
    $assert_session->fieldExists($base_field_name_style_options . '[fields][]')
      ->setValue($this->sample_file_fields);

    // Save visualisation style options.
    $this->submitForm([],$this->t('Save'));

    // Visit page in frontend and confirm display of all chart headers.
    $this->drupalGet("node/$nid");
    foreach ($this->sample_file_fields as $field) {
       $assert_session->pageTextContains($field);
    }
  }
}
