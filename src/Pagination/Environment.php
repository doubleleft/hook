<?php

namespace API\Pagination;

class Environment
{
    protected $request;
    protected $paginator;
    protected $currentPage;

    public function __construct()
    {
        $slim_app = \Slim\Slim::getInstance();
        $this->request = $slim_app->request();
    }

    public function getCurrentPage()
    {
        $page = (int) $this->currentPage ?: $this->request->get('page', 1);

        if ($page < 1 || filter_var($page, FILTER_VALIDATE_INT) === false) {
            return 1;
        }

        return $page;
    }

    public function make(array $items, $total, $perPage)
    {
        $this->paginator = new Paginator($this, $items, $total, $perPage);

        return $this->paginator->setupPaginationContext();
    }

}
