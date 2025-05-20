<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    public function __construct(
        private readonly string $targetDirectory,
        private readonly SluggerInterface $slugger,
    ) {
    }

    public function upload(UploadedFile $file): string
    {
        if (!$file->isValid()) {
            throw new \Exception('Invalid file upload');
        }

        if ($file->getSize() === 0) {
            throw new \Exception('File is empty');
        }

        $targetDirectory = $this->getTargetDirectory();
        
        if (!file_exists($targetDirectory)) {
            mkdir($targetDirectory, 0777, true);
        }

        $fileName = uniqid() . '.' . $file->guessExtension();
        
        try {
            $file->move($targetDirectory, $fileName);
        } catch (\Exception $e) {
            throw new \Exception('Failed to move uploaded file: ' . $e->getMessage());
        }
        
        return $fileName;
    }

    public function delete(string $fileName): void
    {
        $targetDirectory = $this->getTargetDirectory();
        $filePath = $targetDirectory . '/' . $fileName;
        
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
} 