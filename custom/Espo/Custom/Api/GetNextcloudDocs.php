<?php
/************************************************************************
 * RSG — list a client's Nextcloud documents folder (server-side reader).
 * Route: GET /Account/:id/nextcloudDocs  (see custom routes.json)
 * The app password lives in Espo server config (nextcloud* keys); it is
 * never exposed to the browser. Same-origin, uses the caller's Espo ACL.
 ************************************************************************/

namespace Espo\Custom\Api;

use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Acl;
use Espo\Core\Utils\Config;
use Espo\ORM\EntityManager;

class GetNextcloudDocs implements Action
{
    public function __construct(
        private EntityManager $entityManager,
        private Config $config,
        private Acl $acl,
    ) {}

    public function process(Request $request): Response
    {
        $id = $request->getRouteParam('id');

        if (!$id) {
            throw new NotFound("No account id.");
        }

        $account = $this->entityManager->getEntityById('Account', $id);

        if (!$account) {
            throw new NotFound("Account not found.");
        }

        if (!$this->acl->check($account, 'read')) {
            throw new Forbidden();
        }

        $folderUrl = $account->get('nextcloud_folder_url') ?: $account->get('nextcloudFolderUrl');

        if (!$folderUrl) {
            return ResponseComposer::json(['folderUrl' => null, 'items' => [], 'count' => 0]);
        }

        $dir = $this->parseDir($folderUrl);

        if ($dir === null) {
            return ResponseComposer::json(
                ['folderUrl' => $folderUrl, 'items' => [], 'count' => 0,
                 'error' => 'Could not parse folder path.']
            );
        }

        $base = rtrim((string) $this->config->get('nextcloudUrl'), '/');
        $user = (string) $this->config->get('nextcloudUser');
        $pass = (string) $this->config->get('nextcloudAppPassword');

        if ($base === '' || $user === '' || $pass === '') {
            return ResponseComposer::json(
                ['folderUrl' => $folderUrl, 'items' => [], 'count' => 0,
                 'error' => 'Nextcloud credentials are not configured.']
            );
        }

        $davBase = $base . '/remote.php/dav/files/' . rawurlencode($user);
        $encPath = implode('/', array_map('rawurlencode', explode('/', ltrim($dir, '/'))));
        $davUrl = $davBase . '/' . $encPath;

        [$status, $body] = $this->propfind($davUrl, $user, $pass);

        if ($status !== 207) {
            return ResponseComposer::json(
                ['folderUrl' => $folderUrl, 'items' => [], 'count' => 0,
                 'error' => "Nextcloud returned $status."]
            );
        }

        $items = $this->parseListing($body, $davBase, $dir, $base);

        return ResponseComposer::json([
            'folderUrl' => $folderUrl,
            'path' => $dir,
            'items' => $items,
            'count' => count($items),
        ]);
    }

    private function parseDir(string $url): ?string
    {
        $q = parse_url($url, PHP_URL_QUERY);

        if (!$q) {
            return null;
        }

        parse_str($q, $params);

        return isset($params['dir']) && $params['dir'] !== '' ? $params['dir'] : null;
    }

    /** @return array{0:int,1:string} */
    private function propfind(string $url, string $user, string $pass): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'PROPFIND',
            CURLOPT_HTTPHEADER => ['Depth: 1', 'Content-Type: application/xml'],
            CURLOPT_USERPWD => $user . ':' . $pass,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            // Request oc:fileid so individual files can be opened directly
            // via Nextcloud's /f/<fileid> viewer route.
            CURLOPT_POSTFIELDS => '<?xml version="1.0"?>'
                . '<d:propfind xmlns:d="DAV:" xmlns:oc="http://owncloud.org/ns">'
                . '<d:prop><d:resourcetype/><oc:fileid/></d:prop>'
                . '</d:propfind>',
        ]);
        $body = (string) curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [$status, $body];
    }

    private function parseListing(string $body, string $davBase, string $dir, string $base): array
    {
        $xml = @simplexml_load_string($body);

        if ($xml === false) {
            return [];
        }

        $xml->registerXPathNamespace('d', 'DAV:');
        $selfPath = parse_url($davBase . '/' . implode('/', array_map('rawurlencode', explode('/', ltrim($dir, '/')))), PHP_URL_PATH);

        $items = [];

        foreach ($xml->xpath('//d:response') as $resp) {
            $resp->registerXPathNamespace('d', 'DAV:');
            $hrefNodes = $resp->xpath('d:href');
            if (!$hrefNodes) {
                continue;
            }
            $hrefPath = rtrim(rawurldecode((string) $hrefNodes[0]), '/');

            if ($hrefPath === rtrim($selfPath, '/')) {
                continue; // the folder itself
            }

            $isDir = (bool) $resp->xpath('d:propstat/d:prop/d:resourcetype/d:collection');
            $name = rawurldecode(basename($hrefPath));

            if ($name === '' || $name === '.') {
                continue;
            }

            $resp->registerXPathNamespace('oc', 'http://owncloud.org/ns');
            $fileIdNodes = $resp->xpath('d:propstat/d:prop/oc:fileid');
            $fileId = $fileIdNodes ? (string) $fileIdNodes[0] : '';

            if ($isDir) {
                $openUrl = $base . '/index.php/apps/files/?dir=' . rawurlencode($dir . '/' . $name);
            } elseif ($fileId !== '') {
                // Open the file itself in Nextcloud's viewer via /f/<fileid>.
                $openUrl = $base . '/f/' . $fileId;
            } else {
                $openUrl = $base . '/index.php/apps/files/?dir=' . rawurlencode($dir);
            }

            $items[] = ['name' => $name, 'isDir' => $isDir, 'openUrl' => $openUrl];
        }

        usort($items, function ($a, $b) {
            if ($a['isDir'] !== $b['isDir']) {
                return $a['isDir'] ? -1 : 1;
            }
            return strcasecmp($a['name'], $b['name']);
        });

        return $items;
    }
}
