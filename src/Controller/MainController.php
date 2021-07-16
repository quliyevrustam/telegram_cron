<?php

namespace Controller;

use Core\Core;
use Model\SiteVocabulary;
use Utilities\Auth;
use Psr\Container\ContainerInterface;
use Utilities\Helper;
use Utilities\Pagination;

class MainController extends Core
{
    const   HTTP_CODE_FORBIDDEN      = 403;
    const   HTTP_CODE_ERROR          = 500;
    const   HTTP_CODE_OK             = 200;
    const   HTTP_CODE_EMPTY          = 204;
    const   HTTP_CODE_NOT_FOUND      = 404;
    const   HTTP_CODE_BAD_REQUEST    = 400;
    const   HTTP_CODE_UNAUTHORIZED   = 401;
    const   HTTP_CODE_CREATED        = 201;
    const   HTTP_CODE_UNPROCESSABLE  = 422;
    const   HTTP_MESSAGE_EMPTY       = 'No Content';
    const   HTTP_MESSAGE_GENERIC     = 'OK';
    const   HTTP_MESSAGE_ERROR       = 'Server error';
    const   HTTP_MESSAGE_FORBIDDEN   = 'User not granted';
    const   HTTP_MESSAGE_NOT_FOUND   = 'Not Found';
    const   HTTP_MESSAGE_BAD_REQUEST = 'Bad request';

    private $auth;
    private $tmp;
    private $http;
    protected $pagination;
    private $result;

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
        if(is_null($this->tmp))
        {
            $this->tmp = $this->getDI()->get('tmp');

            $languageVariables = (new SiteVocabulary())->getAllLanguageVariables();
            $this->tmp->addGlobal('translation', $languageVariables['en']);
            $this->tmp->addGlobal('DB_HOST', DB_HOST);
        }

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

    protected function getPaginationMeta(): array
    {
        return [
            'offset'            => $this->pagination->offset,
            'limit'             => $this->pagination->limit,
            'order_field'       => $this->pagination->orderField,
            'order_destination' => $this->pagination->orderDestination,
        ];
    }

    protected final function responseAPI ($data, $meta = [], ?string $message = self::HTTP_MESSAGE_GENERIC, int $code = self::HTTP_CODE_OK) : string {

        $this->result['code']    = $code;
        $this->result['data']    = $data;
        $this->result['message'] = $message;
        $this->result['meta']    = $data['meta']??$meta;

        switch ($this->result['code']) {
            case self::HTTP_CODE_OK:
            {
                if (is_array($data) && count($data) === 0) {
                    $this->result['message'] = self::HTTP_MESSAGE_EMPTY;
                    //$this->result['code']    = self::HTTP_CODE_EMPTY;
                }
                break;
            }
            case self::HTTP_CODE_EMPTY:
            {
                $this->result['code']    = self::HTTP_CODE_OK;
                $this->result['message'] = self::HTTP_MESSAGE_EMPTY;
                break;
            }
            case self::HTTP_CODE_ERROR:
            {
                $this->result['message'] = self::HTTP_MESSAGE_ERROR;
                if ($message == \CRM\Utility\ExceptionMessage::NOT_GRANTED) {
                    $this->result['code']    = self::HTTP_CODE_FORBIDDEN;
                    $this->result['message'] = self::HTTP_MESSAGE_FORBIDDEN;
                }
                break;
            }
            case self::HTTP_CODE_NOT_FOUND:
            {
                $this->result['message'] = self::HTTP_MESSAGE_NOT_FOUND;
                break;
            }
            case self::HTTP_CODE_FORBIDDEN:
            {
                $this->result['message'] = self::HTTP_MESSAGE_FORBIDDEN;
                break;
            }
        }

        if (empty ($this->result['meta'])) unset ($this->result['meta']);

        $this->result['meta']['offset'] = $this->offset??0;
        $this->result['meta']['limit']  = $this->limit??0;

        if (is_array($data)) $this->result['meta']['count'] = $this->result['meta']['count']??count($data);
        else $this->result['meta']['count'] = 1;

        $this->result['meta']['total'] = !isset ($meta['total']) ? 0 : (int) $meta['total'];

        header("Access-Control-Allow-Orgin: *");
        header("Access-Control-Allow-Methods: *");
        header("Content-Type: application/json");
        header("HTTP/1.1 " . $this->result['code'] . " " . $this->result['message']);
        return json_encode($this->result);
    }
}