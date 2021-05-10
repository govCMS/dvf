<?php

namespace Drupal\Tests\dvf\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\Core\DependencyInjection\ContainerBuilder;

/**
 * Tests dvf.
 *
 * @group doghouse
 */
class DvfTest extends UnitTestCase
{
  /**
   * Test data.
   *
   * @var array
   */
  protected $testData = [
      //users info
      [
        'Username' => 'name1',
        'Id' => 'id1',
        'First name' => 'firstname1',
        'Last name' => 'last1',
      ],
      [
        'Username' => 'name2',
        'Id' => 'id2',
        'First name' => 'firstname2',
        'Last name' => 'last2',
      ],
      [
        'Username' => 'name3',
        'Id' => 'id3',
        'First name' => 'firstname3',
        'Last name' => 'last3',
      ]
  ];

  protected function setUp() {
    parent::setUp();

    $container = new ContainerBuilder();
    \Drupal::setContainer($container);
  }

  /**
   * Test input data.
   */
  public function testInputData()
  {
    $output = $this->testData;
    $this->assertIsArray($output);
  }
}
