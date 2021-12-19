<?php

namespace App\Controller;

use App\Entity\Book;
use App\Helper\Entity\BookFactory;
use App\Message\Book as BookMessage;
use App\Message\System as SystemMessage;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use HttpInvalidParamException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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

    /**
     * @param BookRepository $bookRepository
     * @param EntityManagerInterface $manager
     * @param BookFactory $bookFactory
     */
    public function __construct(
        BookRepository $bookRepository,
        EntityManagerInterface $manager,
        BookFactory $bookFactory
    ) {
        $this->bookRepository = $bookRepository;
        $this->manager = $manager;
        $this->bookFactory = $bookFactory;
    }

    /**
     * @Route("/books", name="book_list", methods={"GET"})
     */
    public function all(): JsonResponse
    {
        $books = $this->bookRepository->findAll();

        return new JsonResponse($books, Response::HTTP_OK);
    }

    /**
     * @Route("/books", name="book_add", methods={"POST"})
     */
    public function add(Request $request): JsonResponse
    {
        try {
            $this->manager->beginTransaction();

            $book = $this->bookFactory->fromJson(
                $request->getContent()
            );

            $this->manager->persist($book);
            $this->manager->flush();

            $this->manager->commit();
        } catch (\Error $exception) {
            $this->manager->rollback();

            return new JsonResponse(
                [
                    "message" => SystemMessage::SY0001
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } catch (HttpInvalidParamException $e) {
            return new JsonResponse(
                [
                    "message" => $e->getMessage()
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        return new JsonResponse($book, Response::HTTP_OK);
    }

    /**
     * @Route("/books/{id}", name="book_show", methods={"GET"})
     */
    public function show($id): JsonResponse
    {
        try {
            $book = $this->manager->find(Book::class, $id);

            if (!$book) {
                return new JsonResponse(
                    [
                        "message" => BookMessage::BK0002
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

        return new JsonResponse($book, Response::HTTP_OK);
    }

    /**
     * @Route("/books/{id}", name="book_delete", methods={"DELETE"})
     */
    public function delete($id): JsonResponse
    {
        try {
            $book = $this->manager->find(Book::class, $id);

            if (!$book) {
                return new JsonResponse(
                    [
                        "message" => BookMessage::BK0002
                    ],
                    Response::HTTP_NOT_FOUND
                );
            }

            $this->manager->remove($book);
            $this->manager->flush($book);
        } catch (Exception $exception) {
            $this->manager->rollback();

            return new JsonResponse([
                "message" => SystemMessage::SY0001
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse([], Response::HTTP_OK);
    }

    /**
     * @Route("/books/{id}", name="book_update", methods={"PUT"})
     */
    public function update($id, Request $request): JsonResponse
    {
        try {
            $this->manager->beginTransaction();

            $book = $this->manager->find(Book::class, $id);

            if (!$book) {
                return new JsonResponse(
                    [
                        "message" => BookMessage::BK0002
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
        } catch (HttpInvalidParamException $e) {
            return new JsonResponse(
                [
                    "message" => $e->getMessage()
                ],
                Response::HTTP_BAD_REQUEST
            );
        } catch (Exception $exception) {
            $this->manager->rollback();

            return new JsonResponse([
                "message" => SystemMessage::SY0001
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse($book, Response::HTTP_OK);
    }
}