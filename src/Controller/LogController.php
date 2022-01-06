<?php

namespace App\Controller;

use App\Repository\LogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/log')]
class LogController extends AbstractController
{
    #[Route('/', name: 'log_index', methods: ['GET'])]
    public function index(LogRepository $logRepository): Response
    {
        return $this->render('log/index.html.twig', [
            'logs' => $logRepository->findAll(),
        ]);
    }
}
