<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

class MailController extends Controller
{
    public function mail(Request $request) {

        $data = ['name' => "Arunkumar"];

        $view = 'mail'; // Substitua 'mail' pelo nome da view do e-mail, se necessário
        $subject = 'Test Mail from Selva';
        $recipientEmail = 'kellykemel@gmail.com';
        $recipientName = 'Arunkumar';
        $senderEmail = 'selva@snamservices.com';
        $senderName = 'Selvakumar';

        Mail::send($view, $data, function ($message) use ($subject, $recipientEmail, $recipientName, $senderEmail, $senderName) {
            $message->to($recipientEmail, $recipientName)->subject($subject);
            $message->from($senderEmail, $senderName);
        });

        return "Email Sent. Check your inbox.";
    }
}
