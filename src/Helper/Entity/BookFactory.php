<?php

namespace App\Helper\Entity;

use App\Entity\Book;
use App\Message\Book as BookMessage;
use HttpInvalidParamException;

class BookFactory implements EntityFactoryFromJsonInterface
{
    /**
     * @throws HttpInvalidParamException
     */
    public function fromJson(string $json): Book
    {
        $data = json_decode($json);

        if (!$data) {
            throw new HttpInvalidParamException(BookMessage::BK0001);
        }

        if (
            !is_object($data) ||
            !property_exists($data, 'title') ||
            !property_exists($data, 'isbn')
        ) {
            throw new HttpInvalidParamException(BookMessage::BK0001);
        }

        $book = new Book();
        $book->setTitle($data->title);
        $book->setIsbn($data->isbn);

        return $book;
    }
}