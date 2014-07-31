<?php

namespace Hook\Pagination;

use Hook\Http\Input;

class Environment
{
    protected $paginator;
    protected $currentPage;

    public function getCurrentPage()
    {
        $page = (int) $this->currentPage ?: Input::get('page', 1);

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
