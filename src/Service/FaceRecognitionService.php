<?php
// src/Service/FaceRecognitionService.php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Process\Process;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class FaceRecognitionService
{
    private EntityManagerInterface $entityManager;
    private ParameterBagInterface $params;
    private LoggerInterface $logger;
    private Filesystem $filesystem;
    private string $pythonScriptPath;
    private string $pythonInterpreter;

    public function __construct(
        EntityManagerInterface $entityManager,
        ParameterBagInterface $params,
        LoggerInterface $logger,
        Filesystem $filesystem,
        string $projectDir,
        string $pythonInterpreter = 'python3'
    ) {
        $this->entityManager = $entityManager;
        $this->params = $params;
        $this->logger = $logger;
        $this->filesystem = $filesystem;
        $this->pythonScriptPath = $projectDir.'/python/face_encoder.py';
        $this->pythonInterpreter = $pythonInterpreter;
    }

    public function processFaceImage(UploadedFile $image): string
    {
        $this->validateImage($image);
        $tempPath = $this->createTempFile($image);
        
        try {
            $faceEncoding = $this->getFaceEncoding($tempPath);
            return json_encode($faceEncoding);
        } finally {
            $this->filesystem->remove($tempPath);
        }
    }

    private function validateImage(UploadedFile $image): void
    {
        if (!$image->isValid()) {
            throw new \RuntimeException('Invalid file upload: '.$image->getErrorMessage());
        }

        if ($image->getSize() > 5 * 1024 * 1024) {
            throw new \RuntimeException('File size exceeds 5MB limit');
        }

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($image->getMimeType(), $allowedMimeTypes)) {
            throw new \RuntimeException('Only JPEG and PNG images are allowed');
        }
    }

    private function createTempFile(UploadedFile $image): string
    {
        $tempDir = sys_get_temp_dir().'/face_recognition';
        $this->filesystem->mkdir($tempDir);
        
        $tempFilename = uniqid('face_').'.'.$image->guessExtension();
        $tempPath = $tempDir.'/'.$tempFilename;
        
        $image->move($tempDir, $tempFilename);
        
        return $tempPath;
    }

    private function getFaceEncoding(string $imagePath): array
    {
        $this->logger->debug('Attempting face encoding', ['image_path' => $imagePath]);
    
    if (!$this->filesystem->exists($imagePath)) {
        $this->logger->error('Image file not found', ['path' => $imagePath]);
        throw new \RuntimeException('Image file not found');
    }
        $process = new Process([
            $this->pythonInterpreter,
            $this->pythonScriptPath,
            $imagePath
        ]);
        
        $process->setTimeout(30);
        $process->run();
        
        if (!$process->isSuccessful()) {
            $this->logger->error('Face encoding failed', [
                'error' => $process->getErrorOutput(),
                'output' => $process->getOutput()
            ]);
            throw new ProcessFailedException($process);
        }
        
        $output = json_decode($process->getOutput(), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON response from face encoder');
        }
        
        if (isset($output['error'])) {
            throw new \RuntimeException($output['error']);
        }
        
        if (!is_array($output) || count($output) !== 128) {
            throw new \RuntimeException('Invalid face encoding format');
        }
        
        return $output;
    }

    public function findUserByFace(string $faceEncodingJson): ?User
    {
        try {
            $users = $this->entityManager->getRepository(User::class)->findAll();
            
            foreach ($users as $user) {
                if (!$user->getFaceEncoding()) {
                    continue;
                }
                
                if ($this->compareFaces($faceEncodingJson, $user->getFaceEncoding())) {
                    return $user;
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('Face comparison error', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        return null;
    }

    private function compareFaces(string $encoding1Json, string $encoding2Json): bool
    {
        try {
            $enc1 = json_decode($encoding1Json, true);
            $enc2 = json_decode($encoding2Json, true);
            
            if (!is_array($enc1) || !is_array($enc2)) {
                return false;
            }
            
            $distance = $this->calculateFaceDistance($enc1, $enc2);
            return $distance < 0.6; // Standard face recognition threshold
        } catch (\Exception $e) {
            $this->logger->error('Face comparison failed', [
                'exception' => $e->getMessage()
            ]);
            return false;
        }
    }

    private function calculateFaceDistance(array $encoding1, array $encoding2): float
    {
        if (count($encoding1) !== 128 || count($encoding2) !== 128) {
            throw new \RuntimeException('Invalid face encoding dimensions');
        }
        
        $sum = 0.0;
        for ($i = 0; $i < 128; $i++) {
            $diff = $encoding1[$i] - $encoding2[$i];
            $sum += $diff * $diff;
        }
        
        return sqrt($sum);
    }

    public function validateFaceImage(UploadedFile $image): bool
    {
        try {
            $tempPath = $this->createTempFile($image);
            $encoding = $this->getFaceEncoding($tempPath);
            return !empty($encoding);
        } catch (\Exception $e) {
            return false;
        } finally {
            if (isset($tempPath)) {
                $this->filesystem->remove($tempPath);
            }
        }
    }
    public function testFaceRecognition(Request $request, FaceRecognitionService $faceService)
{
    /** @var UploadedFile|null $file */
    $file = $request->files->get('face_image');
    
    if (!$file) {
        return new JsonResponse(['error' => 'No file uploaded'], 400);
    }

    try {
        $faceEncoding = $faceService->processFaceImage($file);
        return new JsonResponse([
            'success' => true,
            'encoding' => json_decode($faceEncoding, true)
        ]);
    } catch (\Exception $e) {
        return new JsonResponse([
            'success' => false,
            'error' => $e->getMessage()
        ], 400);
    }
}
}