<?php
namespace App\Controller;

use App\Service\LibreTranslateService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TranslationController extends AbstractController
{
    private $libreTranslateService;

    public function __construct(LibreTranslateService $libreTranslateService)
    {
        $this->libreTranslateService = $libreTranslateService;
    }

    #[Route('/translate/{page}', name: 'translate_page')]
    public function translatePage($page): Response
    {
        // Load the content of the existing page
        $filePath = __DIR__ . '/../../templates/' . $page . '.html.twig';
        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('Page not found.');
        }

        // Load the HTML content of the page
        $htmlContent = file_get_contents($filePath);

        // Use DOMDocument to parse and extract text
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($htmlContent);

        // Extract text content from the HTML
        $xpath = new \DOMXPath($dom);
        $elements = $xpath->query('//text()');

        // Translate each text node
        foreach ($elements as $element) {
            $text = $element->nodeValue;

            // Translate text if it's not empty
            if (!empty($text)) {
                $translatedText = $this->libreTranslateService->translate($text, 'fr'); // Translate to French
                $element->nodeValue = $translatedText;
            }
        }

        // Output the updated HTML with translated text
        return new Response($dom->saveHTML());
    }
}
