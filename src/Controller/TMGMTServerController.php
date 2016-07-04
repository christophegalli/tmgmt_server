<?php

namespace Drupal\tmgmt_server\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\tmgmt\Entity\JobItem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Serialization\Json;
use Drupal\tmgmt\Entity\Job;
use Drupal\tmgmt_local\Entity\LocalTask;
use Drupal\tmgmt_server\Entity\RemoteSource;

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
  public function saveRemoteSource (array $job_data) {
    /** @var  Job $job */
    /** @var  JobItem $job_item */
    
    foreach($job_data['items'] as $key => $item) {
      $item['cid'] = 0;
      $item['source_language'] = $job_data['source_language'];
      $item['target_language'] = $job_data['target_language'];
      $item['comment'] = $job_data['comment'];
      $item['uid'] = 1;
      $item['data'] = serialize($item['data']);

      RemoteSource::create($item)->save();
    }
  }

  
  /**
   * Addtranslation.
   *
   * @return string
   *   Return result code.
   *   If successful, return relation table in body.
   */
  public function translationJob (Request $Request) {

    $job_data = [
      'from' => $Request->get('from'),
      'to' => $Request->get('to'),
      'items' => $Request->get('items'),
      'comment' => $Request->get('comment'),
    ];

    $job = $this->saveRemoteSource($job_data);
    
    
    
    $response['test'] = $job_data;
    return  new JsonResponse($response);

  }
}
