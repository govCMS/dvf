<?php

namespace Drupal\Tests\dvf\Functional\Traits;

/**
 * Trait DvfNodeTrait.
 *
 * Provide helper methods to deal manage nodes during testing.
 *
 * @package Drupal\Tests\dvf\Functional\Traits
 */
trait DvfNodeTrait {

  /**
   * Creates a test instance of a node.
   *
   * @param string $bundle
   *   Name of node bundle, defaults to 'page'.
   * @param array $node_config
   *   Optional node config to be used.
   *
   * @return int
   *   Id of created node.
   */
  public function createTestNode($bundle = 'page', array $node_config = []) {
    $node_config = array_merge(['type' => $bundle], $node_config);
    $node = $this->drupalCreateNode($node_config);
    $nid = $node->id();
    $node->save();

    // Reset cache to make sure tha node is ready to be retrieved/used.
    $node_storage = $this->container->get('entity_type.manager')->getStorage('node');
    $node_storage->resetCache([$nid]);

    return $nid;
  }

  /**
   * Retrieves a node using supplied ID.
   *
   * @param int $nid
   *   Unique node id.
   *
   * @return \Drupal\node\Entity\Node
   *   Returns a node entity.
   */
  public function getNodeById($nid) {
    $node_storage = $this->container->get('entity_type.manager')->getStorage('node');
    return $node_storage->load($nid);
  }

}
