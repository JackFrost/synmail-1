<?php

namespace Drupal\synmail\Controller;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Mail\MailFormatHelper;
use Drupal\Core\Site\Settings;

/**
 * Defines the default Drupal mail backend, using PHP's native mail() function.
 */
class PhpMail extends ControllerBase {

  /**
   * Concatenates and wraps the email body for plain-text mails.
   *
   * @param array $message
   *   A message array, as described in hook_mail_alter().
   *
   * @return array
   *   The formatted $message.
   */
  public static function format($message) {
    // Join the body array into one string.
    $config = \Drupal::config('node_city.settings');
    $body = $message['body'];

    $formater = new MailFormatHelper();
    $body = implode("\n\n", $body);

    $config = \Drupal::config('synmail.settings');
    if (!$config->get('html')) {
      // Convert any HTML to plain-text.
      $body = $formater::htmlToText($body);
      // Wrap the mail body for sending.
      $body = $formater::wrapMail($body);
      // Note: email uses CRLF for line-endings. PHP's API requires LF on Unix.
    }
    $line_endings = Settings::get('mail_line_endings', PHP_EOL);
    $body = preg_replace('@\r?\n@', $line_endings, $body);

    return $body;
  }

  /**
   * Sends an email message.
   *
   * @param array $message
   *   A message array, as described in hook_mail_alter().
   *
   * @return bool
   *   TRUE if the mail was successfully accepted, otherwise FALSE.
   *
   * @see http://php.net/manual/function.mail.php
   * @see \Drupal\Core\Mail\MailManagerInterface::mail()
   */
  public static function mail(array $message) {
    // If 'Return-Path' isn't already set in php.ini, we pass it separately
    // as an additional parameter instead of in the header.
    if (isset($message['headers']['Return-Path'])) {
      $return_path_set = strpos(ini_get('sendmail_path'), ' -f');
      if (!$return_path_set) {
        $message['Return-Path'] = $message['headers']['Return-Path'];
        unset($message['headers']['Return-Path']);
      }
    }
    $mimeheaders = [];
    foreach ($message['headers'] as $name => $value) {
      if ($name == "From") {
        $mimeheaders[] = $name . ': ' . $value;
      }
      else {
        $mimeheaders[] = $name . ': ' . Unicode::mimeHeaderEncode($value);
      }
    }
    // Prepare mail commands.
    $mail_subject = Unicode::mimeHeaderEncode($message['subject']);
    $mail_body = self::format($message);

    // For headers, PHP's API suggests that we use CRLF normally,
    // but some MTAs incorrectly replace LF with CRLF. See #234403.
    $mail_headers = implode("\n", $mimeheaders);

    $request = \Drupal::request();

    $additional_headers = isset($message['Return-Path']) ? '-f ' . $message['Return-Path'] : '';

    $config = \Drupal::config('synmail.settings');
    if ($config->get('debug')) {
      dsm([
        $message['to'],
        $mail_subject,
        $mail_body,
        $mail_headers,
        $additional_headers,
      ]
      );
    }
    $mail_result = @mail(
      $message['to'],
      $mail_subject,
      $mail_body,
      $mail_headers,
      $additional_headers
    );

    if (!$mail_result) {
      drupal_set_message('Не удалось отправить сообщение', 'error');
    }
    return $mail_result;
  }

}
