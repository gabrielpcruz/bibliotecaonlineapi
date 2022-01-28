<?php

namespace App\Controller;

use App\Error\Message;
use App\Error\Message\System as SystemMessage;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class AuthController extends AbstractController
{
    private UserRepository $userRepository;

    private Security $security;

    public function __construct(
        UserRepository $userRepository,
        Security $security
    ) {
        $this->userRepository = $userRepository;
        $this->security = $security;
    }

    /**
     * @Route("/api/register", methods={"POST"})
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $jsonData = json_decode($request->getContent());
            $user = $this->userRepository->create($jsonData);

            return new JsonResponse([
                "user" => $user
            ]);
        } catch (UniqueConstraintViolationException $exception) {
            return new JsonResponse(
                [
                    "message" => Message::MG0006
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } catch (\Exception $exception) {
            var_dump($exception);
            return new JsonResponse(
                [
                    "message" => Message::MG0005
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}