<?php

namespace Drupal\tmgmt_server;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the TMGMT Server Client entity.
 *
 * @see \Drupal\tmgmt_server\Entity\TMGMTServerClient.
 */
class TMGMTServerClientAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\tmgmt_server\Entity\TMGMTServerClientInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished tmgmt server client entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published tmgmt server client entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit tmgmt server client entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete tmgmt server client entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add tmgmt server client entities');
  }

}
