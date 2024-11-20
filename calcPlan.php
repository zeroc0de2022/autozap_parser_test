<?php
declare(strict_types = 1);
/**
 * Расчет плана закупки
 * @param $pricelist
 * @param $N
 * @return array
 * Использовалась подсказка от ChatGPT, чтобы привести в порядок написанныйкод
 */
function calcPlan($pricelist, $N): array
{
    // Проверяем возможности закупки, если нет товаров, то выходим
    $available = array_sum(array_column($pricelist, 'count'));
    if($available < $N) {
        return []; // Не хватает товаров
    }

    // Рассчет цены за 1 товар
    foreach($pricelist as &$item) {
        $item['unit_price'] = $item['price'] / $item['pack'];
    }
    unset($item);

    // Сортировка по цене за единицу
    usort($pricelist, function($a, $b) {
        return $a['unit_price'] <=> $b['unit_price'];
    });

    // Подбор товаров
    $result    = [];
    $remaining = $N;
    foreach($pricelist as $item) {
        if($remaining <= 0) {
            break;
        }
        // Рассчет максимального количества товара, который можно закупить
        $max_prod_to_buy = floor($remaining / $item['pack']) * $item['pack'];
        $qty_to_buy      = min($max_prod_to_buy, floor($item['count'] / $item['pack']) * $item['pack']);
        if($qty_to_buy > 0) {
            $result[]  = ['id' => $item['id'], 'qty' => $qty_to_buy];
            $remaining -= $qty_to_buy;
        }
    }
    if($remaining > 0) {
        return [];
    }

    return $result;
}

// Пример
$pricelist = [
    ['id' => 111, 'count' => 42, 'price' => 13, 'pack' => 1],
    ['id' => 222, 'count' => 77, 'price' => 11, 'pack' => 10],
    ['id' => 333, 'count' => 103, 'price' => 10, 'pack' => 50],
    ['id' => 444, 'count' => 65, 'price' => 12, 'pack' => 5],
];
$N = 76;
$result = calcPlan($pricelist, $N);

// Вывод результата
if (empty($result)) {
    echo "Нет подходящего плана закупки.\n";
} else {
    echo "План закупки:\n";
    print_r($result);
}