<?php

namespace Drupal\Tests\libraries\Kernel\ExternalLibrary\Asset;

use Drupal\libraries\ExternalLibrary\Asset\AssetLibrary;
use Drupal\libraries\ExternalLibrary\Exception\LibraryClassNotFoundException;
use Drupal\libraries\ExternalLibrary\Exception\LibraryDefinitionNotFoundException;
use Drupal\Tests\libraries\Kernel\ExternalLibrary\TestLibraryFilesStream;
use Drupal\Tests\libraries\Kernel\LibraryKernelTestBase;

/**
 * Tests that external asset libraries are registered as core asset libraries.
 *
 * @group libraries
 */
class AssetLibraryTest extends LibraryKernelTestBase {

  /**
   * {@inheritdoc}
   *
   * \Drupal\libraries\Extension requires system_get_info() which is in
   * system.module.
   */
  public static $modules = ['libraries', 'libraries_test', 'system'];

  /**
   * The Drupal core library discovery.
   *
   * @var \Drupal\Core\Asset\LibraryDiscoveryInterface
   */
  protected $libraryDiscovery;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->libraryDiscovery = $this->container->get('library.discovery');
  }

  /**
   * Tests that library metadata is correctly gathered.
   */
  public function testMetadata() {
    try {
      /** @var \Drupal\libraries\ExternalLibrary\Asset\AssetLibrary $library */
      $library = $this->externalLibraryRegistry->getLibrary('test_asset_library');
      $this->assertInstanceOf(AssetLibrary::class, $library);

      $this->assertEquals('test_asset_library', $library->getId());
      $expected = ['test_asset_library' => [
        'version' => '1.0.0',
        'css' => ['base' => ['http://example.com/example.css' => []]],
        'js' => ['http://example.com/example.js' => []],
        'dependencies' => [],
      ]];
      $this->assertEquals($expected, $library->getAttachableAssetLibraries());
    }
    catch (LibraryClassNotFoundException $exception) {
      $this->fail();
    }
    catch (LibraryDefinitionNotFoundException $exception) {
      $this->fail();
    }
  }

  /**
   * Tests that a remote asset library is registered as a core asset library.
   *
   * @see \Drupal\libraries\Extension\Extension
   * @see \Drupal\libraries\Extension\ExtensionHandler
   * @see \Drupal\libraries\ExternalLibrary\Asset\AssetLibrary
   * @see \Drupal\libraries\ExternalLibrary\Asset\AssetLibraryTrait
   * @see \Drupal\libraries\ExternalLibrary\ExternalLibraryManager
   * @see \Drupal\libraries\ExternalLibrary\ExternalLibraryTrait
   * @see \Drupal\libraries\ExternalLibrary\Registry\ExternalLibraryRegistry
   */
  public function testAssetLibraryRemote() {
    $library = $this->libraryDiscovery->getLibraryByName('libraries', 'test_asset_library');
    $expected = [
      'version' => '1.0.0',
      'css' => [[
        'weight' => -200,
        'group' => 0,
        'type' => 'external',
        'data' => 'http://example.com/example.css',
        'version' => '1.0.0',
      ]],
      'js' => [[
        'group' => -100,
        'type' => 'external',
        'data' => 'http://example.com/example.js',
        'version' => '1.0.0',
      ]],
      'dependencies' => [],
      'license' => [
        'name' => 'GNU-GPL-2.0-or-later',
        'url' => 'https://www.drupal.org/licensing/faq',
        'gpl-compatible' => TRUE,
      ]
    ];
    $this->assertEquals($expected, $library);
  }

  /**
   * Tests that a local asset library is registered as a core asset library.
   */
  public function testAssetLibraryLocal() {
    $this->container->set('stream_wrapper.asset_libraries', new TestLibraryFilesStream(
      $this->container->get('module_handler'),
      $this->container->get('string_translation'),
      'assets/vendor'
    ));
    $this->libraryDiscovery->clearCachedDefinitions();
    $library = $this->libraryDiscovery->getLibraryByName('libraries', 'test_asset_library');
    $expected = [
      'version' => '1.0.0',
      'css' => [[
        'weight' => -200,
        'group' => 0,
        'type' => 'file',
        'data' => $this->modulePath . '/tests/assets/vendor/test_asset_library/example.css',
        'version' => '1.0.0',
      ]],
      'js' => [[
        'group' => -100,
        'type' => 'file',
        'data' => $this->modulePath . '/tests/assets/vendor/test_asset_library/example.js',
        'version' => '1.0.0',
        'minified' => FALSE,
      ]],
      'dependencies' => [],
      'license' => [
        'name' => 'GNU-GPL-2.0-or-later',
        'url' => 'https://www.drupal.org/licensing/faq',
        'gpl-compatible' => TRUE,
      ]
    ];
    $this->assertEquals($expected, $library);
  }

}
