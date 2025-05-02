<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/chatbot')]
class ChatbotController extends AbstractController
{
    private const OPENROUTER_API_KEY = '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';
    private const OPENROUTER_API_URL = 'https://openrouter.ai/api/v1/chat/completions';
    private const DEFAULT_MODEL = 'deepseek/deepseek-chat-v3-0324:free';
    
    #[Route('/api/chat', name: 'app_chatbot_api', methods: ['POST'])]
    public function chat(Request $request): JsonResponse
    {
        // Get the message from the request
        $data = json_decode($request->getContent(), true);
        $userMessage = $data['message'] ?? null;
        
        if (!$userMessage) {
            return $this->json(['error' => 'No message provided'], 400);
        }
        
        // Prepare the request to OpenRouter API
        $requestData = [
            'model' => self::DEFAULT_MODEL,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $userMessage
                ]
            ]
        ];
        
        // Initialize cURL session
        $ch = curl_init(self::OPENROUTER_API_URL);
        
        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . self::OPENROUTER_API_KEY
        ]);
        
        // Execute cURL request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        // Check for errors
        if (curl_errno($ch) || $httpCode !== 200) {
            $error = curl_error($ch);
            curl_close($ch);
            return $this->json([
                'error' => 'API request failed',
                'details' => $error,
                'httpCode' => $httpCode
            ], 500);
        }
        
        // Close cURL session
        curl_close($ch);
        
        // Parse the response
        $result = json_decode($response, true);
        
        // Extract the bot's response
        $botResponse = $result['choices'][0]['message']['content'] ?? 'Sorry, I could not generate a response.';
        
        return $this->json([
            'response' => $botResponse
        ]);
    }
}