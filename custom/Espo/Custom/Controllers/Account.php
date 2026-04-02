<?php
namespace Espo\Custom\Controllers;

use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Exceptions\BadRequest;

class Account extends \Espo\Modules\Crm\Controllers\Account
{
    public function postActionRunIntelPack(Request $request): object
    {
        $data = $request->getParsedBody();

        if (empty($data->id)) {
            throw new BadRequest("Missing account ID.");
        }

        $entityManager = $this->getContainer()->getByClass(\Espo\ORM\EntityManager::class);
        $account = $entityManager->getEntityById('Account', $data->id);

        if (!$account) {
            throw new BadRequest("Account not found.");
        }

        // Get webhook URL from config
        $config = $this->getContainer()->getByClass(\Espo\Core\Utils\Config::class);
        $webhookUrl = $config->get('intelPackWebhookUrl');

        if (empty($webhookUrl)) {
            return (object) [
                'success' => false,
                'message' => 'Intel Pack webhook URL not configured. Set intelPackWebhookUrl in config.'
            ];
        }

        // Resolve assigned user name
        $assignedUserName = '';
        $assignedUserId = $account->get('assignedUserId');
        if ($assignedUserId) {
            $user = $entityManager->getEntityById('User', $assignedUserId);
            if ($user) {
                $assignedUserName = $user->get('name');
            }
        }

        $payload = json_encode([
            'entityType' => 'Account',
            'entityId' => $account->get('id'),
            'assignedUserName' => $assignedUserName,
            'momentumId' => $account->get('momentumClientId') ?? '',
        ]);

        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return (object) ['success' => true, 'message' => 'Intel Pack triggered.'];
        }

        return (object) ['success' => false, 'message' => 'Webhook returned HTTP ' . $httpCode];
    }
}
