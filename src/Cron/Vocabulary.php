<?php

namespace Cron;

use Model\Cycle\AzeriVocabulary;
use Utilities\Cron;
use danog\MadelineProto\API;
use GuzzleHttp\Client as httpClient;
use Utilities\Helper;

class Vocabulary extends Cron
{
    public function actionPostWord(): void
    {

        try {
            $vocabulary = new AzeriVocabulary();
            $channelPost = $vocabulary->getRandomPost();

            $settings['app_info']['api_id'] = APP_API_ID;
            $settings['app_info']['api_hash'] = APP_API_HASH;
            $madelineProto = new API(MADELINE_SESSION_PATH, $settings);
            $madelineProto->start();

            $result = $madelineProto->messages->sendMessage(
                [
                    'peer'       => CHANNEL_KEY_AZERI_WORD,
                    'message'    => $channelPost,
                    'parse_mode' => 'HTML'
                ]
            );

            if(isset($result['updates'][0]['id']))
            {
                echo "New Post ID: ".$result['updates'][0]['id']."\n";
                $vocabulary->setShowed($vocabulary->id);
            }
        }
        catch (\Throwable $exception)
        {
            echo $exception->getCode()."\n";
            echo $exception->getMessage()."\n";
        }
    }

/*
SELECT COUNT(*) FROM `azeri_vocabulary_parse`;

SELECT * FROM `azeri_vocabulary_parse` ORDER BY id DESC LIMIT 1;

DELETE FROM `telegram`.`azeri_vocabulary_parse` WHERE `word` LIKE '%?%';
DELETE FROM `telegram`.`azeri_vocabulary_parse` WHERE `word` LIKE '%̇%';
DELETE FROM `telegram`.`azeri_vocabulary_parse` WHERE `word` LIKE '%̇%';
DELETE FROM `telegram`.`azeri_vocabulary_parse` WHERE `word` LIKE '%̇%';

SELECT
  *
FROM
  `azeri_vocabulary_parse`
WHERE
  `status` > 0
  AND `is_suitable` = 0
ORDER BY RAND()
LIMIT 20;

SELECT * FROM `azeri_vocabulary` WHERE `showed_at` IS NULL ORDER BY 4 ASC LIMIT 0, 1000;
*/

    public function actionParser(): void
    {
        $i = rand(1, 450);
        $j = $i + 10;
        for ($i; $i <= $j; $i++)
        {
            echo 'https://obastan.com/azerbaycanca-rusca-luget-2/a/?l=ru&p='.$i."\n";
            $html = file_get_contents('https://obastan.com/azerbaycanca-rusca-luget-2/a/?l=ru&p='.$i);
            $doc = new \DOMDocument();
            libxml_use_internal_errors(true);
            $doc->loadHTML($html);
            $elements = $doc->getElementsByTagName('a');

            if (!is_null($elements))
            {
                foreach ($elements as $element)
                {
                    $this->processParsedElement($element);
                }
            }
        }

        $sql = "UPDATE `azeri_vocabulary_parse` SET `word` = REPLACE(`word`,'?', '') WHERE `word` LIKE '%?%';";
        $sql .= "UPDATE `azeri_vocabulary_parse` SET `word` = REPLACE(`word`, '̇', '') WHERE `word` LIKE '%̇%';";
        $sql .= "UPDATE `azeri_vocabulary_parse` SET `word` = REPLACE(`word`, '̇', '') WHERE `word` LIKE '%̇%';";
        $sql .= "UPDATE `azeri_vocabulary_parse` SET `word` = REPLACE(`word`, '̇', '') WHERE `word` LIKE '%̇%';";
        $sql .= "UPDATE `azeri_vocabulary_parse` SET `word` = REPLACE(`word`, '̇', '') WHERE `word` LIKE '%̇%';";
        $this->db()->prepare($sql)->execute();

        $sql = "
        DELETE FROM `azeri_vocabulary_parse` WHERE `id` IN (
            SELECT cid FROM (
            SELECT `id` AS cid
            FROM `azeri_vocabulary_parse` 
            WHERE 
                `word` IN (SELECT `word` FROM `azeri_vocabulary_parse` GROUP BY `word` HAVING COUNT(*) > 1) AND 
                `id` NOT IN (SELECT MIN(id) FROM `azeri_vocabulary_parse` GROUP BY `word` HAVING COUNT(*) > 1)
            ) AS c
        );  
        ";
        $this->db()->prepare($sql)->execute();
    }

    private function processParsedElement($element): void
    {
        try
        {
            if($element->getAttribute('class') == 'wli-link')
            {
                $data = [];
                $html = file_get_contents('https://obastan.com'.$element->getAttribute('href'));
                $doc = new \DOMDocument();
                libxml_use_internal_errors(true);
                $doc->loadHTML($html);
                $elements = $doc->getElementsByTagName('h1');
                foreach ($elements as $element)
                {
                    if($element->getAttribute('itemprop') == 'headline')
                    {

                        $from = ["u00e7","u0131","u00fc","u011f","u00f6","u015f","u0130","u011e","u00dc","u00d6","u015e","u00c7"];
                        $to = array("ç","ı","ü","ğ","ö","ş","İ","Ğ","Ü","Ö","Ş","Ç");
                        $element->nodeValue = str_replace($from, $to, $element->nodeValue);

                        $element->nodeValue = trim(str_replace(['̇', '̇', '̇', '̇', '̇', '̇', '̇', '̇', '̇'], '', $element->nodeValue));
                        $element->nodeValue = trim(str_replace(["i̇"], ['i'], $element->nodeValue));
                        $element->nodeValue = trim(str_replace(["̇"], [''], $element->nodeValue));
                        $element->nodeValue = iconv(mb_detect_encoding($element->nodeValue), "UTF-8", $element->nodeValue);

                        $data['word'] = Helper::MbUcfirst(mb_strtolower($element->nodeValue));
                    }
                }
                $elements = $doc->getElementsByTagName('div');
                foreach ($elements as $element)
                {
                    if($element->getAttribute('itemprop') == 'articleBody')
                        $data['description'] = trim($element->nodeValue);
                }

                $sqlRequest = $this->db()->prepare("
                SELECT 
                    id
                FROM 
                    azeri_vocabulary_parse
                WHERE word = :word;");
                $sqlRequest->execute(['word' => $data['word']]);
                $row = $sqlRequest->fetch(\PDO::FETCH_OBJ);
                if(!$row)
                {
                    $sql = "
                        INSERT INTO 
                            azeri_vocabulary_parse (word, description, created_at) 
                        VALUES 
                            (:word, :description, NOW())";
                    $this->db()->prepare($sql)->execute($data);
                    //echo 'Add new word ' . $data['word'] . "\n";
                }
                else
                {
                    //echo 'Duplicate word ' . $data['word'] . "\n";
                }
            }
        }
        catch (\Throwable $exception)
        {
            echo $exception->getCode()."\n";
            echo $exception->getMessage()."\n";
        }
    }

    public function actionAddParsedSuitableWord(): void
    {
        $sql = "
        SELECT 
          `word`, 
          `description`
        FROM 
          `azeri_vocabulary_parse` 
        WHERE 
          `status` > 0 AND `is_suitable` = 1;";
        $sqlRequest = $this->db()->prepare($sql);
        $sqlRequest->execute();
        $rows = $sqlRequest->fetchAll(\PDO::FETCH_OBJ);
        if($rows)
        {
            foreach ($rows as $row)
            {
                try
                {
                    $data['word'] = $row->word;
                    $data['description'] = $row->description;

                    $sql = "
                        INSERT INTO 
                            azeri_vocabulary (word, description, created_at) 
                        VALUES 
                            (:word, :description, NOW())";
                    $this->db()->prepare($sql)->execute($data);
                }
                catch (\Throwable $exception)
                {
                    echo $exception->getCode()."\n";
                    echo $exception->getMessage()."\n";
                }
            }
        }
    }
}