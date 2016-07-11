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
  public function receiveTranslationJob (array $job_data) {
    /** @var  Job $job */
    /** @var  JobItem $job_item */
    /** @var  \Drupal\Core\Database\Transaction $transaction */

    $sources = [];
    $transaction = \Drupal::service('database')->startTransaction();

    foreach($job_data['items'] as $key => $item) {
      $item['cid'] = 0;
      $item['source_language'] = $job_data['from'];
      $item['target_language'] = $job_data['to'];
      $item['uid'] = 1;
      $item['data'] = serialize($item['data']);
      $item['user_agent'] = $job_data['user_agent'];
      $item['langcode'] = $job_data['from'];

      $sources[$key] = RemoteSource::create($item);
      $sources[$key]->save();

    }

    // Create translation job for this translation request.
    $job = Job::create([
      'uid' => 1,
      'source_language' => $job_data['from'],
      'target_language' => $job_data['to'],
      'translator' => 'local',
    ]);

    // This will be saved in the following addItem() call.
    //$job->set('job_comment', $job_data['job_comment']);

    // Create job items for each remote source.
    foreach($sources as $key => $source) {
      $job->addItem('remote', 'tmgmt_server_remote_source', $source->id());
    }

    // Request translation locally.
    if ($job->requestTranslation() === FALSE) {
      $transaction->rollback();
    }

    return $job;
   }

  
  /**
   * TranslationJob.
   *
   * @return string
   *   Return result code.
   *   If successful, return relation table in body.
   */
  public function translationJob (Request $Request) {

    $headers = getallheaders();

    $job_data = [
      'from' => $Request->get('from'),
      'to' => $Request->get('to'),
      'items' => $Request->get('items'),
      'job_comment' => $Request->get('comment'),
      'user_agent' => $headers['User-Agent'],
    ];

    $job_data_json = Json::encode($job_data);
    $job = $this->receiveTranslationJob($job_data);
    
    
    
    $response['test'] = $job_data;
    $response['headers'] = getallheaders();
    $response['cookies'] = $_COOKIE;
    return  new JsonResponse($response);

  }
}
