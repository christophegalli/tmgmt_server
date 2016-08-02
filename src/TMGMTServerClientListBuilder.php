<?php

namespace Drupal\tmgmt_server;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of TMGMT Server Client entities.
 *
 * @ingroup tmgmt_server
 */
class TMGMTServerClientListBuilder extends EntityListBuilder {

  use LinkGeneratorTrait;

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('TMGMT Server Client ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\tmgmt_server\Entity\TMGMTServerClient */
    $row['id'] = $entity->id();
    $row['name'] = $this->l(
      $entity->label(),
      new Url(
        'entity.tmgmt_server_client.edit_form', array(
          'tmgmt_server_client' => $entity->id(),
        )
      )
    );
    return $row + parent::buildRow($entity);
  }

}
