<?php

namespace App\Services;

use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;

class GoogleDriveService
{
    protected $client;
    protected $driveService;

    public function __construct()
    {
        $client = new Google_Client();
        $client->setAuthConfig(storage_path('app/google/credentials.json')); // Path to your credentials.json file
        $client->addScope(Google_Service_Drive::DRIVE);
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');

        $this->client = $client;
        $this->driveService = new Google_Service_Drive($this->client);
    }

    // Get Google Drive service
    public function getService()
    {
        return $this->driveService;
    }

    // Get file metadata by file ID
    public function getFile($fileId)
    {
        return $this->driveService->files->get($fileId);
    }

    // Download file from Google Drive
    public function downloadFile($fileId)
    {
        $response = $this->driveService->files->get($fileId, ['alt' => 'media']);
        return $response->getBody()->getContents();
    }

    // Upload a file to Google Drive
    public function uploadFile($outputPath, $filePath, $folderId, $mimeType = null)
    {
        $driveService = $this->getService();

        // Check if the file already exists in the specified folder
        $existingFileId = $this->getFileIdByName($filePath, $folderId);

        // If $file is an instance of UploadedFile, get its real path and set MIME type
        if ($outputPath instanceof \Illuminate\Http\UploadedFile) {
            $file = $outputPath->getRealPath();
            if (!$mimeType) {
                $mimeType = $outputPath->getMimeType();
            }
        } else {
            // $file is treated as a file path
            $file = $outputPath;
            if (!$mimeType) {
                $mimeType = mime_content_type($file);
            }
        }

        // Prepare file metadata
        $fileMetadata = new Google_Service_Drive_DriveFile([
            'name' => $filePath,
        ]);

        // Upload the file
        try {
            $content = file_get_contents($outputPath);
            if ($existingFileId) {
                // Update the existing file
                $file = $driveService->files->update($existingFileId, $fileMetadata, [
                    'data' => $content,
                    'mimeType' => $mimeType,
                    'uploadType' => 'multipart'
                ]);
            } else {
                // Create a new file if it doesn't exist
                $fileMetadata['parents'] = [$folderId]; // Specify the folder ID
                $file = $driveService->files->create($fileMetadata, [
                    'data' => $content,
                    'mimeType' => $mimeType,
                    'uploadType' => 'multipart'
                ]);
            }
            
            return $file->getId(); // File uploaded successfully, return file metadata (or file ID)
        } catch (\Exception $e) {
            \Log::error("Error uploading file: " . $e->getMessage());
            throw new \Exception("Error uploading file: " . $e->getMessage());
        }
    }

    // Get file ID by file name and folder
    public function getFileIdByName($fileName, $folderId = null)
    {
        $query = "name='{$fileName}' and trashed=false";
        if ($folderId) {
            $query .= " and '{$folderId}' in parents";
        }

        $results = $this->driveService->files->listFiles(['q' => $query])->getFiles();
        return count($results) > 0 ? $results[0]->getId() : null;
    }

    // Create a public link for a file
    public function createPublicLink($fileId)
    {
        $permission = new \Google_Service_Drive_Permission([
            'type' => 'anyone',
            'role' => 'reader',
        ]);

        $this->driveService->permissions->create($fileId, $permission);
        return "https://drive.google.com/file/d/{$fileId}/view?usp=sharing";
    }

     // Get folder mapping
    public function getFolderMapping()
    {
        return [
            'surat-izin-penelitian' => '1w-1ruM9tMAwX6ne9z55e_49jfCEpcIro',
            'surat-keterangan-aktif' => '12uX_fjhrcIS-rqvoycDC2gLUDRioWfPC',
            'surat-keterangan-aktif-ortu-pns' => '1fcxX-JFCfw3YMEHJ8wkMRMQqOulKKkF2',
            'surat-bebas-pustaka' => '1llk8yjHJSE11jWZudisCKwhU1NwvF2Rg',
            'surat-pengajuan-cuti' => '1pDq26rBJEmnMl4tzhDDh-RLunWwYGNFR',
            'surat-pengunduran-diri' => '1BEl0AgclSyBeOVauCRl0Ar_CyTZL9_Ke',
            'surat-observasi' => '1ON-N1NMNBdltBDc3uF2e3CAwTxdkOYAE',
            'surat-pindah-kuliah' => '1exUcEZytAuykkPfkWoyw4HVhQCmcUlvq',
            'surat-pengantar-magang' => '1MJ8oavzSqAJx6ATmRTsm2nWYQeFhNFGO',
            'surat-rekomendasi-sept' => '1_649bqzMYm_6hHa6a6fZQfX5sDBDejJn',
        ];
    }
 
    // Resolve folder ID by folder name
    public function getFolderId($folderName)
    {
        $folderMapping = $this->getFolderMapping();
 
        if (!array_key_exists($folderName, $folderMapping)) {
            throw new \Exception("Invalid folder name: {$folderName}");
        }
 
        return $folderMapping[$folderName];
    }

}
