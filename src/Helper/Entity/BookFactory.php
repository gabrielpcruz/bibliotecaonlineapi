<?php

namespace App\Helper\Entity;

use App\Entity\Book;
use App\Message\Book as BookMessage;
use App\Repository\GenreRepository;
use InvalidArgumentException;

class BookFactory implements EntityFactoryFromJsonInterface
{
    private GenreRepository $genreRepository;

    public function __construct(GenreRepository $genre)
    {
        $this->genreRepository = $genre;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function fromJson(string $json): Book
    {
        $data = json_decode($json);

        if (!$data) {
            throw new InvalidArgumentException(BookMessage::BK0001);
        }

        if (
            !is_object($data) ||
            !property_exists($data, 'title') ||
            !property_exists($data, 'isbn') ||
            !property_exists($data, 'genre_id')
        ) {
            throw new InvalidArgumentException(BookMessage::BK0001);
        }

        $book = new Book();
        $book->setTitle($data->title);
        $book->setIsbn($data->isbn);

        $genre = $this->genreRepository->find($data->genre_id);

        if (!$genre) {
            throw new InvalidArgumentException(BookMessage::BK0001);
        }

        $book->setGenre($genre);

        return $book;
    }
}