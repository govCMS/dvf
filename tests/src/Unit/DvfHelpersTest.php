<?php

namespace Drupal\Tests\dvf\Unit;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\GeneratedLink;
use Drupal\Core\Link;
use Drupal\dvf\DvfHelpers;
use Drupal\Tests\UnitTestCase;
use Drupal\Core\DependencyInjection\ContainerBuilder;

/**
 * Tests DvfHelpers.
 *
 * @group dvf
 */
class DvfHelpersTest extends UnitTestCase
{
  /**
   * The Dvf helpers class to test.
   *
   * @var DvfHelpers
   */
  protected $dvf_helpers;

  /**
   * Setup test dependencies including container and mock.
   */
  protected function setUp(): void {
    parent::setUp();

    $this->dvf_helpers = new DvfHelpers();

    // Create dummy Drupal container for DI services.
    $container = new ContainerBuilder();
    \Drupal::setContainer($container);

    // Mock the link generator for testGetHelpPageLink().
    $linkGenerator = $this->createMock('Drupal\Core\Utility\LinkGeneratorInterface');
    // Mock its generateFromLink function by creating link markup using generated Uri.
    $linkGenerator->expects($this->any())
      ->method('generateFromLink')
      ->willReturnCallback(function (Link $link) {
        // Since path validator mock returns null, link has been built with Link::fromInternalUri('base:...')
        $uri = str_replace('base:', '/', $link->getUrl()->getUri());
        $markup = '<a href="' . $uri . '">' . $link->getText() . '</a>';
        $generated_link = new GeneratedLink();
        return $generated_link->setGeneratedLink($markup);
      });
    $container->set('link_generator', $linkGenerator);
    $container->set('path.validator', $this->createMock('Drupal\Core\Path\PathValidatorInterface'));
  }

  /**
   * Test for correct json validation.
   *
   * @param string $input
   *   Input to simulate valid /invalid json.
   * @param string $message
   *   A message to display successful outcome.
   * @param bool $expected
   *   Expected result from function call.
   *
   * @cover ::validateJson()
   * @dataProvider providerTestValidateJson()
   */
  public function testValidateJson(string $input, string $message, bool $expected)
  {
    $output = $this->dvf_helpers->validateJson($input);

    $this->expectedEqualOutput($message, $expected, $output);
  }

  /**
   * Provider for testValidateJson().
   */
  public function providerTestValidateJson()
  {
    return [[
      'input' => '[{"2001": 30,"2002": 40,"2003": 50,"Fruits": "Apple"}]',
      'message' => 'Provided json is valid',
      'expected' => TRUE,
      ],
      [
        'input' => '[{{"2001": 30,"2002": 40,"2003": 50,"Fruits": "Apple"}]',
        'message' => 'Provided json is valid',
        'expected' => FALSE,
      ]
    ];
  }

  /**
   * Tests correct transformation of text to machine name.
   *
   * @covers ::transformMachineName()
   */
  public function testTransformMachineName()
  {
    $message = 'Text transformed to machine name';
    $input = 'Non Machine Name';
    $expected = 'non_machine_name';
    $output = $this->dvf_helpers->transformMachineName($input);

    $this->expectedEqualOutput($message, $expected, $output);
  }

  /**
   * Tests that "help page" link can be retrieved.
   *
   * @covers ::getHelpPageLink()
   */
  public function testGetHelpPageLink()
  {
    $message = 'Help page page link rendered';
    $template_name = 'label-overrides';
    $base_path = '/dvf/help/';
    $expected = '<span class="dvf-admin-popup"><a href="' . $base_path . $template_name . '">Help</a> &#x29c9;</span>';

    /** @var FormattableMarkup $output */
    $result = $this->dvf_helpers->getHelpPageLink($template_name);
    $output = $result->__toString();

    $this->expectedEqualOutput($message, $expected, $output);
  }

  /**
   * Tests that empty elements are removed from nested array.
   *
   * @covers ::filterArrayRecursive()
   */
  public function testFilterArrayRecursive()
  {
    $message = 'Empty elements removed from nested array';
    $input = [
      'to_keep' => '1',
      'to_keep_nested' => ['2'],
      'to_remove' => '',
      'to_remove_nested' => [],
    ];
    $expected = [
      'to_keep' => '1',
      'to_keep_nested' => ['2'],
    ];
    $output = $this->dvf_helpers->filterArrayRecursive($input);

    $this->expectedEqualOutput($message, $expected, $output);
  }

  /**
   * Handy function to run equal assertion against two values.
   *
   * Compares two values and display both values if different.
   *
   * @param string $message
   *   The message to render.
   * @param mixed $expected
   *   Expected value that can be compared with '===' operator.
   * @param mixed $output
   *   Output value that can be compared with '===' operator.
   */
  protected function expectedEqualOutput($message, $expected, $output)
  {
    // Display output vs expected in case of failure.
    if ($output !== $expected) {
      print_r([
        'output' => $output,
        'expected' => $expected,
      ]);
    }

    $this->assertTrue(
      $output === $expected,
      $message
    );
  }

}
