<?php

namespace Customize\Services;

use Customize\Repository\BlogRepository;

class FrontService
{
    /**
     * @var BlogRepository
     */
    protected $blogRepository;

    public function __construct(
        BlogRepository $blogRepository
    )
    {
        $this->blogRepository = $blogRepository;
    }

    public function getDisplayNotices()
    {
        return $this->blogRepository->getDisplayNotices();
    }
}