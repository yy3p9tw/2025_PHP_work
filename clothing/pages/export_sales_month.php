<?php
require_once '../includes/db.php';
$Sale = new DB('sales');
$Item = new DB('items');
$Variant = new DB('item_variants');
$Color = new DB('colors');
$items = $Item->all();
$variants = $Variant->all();
$colors = $Color->all();
$itemMap = [];
foreach($items as $i) $itemMap[$i['id']] = $i;
$variantMap = [];
foreach($variants as $v) $variantMap[$v['id']] = $v;
$colorMap = [];
foreach($colors as $c) $colorMap[$c['id']] = $c['name'];
$thisMonth = date('Y-m');
$sales = $Sale->all("sale_date LIKE '$thisMonth%'");
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="sales_month_'.date('Ym').'.csv"');
echo "商品,顏色,成本,售價,數量,總價,日期\n";
foreach($sales as $sale) {
    $variant = $variantMap[$sale['item_id']] ?? null;
    $item = $variant && isset($itemMap[$variant['item_id']]) ? $itemMap[$variant['item_id']] : null;
    $color = $variant && isset($colorMap[$variant['color_id']]) ? $colorMap[$variant['color_id']] : '';
    $row = [
        $item ? $item['name'] : '(已刪除)',
        $color,
        $variant ? intval($variant['cost_price']) : '-',
        $variant ? intval($variant['sell_price']) : '-',
        intval($sale['quantity']),
        intval($sale['total_price']),
        isset($sale['sale_date']) ? $sale['sale_date'] : ''
    ];
    foreach($row as &$v) $v = str_replace(["\r","\n",","], [' ',' ',' '], $v);
    echo implode(',', $row) . "\n";
}
