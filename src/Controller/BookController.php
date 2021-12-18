<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
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
     * @param BookRepository $bookRepository
     * @param EntityManagerInterface $manager
     */
    public function __construct(BookRepository $bookRepository, EntityManagerInterface $manager)
    {
        $this->bookRepository = $bookRepository;
        $this->manager = $manager;
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

            $title = $request->get('title');
            $isbn = $request->get('isbn');

            $book = new Book();
            $book->setTitle($title);
            $book->setIsbn($isbn);

            $this->manager->persist($book);
            $this->manager->flush();

            $this->manager->commit();
        } catch (\Error $exception) {
            $this->manager->rollback();

            return new JsonResponse([
                "message" => "Please, improve the correctly parameters to add a new Book."
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }


        return new JsonResponse($book, Response::HTTP_OK);
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
                return new JsonResponse([
                    "message" => "Book not found. Please check if the resouce is realy created."
                ], Response::HTTP_NOT_FOUND);
            }

            $book->setTitle($request->get('title'));
            $book->setIsbn($request->get('isbn'));

            $this->manager->flush();

            $this->manager->commit();
        } catch (\Error $exception) {
            $this->manager->rollback();

            return new JsonResponse([
                "message" => "Please, improve the correctly parameters to edit a new Book."
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }


        return new JsonResponse($book, Response::HTTP_OK);
    }
}