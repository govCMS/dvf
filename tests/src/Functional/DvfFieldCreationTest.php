<?php

namespace Drupal\Tests\dvf\Functional;

/**
 * Functional tests for creation of dvf fields.
 *
 * @group dvf
 */
class DvfFieldCreationTest extends DvfFieldTestBase {

  /**
   * Test that the Visualisation file field can be added.
   *
   * (... to an entity such as page.)
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  public function testAddFieldVisualisationFile() {
    $new_field_label = 'Testing visualisation file field';
    $new_field_name = 'testing_vis_file_field';
    $storage_type = 'dvf_file';

    $this->addNewFieldToPage($new_field_label, $new_field_name, $storage_type);
  }

  /**
   * Test that the Visualisation url field can be added.
   *
   * (... to an entity such as page.)
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  public function testAddFieldVisualisationUrl() {
    $new_field_label = 'Testing visualisation url field';
    $new_field_name = 'testing_vis_url_field';
    $storage_type = 'dvf_url';

    $this->addNewFieldToPage($new_field_label, $new_field_name, $storage_type);
  }

  /**
   * Helper function to add and configure a new field to page entity.
   *
   * @param string $new_field_label
   *   Label setting of the new field.
   * @param string $new_field_name
   *   Name setting of the new field.
   * @param string $storage_type
   *   New field storage type (dvf_url or dvf_file).
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  public function addNewFieldToPage($new_field_label, $new_field_name, $storage_type) {
    $page_path = 'admin/structure/types/manage/page/fields/add-field';
    $visualisation_source_test = 'dvf_csv_file';

    // Visit the add field page to "page" content type.
    $this->drupalGet($page_path);

    $assert_session = $this->assertSession();

    // Make sure that selectbox has Visualisation file option.
    $assert_session->optionExists('new_storage_type', $storage_type);

    // Select field option, add dummy name and submit.
    $assert_session->fieldExists('new_storage_type')
      ->setValue($storage_type);
    $assert_session->fieldExists('label')
      ->setValue($new_field_label);
    $assert_session->fieldExists('field_name')
      ->setValue($new_field_name);
    $assert_session->buttonExists('edit-submit')
      ->press();

    // Make sure that we moved to the field settings page.
    // And submit default settings (display and upload destination).
    $assert_session->buttonExists('Save field settings')
      ->press();

    // Make sure that we moved to the field settings page.
    $assert_session->pageTextContains($new_field_label . ' settings');

    // Test update of visualisation source using a csv file.
    $assert_session->fieldExists('settings[visualisation_source]')
      ->setValue($visualisation_source_test);
    // Test saving of settings.
    $assert_session->buttonExists('Save settings')
      ->press();

    // Make sure that all field settings have been saved.
    $assert_session->pageTextContains('Saved ' . $new_field_label . ' configuration');
  }

}
