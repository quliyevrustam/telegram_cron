<?php


namespace Model\Cycle;

use Exception;
use Model\MainModel;
use Utilities\Helper;
use Utilities\TextFormat;

class Quran extends MainModel
{
    public $id = [];
    public $quoteText = '';

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
            $this->id[$row->id] = $row->id;
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

        $inPlaceholder  = str_repeat('?,', count($this->id) - 1) . '?';
        $sqlRequest = $this->db()->prepare("
        SELECT 
            a.`number`,
            a.`text`
        FROM 
            `quran_ayah` a
        WHERE id IN ($inPlaceholder) AND surah_number = ?");
        $sqlRequest->execute(array_merge($this->id, [$this->surahNumber]));
        $rows = $sqlRequest->fetchAll();
        if($rows)
        {
            foreach ($rows as $row)
            {
                $quote = $quote.(TextFormat::makeItalic($row['number'] . '. ' . $row['text'] . "\n"));
            }
        }
        else
            throw new Exception('Error!');

        $quote = $quote.TextFormat::makeBold('Сура '.$this->surahNumber.', '.$this->surahName);

        return $quote;
    }

    /**
     * @throws Exception
     */
    private function checkQuoteComplete(): void
    {
        Helper::prePrint($this->id);

        // Check Ayah Text Begin
        if (preg_match('/^[а-я]/u', $this->quoteText))
        {
            $prevId             = min($this->id) - 1;
            $ayah               = $this->getAyahById($prevId);
            $this->quoteText    = $ayah['text']  . $this->quoteText;
            $this->checkQuoteComplete();
        }

        // Check Ayah Text End
        if (!in_array(substr($this->quoteText, -1, 1), ['.', '!', '?']))
        {
            $nextId             = max($this->id) + 1;
            $ayah               = $this->getAyahById($nextId);
            $this->quoteText    = $this->quoteText . $ayah['text'];
            $this->checkQuoteComplete();
        }
    }

    /**
     *
     */
    public function setShowed(): void
    {
        foreach ($this->id as $ayahId)
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
            $this->id[$row->id] = $row->id;
            ksort($this->id);

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