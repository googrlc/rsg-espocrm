<?php

namespace Espo\Custom\Hooks\ClientNote;

use Espo\Core\Hook\Hook\AfterSave;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Repository\Option\SaveOptions;

class PostToStream implements AfterSave
{
    public function __construct(
        private EntityManager $entityManager
    ) {}

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        if (!$entity->isNew()) {
            return;
        }

        $accountId = trim((string) ($entity->get('accountId') ?? ''));
        $content = trim((string) ($entity->get('content') ?? ''));

        if ($accountId === '' || $content === '') {
            return;
        }

        $category = trim((string) ($entity->get('category') ?? ''));
        $post = $category !== '' ? sprintf('**%s**%s%s', $category, "\n\n", $content) : $content;

        $data = [
            'clientNoteId' => $entity->getId(),
            'category' => $category,
        ];

        $values = [
            'type' => 'Post',
            'post' => $post,
            'parentId' => $accountId,
            'parentType' => 'Account',
            'relatedId' => $entity->getId(),
            'relatedType' => 'ClientNote',
            'data' => $data,
        ];

        $createdById = trim((string) ($entity->get('createdById') ?? ''));
        $options = $createdById !== '' ? ['createdById' => $createdById] : [];

        $this->entityManager->createEntity('Note', $values, $options);
    }
}
