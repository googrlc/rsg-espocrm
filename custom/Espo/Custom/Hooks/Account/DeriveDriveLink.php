<?php

namespace Espo\Custom\Hooks\Account;

use Espo\Core\Hook\Hook\BeforeSave;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

class DeriveDriveLink implements BeforeSave
{
    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        $folderUrl = trim((string) ($entity->get('google_drive_folder_url') ?? ''));

        if ($folderUrl === '') {
            return;
        }

        // If it's already a full URL (any provider), leave it as-is.
        if (preg_match('#^https?://#i', $folderUrl)) {
            return;
        }

        // Bare folder ID — normalize to a Google Drive URL.
        if (preg_match('/^[A-Za-z0-9_-]{10,}$/', $folderUrl)) {
            $entity->set(
                'google_drive_folder_url',
                'https://drive.google.com/drive/u/0/folders/' . rawurlencode($folderUrl)
            );
        }
    }
}
