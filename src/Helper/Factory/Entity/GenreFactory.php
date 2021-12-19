<?php

namespace App\Helper\Factory\Entity;

use App\Entity\Genre;
use App\Message\Genre as GenreMessage;
use InvalidArgumentException;


class GenreFactory implements FactoryFromJsonInterface
{
    public function fromJson(string $json): Genre
    {
        $data = json_decode($json);

        if (!$data) {
            throw new InvalidArgumentException(GenreMessage::GR0001);
        }

        if (
            !is_object($data) ||
            !property_exists($data, 'name') ||
            !property_exists($data, 'description')
        ) {
            throw new InvalidArgumentException(GenreMessage::GR0001);
        }

        $genre = new Genre();
        $genre->setName($data->name);
        $genre->setDescription($data->description);

        return $genre;
    }
}