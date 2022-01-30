<?php

namespace App\Tests\Unit;

use App\Entity\Book;
use App\Entity\Genre;
use App\Error\Code;
use App\Helper\Factory\Entity\BookFactory;
use App\Repository\GenreRepository;
use App\Tests\UnitTester;
use Codeception\Test\Unit;
use Exception;
use InvalidArgumentException;

class BookFactoryTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected UnitTester $tester;

    /**
     * @var BookFactory
     */
    protected BookFactory $bookFactory;

    /**
     * @var GenreRepository
     */
    protected GenreRepository $genreRepository;

    /**
     * @throws Exception
     */
    protected function _before()
    {
        $this->genreRepository = $this->make(
            GenreRepository::class, ['find' => new Genre()]
        );

        $this->bookFactory = new BookFactory($this->genreRepository);
    }

    protected function _after()
    {
    }

    /**
     * @dataProvider jsonValidProvider
     * @return void
     */
    public function testCreateBookFromAValidJson(string $json)
    {
        $book = $this->bookFactory->fromJson($json);

        $this->assertEquals(Book::class, get_class($book));
        $this->assertEquals(13, strlen($book->getIsbn()));
        $this->assertEquals(Genre::class, get_class($book->getGenre()));
    }

    /**
     * @dataProvider jsonInvalidProvider
     * @return void
     */
    public function testCantCreateBookFromAInvalidJson(string $json)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(Code::CD0001);

        $this->bookFactory->fromJson($json);
    }

    /**
     * @return string[]
     */
    public function jsonValidProvider(): array
    {
        return [
            [
                file_get_contents("tests/_data/Factory/book/valid_book_1.json")
            ],
            [
                file_get_contents("tests/_data/Factory/book/valid_book_2.json")
            ],
            [
                file_get_contents("tests/_data/Factory/book/valid_book_3.json")
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
                file_get_contents("tests/_data/Factory/book/invalid_book_1.json")
            ],
            [
                file_get_contents("tests/_data/Factory/book/invalid_book_2.json")
            ],
            [
                file_get_contents("tests/_data/Factory/book/invalid_book_3.json")
            ],
        ];
    }
}