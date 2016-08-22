<?php

namespace Drupal\tmgmt_server\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\tmgmt_server\Entity\TMGMTServerRemoteSource;

/**
 * Form controller for TMGMT Server Client edit forms.
 *
 * @ingroup tmgmt_server
 */
class TMGMTServerClientForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\tmgmt_server\Entity\TMGMTServerClient */
    $form = parent::buildForm($form, $form_state);
    $entity = $this->entity;



    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var TMGMTServerRemoteSource $entity */
    $entity = $this->entity;

    // Create keys and add them if not available.
    if ($entity->client_secret->value == '') {
      $entity->setKeys();
    }

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:

        drupal_set_message($this->t('Created the %label TMGMT Server Client.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label TMGMT Server Client.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.tmgmt_server_client.canonical', ['tmgmt_server_client' => $entity->id()]);
  }
  /**
   * {@inheritdoc}
   */

  public function form(array $form, FormStateInterface $form_state) {

    $form = parent::form($form, $form_state);

    $account = \Drupal::currentUser()->getAccount();
    if (!$account->hasPermission('administer tmgmt server client entities')) {
      $form['owner']['#disabled'] = TRUE;
    }
    return $form;
  }

}
