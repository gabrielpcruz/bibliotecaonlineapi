<?php

namespace App\Controller\Admin;

use App\Error\Message;
use App\Error\Message\Genre as GenreMessage;
use App\Error\Message\System as SystemMessage;
use App\Helper\Factory\Entity\GenreFactory;
use App\Repository\GenreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GenreController extends AbstractController
{
    private GenreRepository $genreRepository;

    private EntityManagerInterface $manager;

    private GenreFactory $genreFactory;

    public function __construct(
        GenreRepository $genreRepository,
        EntityManagerInterface $manager,
        GenreFactory $genreFactory
    ) {
        $this->genreRepository = $genreRepository;
        $this->manager = $manager;
        $this->genreFactory = $genreFactory;
    }

    /**
     * @Route("/api/genre", name="genre_list", methods={"GET"})
     */
    public function all(): JsonResponse
    {
        $genres = $this->genreRepository->findAll();

        return new JsonResponse($genres, Response::HTTP_OK);
    }

    /**
     * @Route("/admin/api/genre", name="genre_add", methods={"POST"})
     */
    public function add(Request $request): JsonResponse
    {
        try {
            $this->manager->beginTransaction();

            $genre = $this->genreFactory->fromJson(
                $request->getContent()
            );

            $this->manager->persist($genre);
            $this->manager->flush();

            $this->manager->commit();
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(
                [
                    "message" => $e->getMessage()
                ],
                Response::HTTP_BAD_REQUEST
            );
        } catch (Exception $exception) {
            $this->manager->rollback();

            return new JsonResponse(
                [
                    "message" => Message::MG0005
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return new JsonResponse($genre, Response::HTTP_OK);
    }

    /**
     * @Route("/admin/api/genre/{id}", name="genre_update", methods={"PUT"})
     */
    public function update($id, Request $request): JsonResponse
    {
        try {
            $this->manager->beginTransaction();

            $genre = $this->genreRepository->findOneBy([
                'id' => $id
            ]);

            if (!$genre) {
                return new JsonResponse(
                    [
                        "message" => Message::MG0004
                    ],
                    Response::HTTP_NOT_FOUND
                );
            }

            $genreRequest = $this->genreFactory->fromJson(
                $request->getContent()
            );

            $genre->setDescription($genreRequest->getDescription());
            $genre->setName($genreRequest->getName());

            $this->manager->flush();

            $this->manager->commit();
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(
                [
                    "message" => $e->getMessage()
                ],
                Response::HTTP_BAD_REQUEST
            );
        } catch (Exception $exception) {
            $this->manager->rollback();

            return new JsonResponse([
                "message" => Message::MG0005
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse($genre, Response::HTTP_OK);
    }
}