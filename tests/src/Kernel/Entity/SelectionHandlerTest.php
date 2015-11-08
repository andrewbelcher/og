<?php

/**
 * @file
 * Contains Drupal\Tests\og\Kernel\Entity\SelectionHandlerTest.
 */

namespace Drupal\Tests\og\Kernel\Entity;

use Drupal\Component\Utility\Unicode;
use Drupal\node\Entity\NodeType;
use Drupal\KernelTests\KernelTestBase;
use Drupal\og\Og;

/**
 * Tests entity reference selection plugins.
 *
 * @group og
 */
class SelectionHandlerTest extends KernelTestBase {

  /**
   * The selection handler.
   *
   * @var \Drupal\og\Plugin\EntityReferenceSelection\OgSelection.
   */
  protected $selectionHandler;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['system', 'user', 'field', 'entity_reference', 'node', 'og'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Add membership and config schema.
    $this->installConfig(['og']);
    $this->installEntitySchema('og_membership');
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installSchema('system', 'sequences');

    // Setting up variables.
    $group_type = Unicode::strtolower($this->randomMachineName());
    $group_content_type = Unicode::strtolower($this->randomMachineName());

    // Create a group.
    NodeType::create([
      'type' => $group_type,
      'name' => $this->randomString(),
    ])->save();

    // Create a group content type.
    NodeType::create([
      'type' => $group_content_type,
      'name' => $this->randomString(),
    ])->save();

    // Define the group content as group.
    Og::groupManager()->addGroup('bundles', $group_type);

    // Add og audience field to group content.
    Og::CreateField(OG_AUDIENCE_FIELD, 'node', $group_content_type);

    // Get the storage of the field.
    $this->selectionHandler = Og::getSelectionHandler('node', $group_content_type, OG_AUDIENCE_FIELD);
  }

  /**
   * Testing the OG manager selection handler.
   *
   * We need to verify that the manager selection handler will use the default
   * selection manager of the entity which the audience field referencing to.
   *
   * i.e: When the field referencing to node, we need verify we got the default
   * node selection handler.
   */
  public function testSelectionHandler() {
    $this->assertEquals(get_class($this->selectionHandler->getSelectionHandler()), 'Drupal\node\Plugin\EntityReferenceSelection\NodeSelection');
    $this->assertEquals($this->selectionHandler->getConfiguration('handler'), 'default:node');
    $this->assertEquals($this->selectionHandler->getConfiguration('target_type'), 'node');
  }

}
