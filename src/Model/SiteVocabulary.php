<?php

namespace Model;

use Core\Core;
use Model\Channel\Message;
use Utilities\Helper;

class SiteVocabulary extends MainModel
{
    const TABLE_NAME = 'site_vocabulary';

    public function getAllLanguageVariables(): array
    {
        $languageVariables = [];
        $sql = "
        SELECT 
          sv.`lang_variable`, 
          svt.`locale`, 
          svt.`translation` 
        FROM 
          `site_vocabulary` sv 
          LEFT JOIN `site_vocabulary_translation` svt ON sv.`id` = svt.`parent_id` 
        WHERE 
          sv.`status` > 0 
          AND svt.`status` > 0;";
        $sqlRequest = $this->db()->prepare($sql);
        $sqlRequest->execute();
        $rows = $sqlRequest->fetchAll(\PDO::FETCH_OBJ);
        if($rows)
        {
            foreach ($rows as $row)
            {
                $languageVariables[$row->locale][$row->lang_variable] = $row->translation;
            }
        }

        return $languageVariables;
    }
}