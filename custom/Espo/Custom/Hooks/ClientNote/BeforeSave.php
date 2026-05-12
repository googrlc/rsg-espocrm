<?php

namespace Espo\Custom\Hooks\ClientNote;

use Espo\ORM\Entity;

class BeforeSave
{
    public function beforeSave(Entity $entity, array $options): void
    {
        if ($entity->isNew() || !$entity->get('name')) {
            $category = $entity->get('category') ?: 'Note';
            $content  = $entity->get('content') ?: '';
            $snippet  = mb_strlen($content) > 60
                ? mb_substr($content, 0, 60) . '…'
                : $content;

            $entity->set('name', $snippet ?: $category);
        }
    }
}
