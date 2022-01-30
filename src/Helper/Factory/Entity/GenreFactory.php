<?php

namespace App\Helper\Factory\Entity;

use App\Entity\Genre;
use App\Error\Code;
use App\Error\Message;
use InvalidArgumentException;


class GenreFactory implements FactoryFromJsonInterface
{
    public function fromJson(string $json): Genre
    {
        $data = json_decode($json);

        if (!$data) {
            throw new InvalidArgumentException(Message::MG0003, Code::CD0003);
        }

        if (
            !is_object($data) ||
            !property_exists($data, 'name') ||
            !property_exists($data, 'description')
        ) {
            throw new InvalidArgumentException(Message::MG0003, Code::CD0003);
        }

        $genre = new Genre();
        $genre->setName($data->name);
        $genre->setDescription($data->description);

        return $genre;
    }
}