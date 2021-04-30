<?php

namespace Controller;

use Core\Core;
use Utilities\Auth;
use Psr\Container\ContainerInterface;
use Utilities\Pagination;

class MainController extends Core
{
    private $auth;
    private $tmp;
    private $http;
    protected $pagination;

    protected function auth()
    {
        if(!$this->auth instanceof Auth)
        {
            $this->auth = new Auth($this->getDI());
        }

        return $this->auth;
    }

    protected function http()
    {
        if(is_null($this->http)) $this->http = $this->getDI()->get('http');

        return $this->http;
    }

    protected function tmp()
    {
        if(is_null($this->tmp)) $this->tmp = $this->getDI()->get('tmp');

        return $this->tmp;
    }

    protected function getPagination()
    {
        if(is_null($this->pagination))
        {
            $offset             = $this->http()->query->get('offset', Pagination::DEFAULT_OFFSET);
            $limit              = $this->http()->query->get('limit', Pagination::DEFAULT_LIMIT);
            $orderField         = $this->http()->query->get('order_field', Pagination::DEFAULT_ORDER_FIELD);
            $orderDestination   = $this->http()->query->get('order_destination', Pagination::DEFAULT_ORDER_DESTINATION);
            $this->pagination = new Pagination(
                (int)$offset,
                (int)$limit,
                $orderField,
                $orderDestination
            );
        }

        return $this->pagination;
    }

    protected function getMeta(): array
    {
        return [
            'offset'            => $this->pagination->offset,
            'limit'             => $this->pagination->limit,
            'order_field'       => $this->pagination->orderField,
            'order_destination' => $this->pagination->orderDestination,
        ];
    }
}