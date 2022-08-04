<?php

namespace Model\Cycle;

use Model\MainModel;

class AzeriVocabularyParse extends MainModel
{
    public const TABLE_NAME = 'azeri_vocabulary_parse';

    private $is_suitable;
    private $word;
    public  $description;
}