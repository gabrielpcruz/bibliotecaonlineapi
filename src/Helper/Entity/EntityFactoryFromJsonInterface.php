<?php

namespace App\Helper\Entity;

interface EntityFactoryFromJsonInterface
{
    public function fromJson(string $json);
}