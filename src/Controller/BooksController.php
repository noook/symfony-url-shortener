<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BooksController extends AbstractController
{
    private $books = [
        [
            'id' => 1,
            'title' => 'The Catcher in the Rye',
            'author' => 'J.D. Salinger',
        ],
        [
            'id' => 2,
            'title' => 'To Kill a Mockingbird',
            'author' => 'Harper Lee',
        ],
        [
            'id' => 3,
            'title' => '1984',
            'author' => 'George Orwell',
        ],
    ];

    #[Route('/books', name: 'app_books')]
    public function index(): Response
    {
        return $this->render('books/index.html.twig', [
            'books' => $this->books,
        ]);
    }

    #[Route('/books/{id}')]
    public function bookById(int $id) {
        $book = array_find($this->books, function (array $element) use ($id) {
            return $element['id'] === $id;
        });

        if ($book === null) {
            throw $this->createNotFoundException("Book not found :(");
        }
        
        return $this->render('books/id.html.twig', [
            'book' => $book,
        ]);
    }
}
