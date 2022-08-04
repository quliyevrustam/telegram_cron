<?php

namespace Model\Cycle;

use Model\MainModel;
use Utilities\HtmlFormat;

class AzeriVocabulary extends MainModel
{
    const TABLE_NAME = 'azeri_vocabulary';

    private $word;
    private $description;
    public  $post;

    private function setRandomWord(): void
    {
        $row = $this->db()->query("
        SELECT 
            id,
            word,
            description
        FROM 
            azeri_vocabulary 
        WHERE showed_at IS NULL 
        ORDER BY RAND() 
        LIMIT 1;")->fetch(\PDO::FETCH_OBJ);
        if($row)
        {
            $this->id           = $row->id;
            $this->word         = $row->word;
            $this->description  = $row->description;
        }
    }

    public function setShowed(int $id): void
    {
        $request= $this->db()->prepare("UPDATE azeri_vocabulary SET showed_at =? WHERE id =?");
        $request->execute([date('Y-m-d H:i:s'), $id]);
    }

    public function getRandomPost(): string
    {
        $this->setRandomWord();
        $this->post = HtmlFormat::makeBold($this->word).' - '.HtmlFormat::makeItalic($this->description);

        return $this->post;
    }
}