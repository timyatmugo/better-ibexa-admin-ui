<?php

declare(strict_types=1);

namespace Netgen\Bundle\BetterIbexaAdminUIBundle\Controller\Content;

use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Core\Helper\TranslationHelper;
use LogicException;
use Netgen\Bundle\BetterIbexaAdminUIBundle\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

final class UpdateAlwaysAvailable extends Controller
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly ContentService $contentService,
        private readonly FormFactory $formFactory,
        private readonly TranslationHelper $translationHelper
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $form = $this->formFactory->createContentAlwaysAvailableUpdateForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Netgen\Bundle\BetterIbexaAdminUIBundle\Form\Data\Content\ContentUpdateAlwaysAvailableData $data */
            $data = $form->getData();

            $contentInfo = $data->getContentInfo();
            $alwaysAvailable = $data->getAlwaysAvailable();

            if ($contentInfo === null || $alwaysAvailable === null) {
                throw new LogicException(
                    'Could not find required form data'
                );
            }

            try {
                $contentName = $this->translationHelper->getTranslatedContentNameByContentInfo($contentInfo);
                $metadataUpdateStruct = $this->contentService->newContentMetadataUpdateStruct();
                $metadataUpdateStruct->alwaysAvailable = $alwaysAvailable;
                $this->contentService->updateContentMetadata(
                    $contentInfo,
                    $metadataUpdateStruct,
                );

                if ($alwaysAvailable) {
                    $message = $this->translator->trans(
                        'content.update_always_available.success.available',
                        ['%name%' => $contentName],
                        'locationview',
                    );
                } else {
                    $message = $this->translator->trans(
                        'content.update_always_available.success.not_available',
                        ['%name%' => $contentName],
                        'locationview',
                    );
                }
            } catch (UnauthorizedException $e) {
                return new JsonResponse(
                    [
                        'errors' => [
                            $e->getMessage(),
                        ],
                    ],
                    Response::HTTP_UNAUTHORIZED,
                );
            }
        } else {
            $errors = [];

            foreach ($form->getErrors(true) as $formError) {
                $errors[] = $formError->getMessage();
            }

            return new JsonResponse(
                [
                    'errors' => $errors,
                ],
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        return new JsonResponse([
            'message' => $message,
        ]);
    }
}