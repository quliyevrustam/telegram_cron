<?php

namespace Model\Cycle;

use Model\MainModel;
use Utilities\TextFormat;

class AzeriVocabulary extends MainModel
{
    public  $id;
    private $word;
    private $description;
    public  $post;

    private function setRandomWord(): void
    {
        $row = $this->database->query("
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
        $request= $this->database->prepare("UPDATE azeri_vocabulary SET showed_at =? WHERE id =?");
        $request->execute([date('Y-m-d H:i:s'), $id]);
    }

    public function getRandomPost(): string
    {
        $this->setRandomWord();
        $this->post = TextFormat::makeBold($this->word).' - '.TextFormat::makeItalic($this->description);

        return $this->post;
    }
}