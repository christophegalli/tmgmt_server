<?php

namespace Drupal\tmgmt_server\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining TMGMT Server Client entities.
 *
 * @ingroup tmgmt_server
 */
interface TMGMTServerClientInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the TMGMT Server Client name.
   *
   * @return string
   *   Name of the TMGMT Server Client.
   */
  public function getName();

  /**
   * Sets the TMGMT Server Client name.
   *
   * @param string $name
   *   The TMGMT Server Client name.
   *
   * @return \Drupal\tmgmt_server\Entity\TMGMTServerClientInterface
   *   The called TMGMT Server Client entity.
   */
  public function setName($name);

  /**
   * Gets the TMGMT Server Client creation timestamp.
   *
   * @return int
   *   Creation timestamp of the TMGMT Server Client.
   */
  public function getCreatedTime();

  /**
   * Sets the TMGMT Server Client creation timestamp.
   *
   * @param int $timestamp
   *   The TMGMT Server Client creation timestamp.
   *
   * @return \Drupal\tmgmt_server\Entity\TMGMTServerClientInterface
   *   The called TMGMT Server Client entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the TMGMT Server Client published status indicator.
   *
   * Unpublished TMGMT Server Client are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the TMGMT Server Client is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a TMGMT Server Client.
   *
   * @param bool $published
   *   TRUE to set this TMGMT Server Client to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\tmgmt_server\Entity\TMGMTServerClientInterface
   *   The called TMGMT Server Client entity.
   */
  public function setPublished($published);

}
