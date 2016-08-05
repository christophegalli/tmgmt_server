<?php

namespace Drupal\tmgmt_server\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

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
    $entity = $this->entity;

    // Create keys and add them if not available.
    if ($entity->secret->value == '') {
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

}
