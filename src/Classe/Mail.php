<?php

namespace App\Classe;
use Mailjet\Client;
use Mailjet\Resources;

class Mail{
    private $api_key = "fdb03554573ac7f33d5aac27dd519362";
    private $api_key_secret = "18d6497f0bd803c49ce9ec7a0be74185";

    public function send($to_email, $to_name, $subject, $content){
        $mj = new Client($this->api_key, $this->api_key_secret,true,['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "poussin6494@hotmail.com",
                        'Name' => "La Boutique Française"
                    ],
                    'To' => [
                        [
                            'Email' => $to_email,
                            'Name' => $to_name
                        ]
                    ],
                    'TemplateID' => 3401787,
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables' => [
                        'content'=>$content
                    ]
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        $response->success();
    }

}