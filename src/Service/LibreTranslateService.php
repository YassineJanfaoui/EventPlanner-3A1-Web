<?php
namespace App\Service;

use GuzzleHttp\Client;

class LibreTranslateService
{
    private $client;
    private $apiUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiUrl = 'https://libretranslate.de/translate';
    }

    public function translate($text, $targetLanguage = 'fr', $sourceLanguage = 'en')
    {
        $response = $this->client->post($this->apiUrl, [
            'json' => [
                'q' => $text,
                'source' => $sourceLanguage,
                'target' => $targetLanguage
            ]
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        return $data['translatedText'];
    }
}
