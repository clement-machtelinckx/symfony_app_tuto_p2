<?php

namespace App\ApiPlatform;

use ApiPlatform\Exception\RuntimeException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use ApiPlatform\Serializer\SerializerContextBuilderInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

class AdminGroupsContextBuilder implements SerializerContextBuilderInterface
{
    #[AsDecorator('api_platform.serializer.context_builder')]
    public function __construct(private SerializerContextBuilderInterface $decorated,private Security $security)
    {
    }

    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        if (isset($context['groups']) && $this->security->isGranted('ROLE_ADMIN')) {
            $context['groups'][] = $normalization ? 'admin:read' : 'admin:write';
        }

        return $context;
    }
}
