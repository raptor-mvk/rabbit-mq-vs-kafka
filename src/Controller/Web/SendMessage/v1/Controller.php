<?php

namespace App\Controller\Web\SendMessage\v1;

use App\Controller\Web\SendMessage\v1\Input\SendMessageDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
class Controller
{
    public function __construct(private readonly Manager $manager)
    {
    }

    #[Route(path: '/api/v1/send-messages', methods: ['POST'])]
    public function saveMessageAction(#[MapRequestPayload] SendMessageDTO $sendMessageDTO): Response
    {
        return new JsonResponse(['time' => $this->manager->sendMessages($sendMessageDTO)], Response::HTTP_OK);
    }
}
