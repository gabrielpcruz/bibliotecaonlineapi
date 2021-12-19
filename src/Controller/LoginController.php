<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Firebase\JWT\JWT;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends AbstractController
{
    /**
     * @var UserRepository
     */
    private UserRepository $repository;

    /**
     * @var UserPasswordHasherInterface
     */
    private UserPasswordHasherInterface $encoder;

    /**
     * @param UserRepository $userRepository
     * @param UserPasswordHasherInterface $encoder
     */
    public function __construct(
        UserRepository $userRepository,
        UserPasswordHasherInterface $encoder
    ) {
        $this->repository = $userRepository;
        $this->encoder = $encoder;
    }

    /**
     * @Route("/login", name="login")
     */
    public function index(Request $request): Response
    {
        $dadosJson = json_decode($request->getContent());

        if (is_null($dadosJson->usuario) || is_null($dadosJson->senha)) {
            return  new JsonResponse([
                'erro' => 'Favor informar usuário e senha'
            ], Response::HTTP_BAD_REQUEST);
        }

        /** @var User $user */
        $user = $this->repository->findOneBy(['username' => $dadosJson->usuario]);

        if (!$this->encoder->isPasswordValid($user, $dadosJson->senha)) {
            return new JsonResponse([
                'erro' => 'Usuário ou senha inválidos'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = JWT::encode(
            ['username' => $user->getUserIdentifier()],
            'key'
        );

        return new JsonResponse([
            'access_token' => $token
        ]);
    }
}
