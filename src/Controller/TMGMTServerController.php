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
   * Create Job from data transferred by the client
   * @param array $job_data
   */
  public function createJobFromData (array $job_data) {
    /** @var  Job $job */
    /** @var  JobItem $job_item */
    
    $job =  Job::create(array(
      'uid' => 0,
      'source_language' => $job_data['from'],
      'target_language' => $job_data['to'],
      'label' => $job_data['label'],
    ));

    $job->save();

    foreach($job_data['items'] as $key => $one_item) {
      $job_item = $job->addItem($one_item['plugin'], $one_item['item_type'], $one_item['item_id']);
      //$job_item->set('unserilizedData', $one_item['data']);
    }
    
    return $job;
  }
  
  /**
   * Addtranslation.
   *
   * @return string
   *   Return Hello string.
   */
  public function addRemoteTranslation (Request $Request) {

    $job_data = [
      'from' => $Request->get('from'),
      'to' => $Request->get('to'),
      'label' => $Request->get('label') . ' remote',
      'items' => $Request->get('items'),
    ];

    $job = $this->createJobFromData($job_data);

    
    $response['test'] = $job_data['items'];
    return  new JsonResponse($response);

  }

}
