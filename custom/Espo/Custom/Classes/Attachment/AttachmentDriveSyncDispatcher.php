<?php

namespace Espo\Custom\Classes\Attachment;

use Espo\Core\Utils\Config;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

class AttachmentDriveSyncDispatcher
{
    public function __construct(
        private Config $config,
        private EntityManager $entityManager
    ) {}

    public function dispatch(Entity $attachment): void
    {
        if (!$this->shouldSync($attachment)) {
            return;
        }

        $webhookUrl = trim((string) ($this->config->get('attachmentDriveSyncWebhookUrl') ?? ''));
        if ($webhookUrl === '') {
            return;
        }

        $binaryPath = $this->resolveBinaryPath($attachment);
        if ($binaryPath === '' || !is_file($binaryPath) || !is_readable($binaryPath)) {
            return;
        }

        $rawContents = @file_get_contents($binaryPath);
        if ($rawContents === false || $rawContents === '') {
            return;
        }

        $context = $this->resolveContext($attachment);

        $payload = [
            'eventType' => 'attachment.created',
            'triggeredAt' => gmdate('c'),
            'attachmentId' => $attachment->getId(),
            'fileName' => $this->normalizeFileName((string) ($attachment->get('name') ?? 'attachment.bin')),
            'mimeType' => (string) ($attachment->get('type') ?? 'application/octet-stream'),
            'fileSize' => (int) ($attachment->get('size') ?? 0),
            'fileContentBase64' => base64_encode($rawContents),
            'accountId' => $context['accountId'],
            'accountName' => $context['accountName'],
            'opportunityId' => $context['opportunityId'],
            'opportunityName' => $context['opportunityName'],
            'driveFolderId' => $context['driveFolderId'],
            'parentType' => (string) ($attachment->get('parentType') ?? ''),
            'parentId' => (string) ($attachment->get('parentId') ?? ''),
            'relatedType' => (string) ($attachment->get('relatedType') ?? ''),
            'relatedId' => (string) ($attachment->get('relatedId') ?? ''),
        ];

        $this->send($webhookUrl, $payload);
    }

    private function shouldSync(Entity $attachment): bool
    {
        $role = trim((string) ($attachment->get('role') ?? ''));
        if ($role !== 'Attachment') {
            return false;
        }

        if ((bool) ($attachment->get('isBeingUploaded') ?? false)) {
            return false;
        }

        $fetchedId = trim((string) ($attachment->getFetched('id') ?? ''));
        $wasUploading = (bool) ($attachment->getFetched('isBeingUploaded') ?? false);
        $isNewRecord = $fetchedId === '';
        $uploadCompletedNow = $wasUploading && !(bool) ($attachment->get('isBeingUploaded') ?? false);

        return $isNewRecord || $uploadCompletedNow;
    }

    private function resolveBinaryPath(Entity $attachment): string
    {
        $storagePath = trim((string) ($attachment->get('storageFilePath') ?? ''));
        $rootPath = realpath(__DIR__ . '/../../../../../');
        $id = (string) ($attachment->getId() ?? '');

        $candidates = [];
        if ($storagePath !== '') {
            $candidates[] = $storagePath;
            if ($rootPath) {
                $candidates[] = rtrim($rootPath, '/') . '/' . ltrim($storagePath, '/');
            }
        }

        if ($rootPath && $id !== '') {
            $candidates[] = rtrim($rootPath, '/') . '/data/upload/' . $id;
        }

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return '';
    }

    private function resolveContext(Entity $attachment): array
    {
        $parentType = (string) ($attachment->get('parentType') ?? '');
        $parentId = (string) ($attachment->get('parentId') ?? '');
        $relatedType = (string) ($attachment->get('relatedType') ?? '');
        $relatedId = (string) ($attachment->get('relatedId') ?? '');

        $opportunityId = '';
        if ($parentType === 'Opportunity' && $parentId !== '') {
            $opportunityId = $parentId;
        } elseif ($relatedType === 'Opportunity' && $relatedId !== '') {
            $opportunityId = $relatedId;
        }

        $accountId = '';
        if ($parentType === 'Account' && $parentId !== '') {
            $accountId = $parentId;
        } elseif ($relatedType === 'Account' && $relatedId !== '') {
            $accountId = $relatedId;
        }

        $opportunityName = '';
        if ($opportunityId !== '') {
            $opportunity = $this->entityManager->getEntityById('Opportunity', $opportunityId);
            if ($opportunity) {
                $opportunityName = trim((string) ($opportunity->get('name') ?? ''));
                if ($accountId === '') {
                    $accountId = trim((string) ($opportunity->get('accountId') ?? ''));
                }
            }
        }

        $accountName = '';
        $driveFolderId = '';
        if ($accountId !== '') {
            $account = $this->entityManager->getEntityById('Account', $accountId);
            if ($account) {
                $accountName = trim((string) ($account->get('name') ?? ''));
                $driveFolderId = $this->extractDriveFolderId((string) ($account->get('googleDriveFolderUrl') ?? ''));
            }
        }

        return [
            'accountId' => $accountId,
            'accountName' => $accountName,
            'opportunityId' => $opportunityId,
            'opportunityName' => $opportunityName,
            'driveFolderId' => $driveFolderId,
        ];
    }

    private function extractDriveFolderId(string $value): string
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return '';
        }

        if (preg_match('#/folders/([^/?]+)#', $trimmed, $match) === 1) {
            return trim((string) $match[1]);
        }

        return ltrim($trimmed, '/');
    }

    private function normalizeFileName(string $name): string
    {
        $clean = str_replace(["\r", "\n", '/', '\\'], '-', trim($name));

        return $clean !== '' ? $clean : 'attachment.bin';
    }

    private function send(string $webhookUrl, array $payload): void
    {
        $body = json_encode($payload);
        if ($body === false) {
            return;
        }

        $headers = [
            'Content-Type: application/json',
            'X-Attachment-Sync-Event: attachment.created',
        ];

        $secret = trim((string) ($this->config->get('attachmentDriveSyncWebhookSecret') ?? ''));
        if ($secret !== '') {
            $headers[] = 'X-Attachment-Sync-Secret: ' . $secret;
        }

        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_exec($ch);
        curl_close($ch);
    }
}
