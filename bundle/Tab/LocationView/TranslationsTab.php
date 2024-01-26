<?php

declare(strict_types=1);

namespace Netgen\Bundle\BetterIbexaAdminUIBundle\Tab\LocationView;

use Ibexa\AdminUi\Tab\LocationView\TranslationsTab as IbexaTranslationsTab;
use Ibexa\Contracts\AdminUi\Tab\AbstractEventDispatchingTab;
use Ibexa\Contracts\AdminUi\Tab\OrderedTabInterface;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Netgen\Bundle\BetterIbexaAdminUIBundle\Form\FormFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

final class TranslationsTab extends AbstractEventDispatchingTab implements OrderedTabInterface
{
    private IbexaTranslationsTab $inner;
    private PermissionResolver $permissionResolver;
    private FormFactory $localFormFactory;

    public function __construct(
        IbexaTranslationsTab $inner,
        PermissionResolver $permissionResolver,
        FormFactory $localFormFactory,
        Environment $twig,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($twig, $translator, $eventDispatcher);
        $this->inner = $inner;
        $this->permissionResolver = $permissionResolver;
        $this->localFormFactory = $localFormFactory;
    }

    public function getIdentifier(): string
    {
        return $this->inner->getIdentifier();
    }

    public function getName(): string
    {
        return $this->inner->getName();
    }

    public function getOrder(): int
    {
        return $this->inner->getOrder();
    }

    public function getTemplate(): string
    {
        return $this->inner->getTemplate();
    }

    public function getTemplateParameters(array $contextParameters = []): array
    {
        $parentParameters = $this->inner->getTemplateParameters($contextParameters);

        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Content $content */
        $content = $contextParameters['content'];

        $alwaysAvailableUpdateForm = $this->localFormFactory->createContentAlwaysAvailableUpdateForm(
            $content->contentInfo->alwaysAvailable,
        );
        $canEdit = $this->permissionResolver->canUser(
            'content',
            'edit',
            $content,
        );

        $parameters = [
            'form_always_available_update' => $alwaysAvailableUpdateForm->createView(),
            'can_edit' => $canEdit,
        ];

        return $parentParameters + $parameters;
    }
}
