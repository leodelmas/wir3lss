<?php

namespace App\Controller;

use App\Behavior\LogStatisticsHandlerTrait;
use App\Dto\LogSearch;
use App\Handler\LogsExportHandler;
use App\Repository\LogRepository;
use App\Utils\RandomColor;
use Knp\Component\Pager\PaginatorInterface;
use League\Csv\Writer;
use SplTempFileObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class LogController extends AbstractController
{
    use LogStatisticsHandlerTrait;

    #[Route('/', name: 'log.index', methods: ['GET'])]
    public function index(LogRepository $logRepository, PaginatorInterface $paginator, Request $request, ChartBuilderInterface $chartBuilder, TranslatorInterface $translator): Response
    {
        $rawNumberByUser = $logRepository->findNumberByUser();
        $numberByUserChartData = $this->handleRequestResult($rawNumberByUser, "count");
        $numberByUserChart = $chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
        $numberByUserChart->setData([
            'labels' => $this->handleRequestResult($rawNumberByUser, "user"),
            'datasets' => [
                [
                    'backgroundColor' => RandomColor::many(count($numberByUserChartData)),
                    'data' => $numberByUserChartData
                ],
            ],
        ]);

        $rawNumberByDate = $logRepository->findNumberByDate();
        $numberByDateChart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $numberByDateChartColor = RandomColor::one();
        $numberByDateChart->setData([
            'labels' => $this->handleRequestResult($rawNumberByDate, "date"),
            'datasets' => [
                [
                    'label' => $translator->trans('Number of logins for each day', [], 'app'),
                    'backgroundColor' => $numberByDateChartColor,
                    'borderColor' => $numberByDateChartColor,
                    'data' => $this->handleRequestResult($rawNumberByDate, "count"),
                ],
            ],
        ]);

        $search = LogSearch::createFromArray($request->query->all());
        $logs = $paginator->paginate(
            $logRepository->findAllFiltered($search),
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('log/index.html.twig', [
            'logs' => $logs,
            'numberByUserChart' => $numberByUserChart,
            'numberByDateChart' => $numberByDateChart
        ]);
    }

    #[Route('/export', name: 'log.export', methods: ['GET'])]
    public function export(LogRepository $logRepository)
    {
        $logs = $logRepository->findAllForExport();
        foreach ($logs as $key => $log) {
            $logs[$key]["sented"] = $log["sented"]->format("d/m/Y");
        }
        $csv = Writer::createFromFileObject(new SplTempFileObject());
        $csv->insertOne(["ID", "Source", "Destination", "Date", "Résultat", "Utilisateur"]);
        $csv->insertAll($logs);
        $csv->output('logs.csv');
        die;
    }
}
