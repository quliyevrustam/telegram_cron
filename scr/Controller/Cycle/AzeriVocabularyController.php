<?php

namespace Controller\Cycle;

use Controller\MainController;
use Model\Cycle\AzeriVocabulary;

class AzeriVocabularyController extends MainController
{
    public function getRandomPost()
    {
        $post = $this->model(AzeriVocabulary::class)->getRandomPost();

        return $this->template_engine->render('Cycle/AzeriVocabulary/view.html', ['post' => $post]);
    }
}