<?php

namespace App\DataFixtures;

use App\Entity\Book;
use App\Entity\Genre;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;

class BookFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $this->loadGenresDependency($manager);

        $genreRepository = $manager->getRepository(Genre::class);
        $userRepository = $manager->getRepository(User::class);

        for ($i = 0; $i < 100; $i++) {
            $book = new Book();
            $book->setTitle("Some title for a some book ({$i})");
            $book->setIsbn(random_int(1000000000000, PHP_INT_MAX));

            $genreFound = false;

            while (!$genreFound) {
                $genreFound = $genreRepository->find(random_int(1, 8));
            }

            $book->setGenre($genreFound);
            $book->setUser($userRepository->find(1));

            $manager->persist($book);
            $manager->flush();
        }
    }

    private function loadGenresDependency(ObjectManager $manager): void
    {
        $genres = [
            "Ficção Literária" => "Ficção literária é uma narrativa irreal, imaginária. Sabe aqueles filmes que assistíamos quando éramos criança? De viagens espaciais, cientistas malucos? Era tudo ficção, igual os livros escritos com a imaginação hoje em dia.",
            "Não-Ficção" => "Em contrapartida, obras de não-ficção nada é inventado, são acontecimentos que podem acontecer de verdade. Baseados no mundo real, na história, no ambiente, na memória e na biografia.",
            "Suspense" => "Aquele frio na barriga, emoção, a expectativa de não saber o que vai acontecer. Tem certeza que você não sabe mesmo o que é uma obra de suspense? A expectativa criada para o leitor, é o que atrai a atenção do público.",
            "Ficção Científica" => "A ficção científica pode ser considerada o gênero que se baseia em ideias, ideias de inovação cientifica que nos levará para o futuro.",
            "Fantasia" => "Obras que se baseiam em fenômenos sobrenaturais para estabelecer os limites de seus mundos. Muitas vezes é a través da magia, que se cria a fantasia e inspira os leitores a mergulharem na história.",
            "Horror" => "Gênero que desperta o medo, através do horror, geralmente provocado por cenas assustadoras e estressantes. Esse medo pode ser criado por elementos naturais e sobrenaturais.",
            "Poesia" => "Há quem diga que poesia é aquele texto com verso regulado por métrica e com rimas no final, simples assim, mas, tem quem ache que é mais que isso. Que é a liberdade de escrita, de estruturas sem amarras.",
            "Romance" => "Um dos gêneros mais presentes nas estantes, é construído em cima de uma história de amor, que varia entre a química e a tensão de um casal. A química é sempre a força motriz, enquanto a tensão se constrói no cotidiano, bem como a relação dos dois.",
        ];

        foreach ($genres as $genreName => $description) {
            $genre = new Genre();
            $genre->setName($genreName);
            $genre->setDescription($description);

            $manager->persist($genre);
            $manager->flush();
        }
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class
        ];
    }
}
