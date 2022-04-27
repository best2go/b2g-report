<?php declare(strict_types=1);

namespace Best2Go\Best2GoReport\Interfaces;

use Best2Go\Best2GoReport\Component\Column;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Symfony\Component\HttpFoundation\Response;

interface ReportEngineInterface extends ReportDataProviderInterface, EventDispatcherInterface
{
    public function getTitles(): ?array;
    public function addColumn(Column $column): void;
    public function registerColumnsProvider(ColumnsProviderInterface $provider): void;

    /** @deprecated not ReportEngineInterface responsibility (!) */
    public function getCsvResponse(int $status = 200, array $headers = []): Response;
    public function getTextTableResponse(int $status = 200, array $headers = []): Response;

    // ExpressionLanguage methods
    public function registerExpressionFunction(ExpressionFunction $function): void;
    public function registerExpressionFunctionProvider(ExpressionFunctionProviderInterface $provider): void;

    // TODO: something nice to have (out-of-scope)

    // get human report name, may be some auto-generated, run tools (aka, ReportRepository::runByName(??)
    // public function getName(): string

    // setup report state ?? (aka, TableStyle, CellStyle, Transpose, e.g.)
    // public function setOption(): void

    // report-after-report, pivot table, ???
    // public function chain()
}
