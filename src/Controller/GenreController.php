<?php

namespace App\Controller;

use App\Entity\Genre;
use App\Helper\Factory\Entity\GenreFactory;
use App\Message\Genre as GenreMessage;
use App\Message\System as SystemMessage;
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
     * @Route("/api/genre", name="genre_add", methods={"POST"})
     */
    public function add(Request $request): JsonResponse
    {
        try {
            $this->manager->beginTransaction();

            $book = $this->genreFactory->fromJson(
                $request->getContent()
            );

            $this->manager->persist($book);
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
                    "message" => SystemMessage::SY0001
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return new JsonResponse($book, Response::HTTP_OK);
    }

    /**
     * @Route("/api/genre/{id}", name="genre_show", methods={"GET"})
     */
    public function show($id): JsonResponse
    {
        try {
            $genre = $this->manager->find(Genre::class, $id);

            if (!$genre) {
                return new JsonResponse(
                    [
                        "message" => GenreMessage::GR0002
                    ],
                    Response::HTTP_NOT_FOUND
                );
            }
        } catch (Exception $exception) {
            $this->manager->rollback();

            return new JsonResponse([
                "message" => SystemMessage::SY0001
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse($genre, Response::HTTP_OK);
    }
}