<?php

namespace core;

use core\plugin\mail\PHPMailer;
use enum\graph;

class mail
{

  public static function simpleMail($email, $subject = "", $htmlMessage = "")
  {
    $htmlMessage = wordwrap($htmlMessage, 70);

    $message = "
    <html>
    <head>
    <title>" . $subject . "</title>
    </head>
    <body style=border-width:1px;border-color:lightgray;border-style:solid;font-size:1.3em;border-radius:0.5em;padding:1.2em>
    " . $htmlMessage . "
    </body>
    </html>
    ";

    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Whizz <" . graph::company_email . "> \r\n";
    $mail = mail($email, $subject, $message, $headers);

    if ($mail) {
      return array("error" => false, "errorMessage" => "", "message" => "Message sent");
    } else {
      return array("error" => true, "errorMessage" => "Email failed to deliver. Please contact our support team");
    }
  }


  public static function anyMail(string $to, string $subject, string $htmlBody, $attachmentLink = 'array' | 'string')
  {

    $message = '<html><body><div style=background-color: #f2f2f2; padding: 7px; margin: 0px;>';
    $message .= '<div style=padding: 0px; margin:0px;><div style=background-color: #f8f9fa; padding: 14px; margin: 0px;>';
    $message .= "<img src='" . graph::logo . "' alt='Whizz logo' style=max-height: 60px; display: block;/></div>";
    $message .= '<div style=padding: 0px; margin: 0px; font-size: 15px; color: #2b2f33; line-height: 1.5;>';
    $message .= '<div style=padding: 14px 14px 36px 14px; border-width: 7px 7px 0 7px; border-style: solid; border-color: #f8f9fa;>';

    $message .= $htmlBody;

    $message .= '</div><div style=background-color:#f8f7f2; font-size: 12px; margin: 0px; text-align:center; border-radius: 0px 0px 10px 10px; padding: 22px; border-top: 2px solid #f8f9fa; >';

    $message .= '<div style=display: flex; justify-content: center; margin-bottom: 21px>';
    $message .= "<a href='" . graph::apple_url . "'><img src='" . graph::apple . "' alt='Get on AppleStore' style='max-height: 40px; display: block; margin-right:1em;' /></a>";
    $message .= "<a href='" . graph::google_url . "'><img src='" . graph::google . "' alt='Get on GooglePlay' style='max-height: 40px; display: block;' /></a>";
    $message .= '</div>';


    $message .=  '<p style=margin-bottom: 14px;> ' . graph::company_address . ' </p>';


    $message .=  '<div style=display: block; margin: 21px 0; text-align: center;>';



    $message .= "<a href='" . graph::facebook . "'><img src='https://childsvoice.org/wp-content/uploads/2016/01/facebook-logo-200x200.png' alt='FB' style=max-height: 25px; border-radius:5px; display: inline-block; margin-right:1em; /></a>";
    $message .= "<a href='" . graph::twitter . "'><img src='https://awmedu.com/wp-content/uploads/2016/08/twitter-icon-big.png' alt='TW' style=max-height: 28px; display: inline-block; border-radius:5px; margin-right:1em; /></a>";
    $message .= "<a href='" . graph::linkedin . "'><img src='http://pngimg.com/uploads/linkedIn/small/linkedIn_PNG16.png' alt='IN' style=max-height: 25px; display: inline-block; border-radius:5px; />";



    $message .= '</div>';



    $message .= '<div style=text-align:center;>';
    $message .= '<a href="' . graph::url . '">Home</a> | <a href="' . graph::url . '/login">Login</a> | <a href="' . graph::url . '/privacy">Privacy</a>';
    $message .= '</div>';



    $message .= '<p>Copyright @ Whizz, <br/> All rights reserved.</p>';



    $message .= '</div></div></div></body></html>';



    $message = wordwrap($message, 70);

    if ($attachmentLink) {
      $email = new PHPMailer();
      $email->SMTPDebug = 2;
      $email->setFrom(graph::company_email, "Whizz");
      $email->Subject = $subject;
      $email->isHTML(true);
      $email->msgHTML($message);
      $email->addAddress($to);

      if (is_array($attachmentLink)) {
        foreach ($attachmentLink as $link) {
          $email->AddAttachment($link);
        }
      } else {
        $email->AddAttachment($attachmentLink);
      }
      if (@$email->send()) {
        return true;
      } else {
        return true;
      }
    } else {
      $headers = "MIME-Version: 1.0" . "\r\n";
      $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

      // FROM
      $headers .= 'From: Whizz <' . graph::company_email . '>' . "\r\n";

      if (@mail($to, $subject, $message, $headers)) {
        return true;
      } else {
        return false;
      }
    }
  }


  private function template()
  {

    //Template 1

    $message = '<html><body><div style="background-color: #f2f2f2; padding: 7px; margin: 0px;">';
    $message .= '<div style="padding: 0px; margin:0px;"><div style="background-color: #f8f9fa; padding: 14px; margin: 0px;">';
    $message .= "<img src='https://apluswebmaker.com/apple-touch-icon.png' alt='Website Change Request' style='max-height: 60px; display: block;' /></div>";
    $message .= '<div style="padding: 14px; margin: 0px; font-size: 15px; color: #2b2f33; line-height: 1.5; border-radius: 0px 0px 10px 10px; border-width: 7px; border-style: solid; border-color: #f8f9fa;">';
    $message .= '<div style="padding-bottom: 36px">';


    // $message .= $body;


    $message .= '</div><div style="text-align:center; font-size:12px; padding: 16px 22px 0px 22px; border-top: 2px solid #f8f9f2;" >';


    // $message .= '<div style="display: flex; justify-content: center; margin-bottom: 21px">';
    // $message .= "<a href='google.com'><img src='https://authentic.diimtech.com/static/media/apple.b0b22820.png' alt='Get on AppleStore' style='max-height: 40px; display: block; margin-right:1em;' /></a>";
    // $message .= "<a href='google.com'><img src='https://authentic.diimtech.com/static/media/google.866c54a3.png' alt='Get on GooglePlay' style='max-height: 40px; display: block;' /></a>";
    // $message .= '</div>';


    $message .=  '<p style="margin-bottom: 14px;"> 68 Johnston street, Sunnyside Gauteng, 0002. Pretoria, South Africa.</p>';


    $message .=  '<div style="display: block; margin: 21px 0; text-align: center;">';

    $message .= "<a href='facebook.com'><img src='https://childsvoice.org/wp-content/uploads/2016/01/facebook-logo-200x200.png' alt='FB' style='max-height: 25px; border-radius:5px; display: inline-block; margin-right:1em;' /></a>";
    $message .= "<a href='twitter.com'><img src='https://awmedu.com/wp-content/uploads/2016/08/twitter-icon-big.png' alt='TW' style='max-height: 28px; display: inline-block; border-radius:5px; margin-right:1em;' /></a>";
    $message .= "<a href='linkedin.com'><img src='http://pngimg.com/uploads/linkedIn/small/linkedIn_PNG16.png' alt='IN' style='max-height: 25px; display: inline-block; border-radius:5px;' />";

    $message .= '</div>';



    $message .= '<div style="text-align:center;">';
    $message .= '<a href="authenticpass.com">Home</a> | <a href="authenticpass.com">Login</a> | <a href="authenticpass.com">Privacy</a>';
    $message .= '</div>';



    $message .= '<p>Copyright @ Apluswebmaker, <br/> All rights reserved.</p>';



    $message .= '</div></div></div></body></html>';
  }
}
