<?php

namespace App\Controller;

use App\Error\Message;
use App\Helper\Factory\Entity\BookFactory;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class BookController extends AbstractController
{
    /**
     * @var BookRepository
     */
    private BookRepository $bookRepository;

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $manager;

    /**
     * @var BookFactory
     */
    private BookFactory $bookFactory;
    private Security $security;

    /**
     * @param BookRepository $bookRepository
     * @param EntityManagerInterface $manager
     * @param BookFactory $bookFactory
     * @param Security $security
     */
    public function __construct(
        BookRepository $bookRepository,
        EntityManagerInterface $manager,
        BookFactory $bookFactory,
        Security $security
    ) {
        $this->bookRepository = $bookRepository;
        $this->manager = $manager;
        $this->bookFactory = $bookFactory;
        $this->security = $security;
    }

    /**
     * @Route("/api/books", name="book_list", methods={"GET"})
     */
    public function all(): JsonResponse
    {
        $books = $this->bookRepository->findBy([
            'user' => $this->security->getUser()
        ]);

        return new JsonResponse($books, Response::HTTP_OK);
    }

    /**
     * @Route("/api/books", name="book_add", methods={"POST"})
     */
    public function add(Request $request): JsonResponse
    {
        try {
            $this->manager->beginTransaction();

            $book = $this->bookFactory->fromJson(
                $request->getContent()
            );

            $book->setUser($this->security->getUser());

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
                    "message" => Message::MG0005
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return new JsonResponse($book, Response::HTTP_OK);
    }

    /**
     * @Route("/api/books/{id}", name="book_show", methods={"GET"})
     */
    public function show($id): JsonResponse
    {
        try {
            $book = $this->bookRepository->findOneBy([
                'user' => $this->security->getUser(),
                'id' => $id
            ]);

            if (!$book) {
                return new JsonResponse(
                    [
                        "message" => Message::MG0002
                    ],
                    Response::HTTP_NOT_FOUND
                );
            }
        } catch (Exception $exception) {
            $this->manager->rollback();

            return new JsonResponse([
                "message" => Message::MG0005
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse($book, Response::HTTP_OK);
    }

    /**
     * @Route("/api/books/{id}", name="book_delete", methods={"DELETE"})
     */
    public function delete($id): JsonResponse
    {
        try {
            $book = $this->bookRepository->findOneBy([
                'user' => $this->security->getUser(),
                'id' => $id
            ]);

            if (!$book) {
                return new JsonResponse(
                    [
                        "message" => Message::MG0002
                    ],
                    Response::HTTP_NOT_FOUND
                );
            }

            $this->manager->remove($book);
            $this->manager->flush($book);
        } catch (Exception $exception) {
            $this->manager->rollback();

            return new JsonResponse([
                "message" => Message::MG0005
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse([], Response::HTTP_OK);
    }

    /**
     * @Route("/api/books/{id}", name="book_update", methods={"PUT"})
     */
    public function update($id, Request $request): JsonResponse
    {
        try {
            $this->manager->beginTransaction();

            $book = $this->bookRepository->findOneBy([
                'user' => $this->security->getUser(),
                'id' => $id
            ]);

            if (!$book) {
                return new JsonResponse(
                    [
                        "message" => Message::MG0002
                    ],
                    Response::HTTP_NOT_FOUND
                );
            }

            $bookRequest = $this->bookFactory->fromJson(
                $request->getContent()
            );

            $book->setTitle($bookRequest->getTitle());
            $book->setIsbn($bookRequest->getIsbn());

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

        return new JsonResponse($book, Response::HTTP_OK);
    }
}