<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RandomController extends AbstractController {
    #[Route('/random/number', methods: ['GET', 'POST'], name: 'random_number')]
    public function number(): Response {
        $number = random_int(0, 100);

        dump($number);

        return $this->render('random/number.html.twig', [
            'number' => $number,
        ]);
    }
}
