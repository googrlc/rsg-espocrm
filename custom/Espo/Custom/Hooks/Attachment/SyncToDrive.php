<?php

namespace Espo\Custom\Hooks\Attachment;

use Espo\Core\Hook\Hook\AfterSave;
use Espo\Custom\Classes\Attachment\AttachmentDriveSyncDispatcher;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

class SyncToDrive implements AfterSave
{
    public function __construct(
        private AttachmentDriveSyncDispatcher $attachmentDriveSyncDispatcher
    ) {}

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        $this->attachmentDriveSyncDispatcher->dispatch($entity);
    }
}
