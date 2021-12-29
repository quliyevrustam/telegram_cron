<?php

namespace Model\Cycle;

use Exception;
use Model\MainModel;
use Utilities\HtmlFormat;

class Quran extends MainModel
{
    private const MIN_AYAH_LENGTH = 81;

    public array $ids = [];
    public string $quoteText = '';

    private $surahNumber;
    private $surahName;

    /**
     * @return string
     * @throws Exception
     */
    public function getRandomQuote(): string
    {
        $row = $this->db()->query("
        SELECT 
            a.`id`,
            a.`surah_number`,
            s.`name` AS surah_name,
            a.`number`,
            a.`text`
        FROM 
            `quran_ayah` a
            LEFT JOIN `quran_surah` s ON a.`surah_number` = s.`number`
        WHERE showed_at IS NULL 
        ORDER BY RAND() 
        LIMIT 1;")->fetch(\PDO::FETCH_OBJ);
        if($row)
        {
            $this->ids[$row->id] = $row->id;
            $this->surahNumber  = $row->surah_number;
            $this->surahName    = $row->surah_name;
            $this->quoteText    = $row->text;
        }
        else
            throw new Exception('Error!');

        $this->checkQuoteComplete();

        return $this->generateQuote();
    }

    /**
     * @return string
     * @throws Exception
     */
    private function generateQuote(): string
    {
        $quote = '';

        $inPlaceholder  = str_repeat('?,', count($this->ids) - 1) . '?';
        $sqlRequest = $this->db()->prepare("
        SELECT 
            a.`number`,
            a.`text`
        FROM 
            `quran_ayah` a
        WHERE id IN ($inPlaceholder) AND surah_number = ?");
        $sqlRequest->execute(array_merge($this->ids, [$this->surahNumber]));
        $rows = $sqlRequest->fetchAll();
        if($rows)
        {
            foreach ($rows as $row)
            {
                $quote = $quote.(HtmlFormat::makeItalic($row['number'] . '. ' . $row['text'] . "\n"));
            }
        }
        else
            throw new Exception('Error!');

        $quote = $quote.HtmlFormat::makeBold('Сура '.$this->surahNumber.', '.$this->surahName);

        return $quote;
    }

    /**
     * @throws Exception
     */
    private function checkQuoteComplete(): void
    {
        // Check Ayah Text Begin
        if (preg_match('/^[а-я]/u', $this->quoteText))
        {
            //echo "\n".'Not TEXT begin'."\n";
            $prevId             = min($this->ids) - 1;
            $ayah               = $this->getAyahById($prevId);
            $this->quoteText    = $ayah['text']  . $this->quoteText;
            $this->checkQuoteComplete();
        }

        // Check Ayah Text End
        if (!in_array(substr($this->quoteText, -1, 1), ['.', '!', '?']))
        {
            //echo "\n".'Not TEXT end'."\n";
            $nextId             = max($this->ids) + 1;
            $ayah               = $this->getAyahById($nextId);
            $this->quoteText    = $this->quoteText . $ayah['text'];
            $this->checkQuoteComplete();
        }

        // Check Ayah Text Length
        if(min($this->ids) != 1 && mb_strlen($this->quoteText) <= self::MIN_AYAH_LENGTH)
        {
            //echo "\n".'TEXT too short'."\n";
            $prevId             = min($this->ids) - 1;
            $ayah               = $this->getAyahById($prevId);
            $this->quoteText    = $ayah['text']  . $this->quoteText;
            $this->checkQuoteComplete();
        }
    }

    /**
     *
     */
    public function setShowed(): void
    {
        foreach ($this->ids as $ayahId)
        {
            $request= $this->db()->prepare("UPDATE quran_ayah SET showed_at =? WHERE id =?");
            $request->execute([date('Y-m-d H:i:s'), $ayahId]);
        }
    }

    /**
     * @param int $id
     * @return array
     * @throws Exception
     */
    private function getAyahById(int $id): array
    {
        $sqlRequest = $this->db()->prepare(
            "
        SELECT 
            a.`id`,
            a.`number`,
            a.`text`
        FROM 
            `quran_ayah` a
        WHERE a.`id` =?"
        );
        $sqlRequest->execute([$id]);
        $row = $sqlRequest->fetch(\PDO::FETCH_OBJ);
        if ($row)
        {
            $this->ids[$row->id] = $row->id;
            ksort($this->ids);

            $ayah = [
                'id'     => $row->id,
                'number' => $row->number,
                'text'   => $row->text,
            ];
        } else
            {
            throw new Exception('Error!');
        }

        return $ayah;
    }
}