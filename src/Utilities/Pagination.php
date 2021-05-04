<?php

namespace Utilities;

class Pagination
{
    const DEFAULT_OFFSET            = 0;
    const DEFAULT_LIMIT             = 10;
    const DEFAULT_ORDER_FIELD       = 'id';
    const DEFAULT_ORDER_DESTINATION = 'DESC';

    public $offset;
    public $limit;
    public $orderField;
    public $orderDestination;

    public function __construct(int $offset, int $limit, string $orderField, string $orderDestination)
    {
        $this->offset           = $offset;
        $this->limit            = $limit;
        $this->orderField       = $orderField;
        $this->orderDestination = $orderDestination;
    }
}