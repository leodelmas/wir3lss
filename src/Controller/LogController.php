<?php

namespace App\Controller;

use App\Repository\LogRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/log')]
class LogController extends AbstractController
{
    #[Route('/', name: 'log_index', methods: ['GET'])]
    public function index(LogRepository $logRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $logs = $paginator->paginate(
            $logRepository->findAll(),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('log/index.html.twig', [
            'logs' => $logs,
        ]);
    }
}
