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

        if ($folderUrl !== '' && strpos($folderUrl, 'drive.google.com') === false) {
            $folderId = $this->extractFolderId($folderUrl);
            if ($folderId !== '') {
                $entity->set('google_drive_folder_url', 'https://drive.google.com/drive/u/0/folders/' . rawurlencode($folderId));
            }
        }
    }

    private function extractFolderId(string $value): string
    {
        if (preg_match('~/folders/([^/?#]+)~', $value, $matches)) {
            return $matches[1];
        }

        if (preg_match('/^[A-Za-z0-9_-]{10,}$/', $value)) {
            return $value;
        }

        return '';
    }
}
