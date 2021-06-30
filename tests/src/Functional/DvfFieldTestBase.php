<?php

namespace Drupal\Tests\dvf\Functional;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\dvf\Functional\Traits\DvfFileTrait;
use Drupal\Tests\dvf\Functional\Traits\DvfNodeTrait;
use Drupal\Tests\dvf\Functional\Traits\DvfTestTrait;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\Tests\dvf\Functional\Traits\DvfFieldCreationTrait;

/**
 * Base test class providing methods and test setup.
 *
 * Specifically for testing Dvf module's field handling.
 */
abstract class DvfFieldTestBase extends BrowserTestBase {

  use DvfFieldCreationTrait;
  use DvfNodeTrait;
  use DvfTestTrait;
  use DvfFileTrait;

  use StringTranslationTrait;

  use TestFileCreationTrait {
    getTestFiles as drupalGetTestFiles;
  }

  /**
   * Admin user test account.
   *
   * @var \Drupal\user\Entity\User|bool
   */
  protected $adminUser;

  /**
   * Modules to enable during test setup.
   *
   * @var array
   */
  protected static $modules = [
    'file',
    'field',
    'field_ui',
    'node',
    'path',
    'dvf',
    'dvf_csv',
    'dvf_json',
    'dvf_ckan',
    'ckan_connect',
  ];

  /**
   * Permissions to grant to admin.
   *
   * @var array
   */
  protected $permissions = [
    'access administration pages',
    'administer site configuration',
    'view the administration theme',
    'administer content types',
    'administer ckan connect',
    'administer nodes',
    'administer node fields',
    'administer node display',
    'create page content',
    'access content',
    'bypass node access',
    'administer node form display',
    'create page content',
    'edit any page content',
    'view own unpublished content',
    'bypass node access',
  ];

  /**
   * Theme for tests relying on no markup at all or at least no core markup.
   *
   * @var string
   */
  protected $defaultTheme = 'stark';
  /**
   * List of all dvf built-in visualisation styles.
   *
   * Declared statically for backward compatible tests.
   *
   * @var array
   */
  protected $visualisationStyles = [
    'dvf_bar_chart',
    'dvf_gauge_chart',
    'dvf_line_chart',
    'dvf_scatter_plot_chart',
    'dvf_spline_chart',
    'dvf_table',
  ];

  /**
   * Default visualisation style plugin.
   *
   * @var string
   */
  protected $defaultVisualisationStyle = 'dvf_bar_chart';

  /**
   * Setup test dependencies including container and mock.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function setUp(): void {
    parent::setUp();

    // Configure basic page content type.
    $this->drupalCreateContentType(['type' => 'page', 'name' => 'Basic page']);

    // Create admin user, assign permissions and login.
    $this->adminUser = $this->drupalCreateUser($this->permissions);
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Uploads a sample file to a given node via supplied dvf_file field.
   *
   * @param string $sample_file_name
   *   Name of dvf sample file in tests folder.
   * @param string $field_name
   *   Name of dvf_file field present on node entity.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  protected function uploadSampleFileToNode($sample_file_name, $field_name) {
    $this->attachFileToDvfField(
      $this->getSampleFile($sample_file_name),
      $field_name
    );
  }

  /**
   * Attaches a file to a dvf_file field on current entity.
   *
   * Assumes that current page visited by test
   * is a node edit page.
   *
   * @param mixed $file
   *   File entity to be attached to the node entity.
   * @param string $field_name
   *   Name of (test) field to attach file to.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  protected function attachFileToDvfField($file, $field_name) {
    // Build file attachment form field name.
    $file_field_name = 'files[' . $field_name . '_0_file]';
    // Get real path to file to attach.
    $file_path = $this->container->get('file_system')
      ->realpath($file->getFileUri());

    // Make sure that file attachment field exists on current page.
    $page = $this->getSession()->getPage();
    $this->assertSession()->fieldExists($file_field_name);

    // Attach file to field and submit upload.
    $page->attachFileToField($file_field_name, $file_path);
    $this->submitForm([], $this->t('Upload'));
  }

}
