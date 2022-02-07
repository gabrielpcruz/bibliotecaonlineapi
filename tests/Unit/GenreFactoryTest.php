<?php
namespace App\Tests;

use App\Entity\Genre;
use App\Error\Code;
use App\Helper\Factory\Entity\GenreFactory;
use Codeception\Test\Unit;
use InvalidArgumentException;

class GenreFactoryTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected UnitTester $tester;

    /**
     * @var GenreFactory
     */
    protected GenreFactory $genreFactory;
    
    protected function _before()
    {
        $this->genreFactory = new GenreFactory();
    }

    protected function _after()
    {
    }

    /**
     * @dataProvider jsonValidProvider
     * @param string $json
     */
    public function testCreateGenreFromAValidJson($json)
    {
        $genre = $this->genreFactory->fromJson($json);

        $this->assertEquals(Genre::class, get_class($genre));
        // Verificando se campos não estão vazios
        $this->assertNotEmpty($genre->getName());
        $this->assertNotEmpty($genre->getDescription());
        // Verificando se campos são strings
        $this->assertIsString($genre->getName());
        $this->assertIsString($genre->getDescription());
    }

    /**
     * @dataProvider jsonInvalidProvider
     * @param string $json
     */
    public function testCantCreateBookFromAInvalidJson(string $json)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(Code::CD0003);

        $this->genreFactory->fromJson($json);
    }

    /**
     * @return string[]
     */
    public function jsonValidProvider(): array
    {
        return [
            [
                file_get_contents("tests/_data/Factory/genre/valid_genre_1.json")
            ],
            [
                file_get_contents("tests/_data/Factory/genre/valid_genre_2.json")
            ],
            [
                file_get_contents("tests/_data/Factory/genre/valid_genre_3.json")
            ],
        ];
    }

    /**
     * @return string[]
     */
    public function jsonInvalidProvider(): array
    {
        return [
            [
                file_get_contents("tests/_data/Factory/genre/invalid_genre_1.json")
            ],
            [
                file_get_contents("tests/_data/Factory/genre/invalid_genre_2.json")
            ],
            [
                file_get_contents("tests/_data/Factory/genre/invalid_genre_3.json")
            ],
        ];
    }
}