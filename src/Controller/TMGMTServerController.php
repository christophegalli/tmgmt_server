<?php

namespace Drupal\tmgmt_server\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\tmgmt\Entity\JobItem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Serialization\Json;
use Drupal\tmgmt\Entity\Job;
use Drupal\tmgmt_local\Entity\LocalTask;

/**
 * Class TMGMTServerController.
 *
 * @package Drupal\tmgmt_server\Controller
 */
class TMGMTServerController extends ControllerBase {

  /**
   * Addtranslation.
   *
   * @return string
   *   Return Hello string.
   */
  public function addRemoteTranslation(Request $Request) {
  /** @var  Job $job */
  /** @var  JobItem $job_item */

    $from = $Request->get('from');
    $to = $Request->get('to');
    $label = $Request->get('label') . ' remote';

    $job =  Job::create(array(
      'uid' => 0,
      'source_language' => $from,
      'target_language' => $to,
      'label' => $label,
    ));

    $job->save();

    $items_data = $Request->request->get('items');
    foreach($items_data as $key => $one_item) {
      $job_item = $job->addItem($one_item['plugin'], $one_item['item_type'], $one_item['item_id']);
      //$job_item->set('unserialized_data', $one_item['data']);
    }
    
    $response['job'] = $job->getData;
    return  new JsonResponse($response);

  }

}
