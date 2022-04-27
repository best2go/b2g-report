<?php declare(strict_types=1);

namespace Best2Go\Best2GoReport\Component\Traits;

use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

trait ExpressionLanguageTrait
{
    /** @var ExpressionLanguage */
    private $engine;

    public function registerExpressionFunction(ExpressionFunction $function): void
    {
        $this->engine->addFunction($function);
    }

    public function registerExpressionFunctionProvider(ExpressionFunctionProviderInterface $provider): void
    {
        $this->engine->registerProvider($provider);
    }

    /** @param string|Expression $expression*/
    private function parse($expression): Expression
    {
        return $this->engine->parse($expression, ['row', 'collection', 'ctx']);
    }
}
