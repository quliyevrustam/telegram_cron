<?php

namespace Controller\Channel;

use Controller\MainController;
use Model\Channel\Post;

class TopController extends MainController
{
    public function showTop()
    {
        $top = $this->model(Post::class)->getTop();

        return $this->tmp()->render('Channel/Top/view.html', ['top' => $top]);
    }
}
