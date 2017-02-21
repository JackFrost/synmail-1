<?php

namespace Drupal\synmail\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements the form controller.
 */
class Settings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'synmail_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['synmail.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('synmail.settings');

    $form['general'] = [
      '#type' => 'details',
      '#title' => $this->t('General settings'),
      '#open' => TRUE,
    ];

    $form["general"]['phpmail'] = array(
      '#title' => $this->t('Использовать свою функцию phpmail'),
      '#type' => 'checkbox',
      '#maxlength' => 20,
      '#required' => FALSE,
      '#size' => 15,
      '#default_value' => $config->get('phpmail'),
      '#description' => $this->t('Там правильная работа с кирилическим
        "Frome",<br /> <strong>лучше всегда её вклчать</strong>.'),
    );
    $form['general']['from'] = [
      '#title' => $this->t('From'),
      '#default_value' => $config->get('from'),
      '#type' => 'textfield',
      '#description' => $this->t('От кого слать письма,
        напр: Сайт @example', ['@example' => '<webmaster@insaitov.ru>']),
    ];
    $form["general"]['html'] = array(
      '#title' => $this->t('Послыть html, а не txt'),
      '#type' => 'checkbox',
      '#maxlength' => 20,
      '#required' => FALSE,
      '#size' => 15,
      '#default_value' => $config->get('html'),
      '#description' => $this->t('Добавляет метку в заголовок,
        использовать в сочетании с <strong>включенной</strong>
        <a href="/admin/structure/contact/settings">html настройкой</a>
        модуля контакт <br />
        и галочкой <strong>Использовать свою функцию phpmail</strong>
      '),
    );
    $form["general"]['tpl'] = array(
      '#title' => $this->t('Использовать шаблон'),
      '#description' => $this->t('synmail.html.twig можно скопировть из
        /modules/custom/synmail/templates'),
      '#type' => 'checkbox',
      '#maxlength' => 20,
      '#required' => FALSE,
      '#size' => 15,
      '#default_value' => $config->get('tpl'),
    );
    $form['general']['emails'] = [
      '#title' => $this->t('Кому посылать письмо'),
      '#default_value' => $config->get('emails'),
      '#description' => $this->t('По 1 email на строчку'),
      '#type' => 'textarea',
    ];
    $form["general"]['debug'] = array(
      '#title' => $this->t('Debug'),
      '#type' => 'checkbox',
      '#maxlength' => 20,
      '#required' => FALSE,
      '#size' => 15,
      '#default_value' => $config->get('debug'),
      '#description' => $this->t('Режим отладки'),
    );
    return parent::buildForm($form, $form_state);
  }

  /**
   * Implements form validation.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * Implements a form submit handler.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $config = $this->config('synmail.settings');
    $config
      ->set('tpl', $form_state->getValue('tpl'))
      ->set('from', $form_state->getValue('from'))
      ->set('phpmail', $form_state->getValue('phpmail'))
      ->set('html', $form_state->getValue('html'))
      ->set('debug', $form_state->getValue('debug'))
      ->set('emails', $form_state->getValue('emails'))
      ->save();
  }

}
