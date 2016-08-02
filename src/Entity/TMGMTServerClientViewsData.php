<?php

namespace Drupal\tmgmt_server\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for TMGMT Server Client entities.
 */
class TMGMTServerClientViewsData extends EntityViewsData implements EntityViewsDataInterface {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['tmgmt_server_client']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('TMGMT Server Client'),
      'help' => $this->t('The TMGMT Server Client ID.'),
    );

    return $data;
  }

}
