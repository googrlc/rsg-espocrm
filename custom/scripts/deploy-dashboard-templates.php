<?php

declare(strict_types=1);

use Espo\Core\Application;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

require dirname(__DIR__, 2) . '/bootstrap.php';

/**
 * Deploys managed dashboard templates and assigns them through User.dashboardTemplate.
 * This is safer than overwriting Preferences.dashboardLayout directly.
 */

$app = new Application();
$app->setupSystemUser();
$entityManager = $app->getContainer()->get('entityManager');

$templates = [
    buildProducerTemplate(),
    buildServiceTemplate(),
];

$templateIdByName = [];

foreach ($templates as $template) {
    $entity = upsertDashboardTemplate($entityManager, $template);
    $templateIdByName[$template['name']] = $entity->getId();

    echo sprintf(
        "Template ready: %s (%s)\n",
        $template['name'],
        $entity->getId()
    );
}

assignTemplates($entityManager, $templateIdByName);

echo "Dashboard template deployment complete.\n";

function upsertDashboardTemplate(EntityManager $entityManager, array $template): Entity
{
    $repository = $entityManager->getRDBRepository('DashboardTemplate');

    $entity = $repository
        ->where(['name' => $template['name']])
        ->findOne();

    if (!$entity) {
        $entity = $entityManager->getNewEntity('DashboardTemplate');
    }

    $entity->set([
        'name' => $template['name'],
        'layout' => toJsonValue($template['layout']),
        'dashletsOptions' => toJsonValue($template['dashletsOptions']),
    ]);

    $entityManager->saveEntity($entity);

    return $entity;
}

function assignTemplates(EntityManager $entityManager, array $templateIdByName): void
{
    $repository = $entityManager->getRDBRepository('User');

    $users = $repository
        ->where([
            'deleted' => false,
            'type!=' => ['api', 'portal'],
        ])
        ->find();

    foreach ($users as $user) {
        if ($user->get('userName') === 'system') {
            continue;
        }

        $templateId = resolveTemplateIdForUser($entityManager, $user, $templateIdByName);

        if (!$templateId) {
            echo sprintf("Skipped user: %s\n", $user->get('userName'));

            continue;
        }

        if ($user->get('dashboardTemplateId') === $templateId) {
            echo sprintf("No change: %s\n", $user->get('userName'));

            continue;
        }

        $user->set('dashboardTemplateId', $templateId);
        $entityManager->saveEntity($user);

        echo sprintf(
            "Assigned template to %s\n",
            $user->get('userName')
        );
    }
}

function resolveTemplateIdForUser(EntityManager $entityManager, Entity $user, array $templateIdByName): ?string
{
    $userName = (string) $user->get('userName');
    $defaultTeamName = (string) ($user->get('defaultTeamName') ?? '');

    $teamNames = getUserTeamNames($entityManager, $user);

    $serviceTeams = ['Service', 'Admin Service Team'];

    if (
        in_array($defaultTeamName, $serviceTeams, true) ||
        count(array_intersect($teamNames, $serviceTeams)) > 0 ||
        $userName === 'gretchcoates'
    ) {
        return $templateIdByName['CSR / Service Team'] ?? null;
    }

    if (
        in_array('Sales Team', $teamNames, true) ||
        $userName === 'lamar@risk-solutionsgroup.com'
    ) {
        return $templateIdByName['Producer / Account Manager'] ?? null;
    }

    return null;
}

function getUserTeamNames(EntityManager $entityManager, Entity $user): array
{
    $user->loadLinkMultipleField('teams');

    $teamIds = $user->getLinkMultipleIdList('teams');

    if (!$teamIds) {
        return [];
    }

    $teamNames = [];

    foreach ($teamIds as $teamId) {
        $team = $entityManager->getEntityById('Team', $teamId);

        if ($team) {
            $teamNames[] = (string) $team->get('name');
        }
    }

    return $teamNames;
}

function toJsonValue(array $data)
{
    return json_decode(json_encode($data, JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR);
}

function buildProducerTemplate(): array
{
    return [
        'name' => 'Producer / Account Manager',
        'layout' => [
            [
                'name' => 'Producer / Account Manager',
                'layout' => [
                    dashlet('overnight-policy-changes', 'Records', 0, 0, 2, 4),
                    dashlet('my-renewals', 'Records', 2, 0, 2, 4),
                    dashlet('policies-expiring-soon', 'Records', 0, 4, 2, 4),
                    dashlet('my-pipeline', 'Records', 2, 4, 2, 4),
                    dashlet('follow-up-tasks', 'Records', 0, 8, 2, 4),
                    dashlet('commission-snapshot', 'Records', 2, 8, 2, 4),
                ],
            ],
        ],
        'dashletsOptions' => [
            'overnight-policy-changes' => [
                'title' => 'Overnight Policy Changes',
                'entityType' => 'ActivityLog',
                'primaryFilter' => 'overnightPolicyChanges',
                'sortBy' => 'dateTime',
                'sortDirection' => 'desc',
                'displayRecords' => 8,
                'expandedLayout' => [
                    'rows' => [
                        [
                            ['name' => 'name', 'link' => true],
                        ],
                        [
                            ['name' => 'activityType'],
                            ['name' => 'dateTime', 'soft' => true],
                        ],
                        [
                            ['name' => 'policy', 'link' => true],
                            ['name' => 'account', 'link' => true],
                        ],
                    ],
                ],
            ],
            'my-renewals' => [
                'title' => 'My Renewals Needing Attention',
                'entityType' => 'Renewal',
                'primaryFilter' => 'active',
                'boolFilterList' => ['onlyMy'],
                'sortBy' => 'expirationDate',
                'sortDirection' => 'asc',
                'displayRecords' => 8,
                'expandedLayout' => [
                    'rows' => [
                        [
                            ['name' => 'name', 'link' => true],
                        ],
                        [
                            ['name' => 'stage'],
                            ['name' => 'expirationDate', 'soft' => true],
                        ],
                        [
                            ['name' => 'account', 'link' => true],
                            ['name' => 'urgency'],
                        ],
                    ],
                ],
            ],
            'policies-expiring-soon' => [
                'title' => 'Policies Expiring Soon',
                'entityType' => 'Policy',
                'primaryFilter' => 'expiringSoon',
                'boolFilterList' => ['onlyMy'],
                'sortBy' => 'expirationDate',
                'sortDirection' => 'asc',
                'displayRecords' => 8,
                'expandedLayout' => [
                    'rows' => [
                        [
                            ['name' => 'name', 'link' => true],
                        ],
                        [
                            ['name' => 'policyNumber'],
                            ['name' => 'expirationDate', 'soft' => true],
                        ],
                        [
                            ['name' => 'carrier'],
                            ['name' => 'lineOfBusiness'],
                        ],
                    ],
                ],
            ],
            'my-pipeline' => [
                'title' => 'My Open Opportunities',
                'entityType' => 'Opportunity',
                'primaryFilter' => 'open',
                'boolFilterList' => ['onlyMy'],
                'sortBy' => 'closeDate',
                'sortDirection' => 'asc',
                'displayRecords' => 8,
                'expandedLayout' => [
                    'rows' => [
                        [
                            ['name' => 'name', 'link' => true],
                        ],
                        [
                            ['name' => 'stage'],
                            ['name' => 'closeDate', 'soft' => true],
                        ],
                        [
                            ['name' => 'estimatedPremium'],
                            ['name' => 'lineOfBusiness'],
                        ],
                    ],
                ],
            ],
            'follow-up-tasks' => [
                'title' => 'Follow-Up Tasks',
                'entityType' => 'Task',
                'primaryFilter' => 'assignedToMe',
                'sortBy' => 'dateEnd',
                'sortDirection' => 'asc',
                'displayRecords' => 8,
                'expandedLayout' => [
                    'rows' => [
                        [
                            ['name' => 'name', 'link' => true],
                        ],
                        [
                            ['name' => 'status'],
                            ['name' => 'dateEnd', 'soft' => true],
                        ],
                        [
                            ['name' => 'linkedAccount', 'link' => true],
                            ['name' => 'taskType'],
                        ],
                    ],
                ],
            ],
            'commission-snapshot' => [
                'title' => 'Commission Snapshot',
                'entityType' => 'Commission',
                'primaryFilter' => 'overdue',
                'boolFilterList' => ['onlyMy'],
                'sortBy' => 'createdAt',
                'sortDirection' => 'desc',
                'displayRecords' => 8,
                'expandedLayout' => [
                    'rows' => [
                        [
                            ['name' => 'name', 'link' => true],
                        ],
                        [
                            ['name' => 'status'],
                            ['name' => 'expectedPaymentDate', 'soft' => true],
                        ],
                        [
                            ['name' => 'estimatedCommission'],
                            ['name' => 'account', 'link' => true],
                        ],
                    ],
                ],
            ],
        ],
    ];
}

function buildServiceTemplate(): array
{
    return [
        'name' => 'CSR / Service Team',
        'layout' => [
            [
                'name' => 'CSR / Service Team',
                'layout' => [
                    dashlet('service-queue-overview', 'Records', 0, 0, 2, 4),
                    dashlet('my-open-tasks', 'Records', 2, 0, 2, 4),
                    dashlet('overdue-tasks', 'Records', 0, 4, 2, 4),
                    dashlet('due-today', 'Records', 2, 4, 2, 4),
                    dashlet('waiting-on-client', 'Records', 0, 8, 2, 4),
                    dashlet('waiting-on-carrier', 'Records', 2, 8, 2, 4),
                    dashlet('recent-policy-changes', 'Records', 0, 12, 4, 4),
                ],
            ],
        ],
        'dashletsOptions' => [
            'service-queue-overview' => [
                'title' => 'Service Queue Overview',
                'entityType' => 'Task',
                'primaryFilter' => 'serviceQueue',
                'sortBy' => 'dateEnd',
                'sortDirection' => 'asc',
                'displayRecords' => 10,
                'expandedLayout' => [
                    'rows' => [
                        [
                            ['name' => 'name', 'link' => true],
                        ],
                        [
                            ['name' => 'status'],
                            ['name' => 'dateEnd', 'soft' => true],
                        ],
                        [
                            ['name' => 'taskType'],
                            ['name' => 'linkedAccount', 'link' => true],
                        ],
                    ],
                ],
            ],
            'my-open-tasks' => [
                'title' => 'My Open Tasks',
                'entityType' => 'Task',
                'primaryFilter' => 'assignedToMe',
                'sortBy' => 'dateEnd',
                'sortDirection' => 'asc',
                'displayRecords' => 8,
                'expandedLayout' => [
                    'rows' => [
                        [
                            ['name' => 'name', 'link' => true],
                        ],
                        [
                            ['name' => 'status'],
                            ['name' => 'dateEnd', 'soft' => true],
                        ],
                        [
                            ['name' => 'taskType'],
                            ['name' => 'linkedAccount', 'link' => true],
                        ],
                    ],
                ],
            ],
            'overdue-tasks' => [
                'title' => 'Overdue Tasks',
                'entityType' => 'Task',
                'primaryFilter' => 'overdue',
                'boolFilterList' => ['onlyMy'],
                'sortBy' => 'dateEnd',
                'sortDirection' => 'asc',
                'displayRecords' => 8,
                'expandedLayout' => [
                    'rows' => [
                        [
                            ['name' => 'name', 'link' => true],
                        ],
                        [
                            ['name' => 'status'],
                            ['name' => 'dateEnd', 'soft' => true],
                        ],
                        [
                            ['name' => 'urgency'],
                            ['name' => 'linkedAccount', 'link' => true],
                        ],
                    ],
                ],
            ],
            'due-today' => [
                'title' => 'Due Today',
                'entityType' => 'Task',
                'primaryFilter' => 'todays',
                'boolFilterList' => ['onlyMy'],
                'sortBy' => 'dateEnd',
                'sortDirection' => 'asc',
                'displayRecords' => 8,
                'expandedLayout' => [
                    'rows' => [
                        [
                            ['name' => 'name', 'link' => true],
                        ],
                        [
                            ['name' => 'status'],
                            ['name' => 'dateEnd', 'soft' => true],
                        ],
                        [
                            ['name' => 'taskType'],
                            ['name' => 'linkedAccount', 'link' => true],
                        ],
                    ],
                ],
            ],
            'waiting-on-client' => [
                'title' => 'Waiting on Client',
                'entityType' => 'Task',
                'primaryFilter' => 'waitingOnClient',
                'boolFilterList' => ['onlyMy'],
                'sortBy' => 'dateEnd',
                'sortDirection' => 'asc',
                'displayRecords' => 8,
                'expandedLayout' => [
                    'rows' => [
                        [
                            ['name' => 'name', 'link' => true],
                        ],
                        [
                            ['name' => 'dateEnd', 'soft' => true],
                            ['name' => 'urgency'],
                        ],
                        [
                            ['name' => 'taskType'],
                            ['name' => 'linkedAccount', 'link' => true],
                        ],
                    ],
                ],
            ],
            'waiting-on-carrier' => [
                'title' => 'Waiting on Carrier',
                'entityType' => 'Task',
                'primaryFilter' => 'waitingOnCarrier',
                'boolFilterList' => ['onlyMy'],
                'sortBy' => 'dateEnd',
                'sortDirection' => 'asc',
                'displayRecords' => 8,
                'expandedLayout' => [
                    'rows' => [
                        [
                            ['name' => 'name', 'link' => true],
                        ],
                        [
                            ['name' => 'dateEnd', 'soft' => true],
                            ['name' => 'urgency'],
                        ],
                        [
                            ['name' => 'taskType'],
                            ['name' => 'linkedAccount', 'link' => true],
                        ],
                    ],
                ],
            ],
            'recent-policy-changes' => [
                'title' => 'Recent Policy Changes',
                'entityType' => 'ActivityLog',
                'primaryFilter' => 'overnightPolicyChanges',
                'sortBy' => 'dateTime',
                'sortDirection' => 'desc',
                'displayRecords' => 10,
                'expandedLayout' => [
                    'rows' => [
                        [
                            ['name' => 'name', 'link' => true],
                        ],
                        [
                            ['name' => 'activityType'],
                            ['name' => 'dateTime', 'soft' => true],
                        ],
                        [
                            ['name' => 'policy', 'link' => true],
                            ['name' => 'account', 'link' => true],
                        ],
                    ],
                ],
            ],
        ],
    ];
}

function dashlet(string $id, string $name, int $x, int $y, int $width, int $height): array
{
    return [
        'id' => $id,
        'name' => $name,
        'x' => $x,
        'y' => $y,
        'width' => $width,
        'height' => $height,
    ];
}
