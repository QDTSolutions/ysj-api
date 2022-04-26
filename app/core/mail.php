<?php

//Mail
function lrv_mail($to, $subject, $html, $attachments = [])
{
    global $lrvconfig;
    $mail = new Nette\Mail\Message;
    $mail->setFrom($lrvconfig['mail']['from']);
    //To
    if (is_array($to)) {
        //From
        if (array_key_exists('from', $to)) {
            $to['from'] = (is_array($to['from']) ? $to['from'] : $to['from'] = [$to['from']]);
            foreach ($to['from'] as $correo) {
                $mail->setFrom($correo);
            }
        }
        //To
        if (array_key_exists('to', $to)) {
            $to['to'] = (is_array($to['to']) ? $to['to'] : $to['to'] = [$to['to']]);
            foreach ($to['to'] as $correo) {
                $mail->addTo($correo);
            }
        } else {
            return false;
        }
        //CC
        if (array_key_exists('cc', $to)) {
            $to['cc'] = (is_array($to['cc']) ? $to['cc'] : $to['cc'] = [$to['cc']]);
            foreach ($to['cc'] as $correo) {
                $mail->addCc($correo);
            }
        }
        //BCC
        if (array_key_exists('bcc', $to)) {
            $to['bcc'] = (is_array($to['bcc']) ? $to['bcc'] : $to['bcc'] = [$to['bcc']]);
            foreach ($to['bcc'] as $correo) {
                $mail->addBcc($correo);
            }
        }
    } else {
        $to_arr = explode(',', $to);
        foreach ($to_arr as $_to) {
            $mail->addTo($_to);
        }
    }
    //Subject
    $mail->setSubject($subject);
    //Message
    $mail->setHTMLBody($html);
    //Attachments
    if (count($attachments)) {
        foreach ($attachments as $archivo) {
            $mail->addAttachment($archivo);
        }
    }
    if ($lrvconfig['debug']['environment'] == 'DEV') {
        $mailer = new Nette\Mail\SmtpMailer([
            'host' => 'server1.lrvhost.com',
            'port' => 465,
            'secure' => 'ssl',
            'username' => 'sistemas@lrvhost.com',
            'password' => '-',
        ]);
    } else {
        $mailer = new Nette\Mail\SendmailMailer();
    }
    try {
        $mailer->send($mail);
        return true;
    } catch (Exception $ex) {
        return false;
    }
}
