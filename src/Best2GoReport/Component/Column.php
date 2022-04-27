<?php declare(strict_types=1);

namespace Best2Go\Best2GoReport\Component;

use Symfony\Component\ExpressionLanguage\Expression;

/** @psalm-immutable */
/* final? */ class Column
{
    /** @var Expression|callable|string */
    private $expression;

    /** @var string */
    private $title;

    /** @var string */
    private $description;

    /** @param Expression|callable|string $expression */
    final public function __construct($expression, string $title = null, string $description = null)
    {
        $this->expression = $expression;
        $this->title = $title ?? (string) $expression;
        $this->description = $description ?? $this->title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /** @return Expression|callable|string */
    public function getExpression()
    {
        return $this->expression;
    }
}
