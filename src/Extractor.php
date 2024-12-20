<?php
declare(strict_types = 1);
/***
 * Date 18.11.2024
 * @author zeroc0de <98693638+zeroc0de2022@users.noreply.github.com>
 */


namespace Parser;

use DiDom\Document;
use DiDom\Exceptions\InvalidSelectorException;


class Extractor
{
    use Common;

    // Сайт для парсинга
    private string $sitename;
    // Ключевое слово для поиска
    private string $keyword;
    // Массив с результатами
    private array $content;

    /**
     * Инициализацияы
     * @param $keyword
     */
    public function __construct($keyword)
    {
        $this->sitename = 'https://www.autozap.ru';
        $this->keyword  = $keyword;
        $this->content  = [];
    }


    /**
     * @throws InvalidSelectorException
     */
    public function loadPage(): array
    {
        // Загрузка страницы
        $html = (new Curl())->request([
            'url'  => $this->sitename . '/goods',
            'post' => 'code=' . $this->keyword . '&count=300&page=1&search=%D0%9D%D0%B0%D0%B9%D1%82%D0%B8'
        ]);
        $document = new Document($html['body']);
        // Определение типа таблицы с результатами
        (str_contains($html['body'], 'ordCart'))
            ? $this->getContent($document)
            : $this->getTablePage($document);
        return $this->content;
    }


    /**
     * Вторичный парсинг страницы, требующей клика по цене
     * @param $document
     * @return void
     * @throws InvalidSelectorException
     */
    private function getTablePage($document): void
    {
        $ahref = $document->find('table#tabGoods > tr > td > a#goodLnk1');
        if(isset($ahref[0])){
            $html  = (new Curl())->request([
                'url' => $this->sitename . $ahref[0]->getAttribute('href')
            ]);
            $this->parseDocument($html['body']);
        }

    }


    /**
     *
     * @throws InvalidSelectorException
     */
    private function parseDocument($html): void
    {
        $document = new Document($html);
        if(str_contains($html, 'ordCart')) {
            $this->getContent($document);
        }
    }

    /**
     * Парсинг данных в массив
     * @param $document
     * @return void
     * @throws InvalidSelectorException
     */
    private function getContent($document): void
    {
        $i = 0;
        foreach($document->find('table#tabGoods > tr') as $item_tr) {
            $item_td = $item_tr->find('td');
            if(!$item_tr->has('.ordCart') || count($item_td) < 13) {
                continue;
            }
            $this->content[] = [
                'name'    => $this->getValueById($item_td[12], 'ecomName' . $i + 1),
                'price'   => $this->getValueById($item_td[12], 'ecomPrice' . $i + 1),
                'article' => $this->getValueById($item_td[12], 'ecomCode' . $i + 1),
                'brand'   => $this->getValueById($item_td[12], 'ecomManuf' . $i + 1),
                'count'   => trim($item_td[9]->text()),
                'time'    => intval($item_td[10]->text()),
                'id'      => $this->getValueById($item_td[9], 'g' . $i + 1),
            ];
            $i++;
        }
    }


    /**
     * Получение значения по id, для Didom
     * @throws InvalidSelectorException
     */
    private function getValueById($document, string $id): ?string
    {
        $input = $document->find("input[id=$id]");
        return $input
            ? $input[0]->getAttribute('value')
            : null;
    }
}