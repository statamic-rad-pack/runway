<?php

namespace StatamicRadPack\Runway\Exceptions;

class PublishedColumnMissingException extends \Exception
{
    public function __construct(public string $table, public string $column)
    {
        parent::__construct("The [{$this->column}] publish column is missing from the {$this->table} table.");
    }
}
