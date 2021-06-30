<?php

namespace Drupal\Tests\dvf\Functional\Traits;

use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\file\Entity\File;

/**
 * Trait DvfFileTrait.
 *
 * Provides handy functions for file manipulations
 * required by dvf_file field testing.
 *
 * @package Drupal\Tests\dvf\Functional\Traits
 */
trait DvfFileTrait {
  /**
   * Path to sample data files used for testing.
   *
   * @var string
   */
  protected $dvfSampleDirPath = '/tests/src/sample_data';

  /***
   * List of fields used by sample data.
   *
   * @var array
   */
  protected $sampleFileFields = [
    '2001',
    '2002',
    '2003',
    'Fruits',
  ];

  /**
   * Retrieves the full path of a dvf sample data file.
   *
   * @param string $filename
   *   Name of the sample file.
   *
   * @return string
   *   Full path to file.
   */
  protected function getDvfSampleFilePath($filename) {
    return $this->getDvfSampleDirFullPath() . '/' . $filename;
  }

  /**
   * Gets the full path to sample data directory.
   *
   * @return string
   *   Full path to directory.
   */
  protected function getDvfSampleDirFullPath() {
    return \Drupal::root() . '/' . drupal_get_path('module', 'dvf') . $this->dvfSampleDirPath;
  }

  /**
   * Gets a test sample file as File entity.
   *
   * Uses file from tests directory, copy to public folder
   * and returns as File entity ready to be attached to a field.
   *
   * @param string $sample_file_name
   *   Name of sample file to be used.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   Sample File entity.
   */
  protected function getSampleFile($sample_file_name) {
    // Configure DVF file field.
    // Upload file.
    $file_path = $this->getDvfSampleFilePath($sample_file_name);

    // Copy sample file to public:// directory.
    /** @var \Drupal\Core\File\FileSystemInterface $file_system */
    $file_system = \Drupal::service('file_system');
    $file_system->copy($file_path, PublicStream::basePath());

    // Create test file to upload.
    return File::create([
      'uri' => 'public://' . $sample_file_name,
      'name' => $sample_file_name,
      'filesize' => filesize('public://' . $sample_file_name),
    ]);
  }

}
