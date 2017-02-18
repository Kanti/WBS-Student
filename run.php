<?php

require "vendor/autoload.php";


$csv = array_map("str_getcsv", file("data/52f82-emgdata-3.csv", FILE_SKIP_EMPTY_LINES));
$keys = array_shift($csv);

foreach ($csv as $i => $row) {
    $csv[$i] = array_combine($keys, array_map('floatval', $row));
}
//var_dump($csv);

$samples = [];
$labels = [];
$testSamples = [];
$testLabels = [];
foreach ($csv as $row) {
    $class = (int)$row['Class'];
    if ((int)$row['Subject'] < 2) {
        $labels[] = (int)$class;
        $samples[] = array_values(array_slice($row, 0, 18));
    } else {
        $testLabels[$class][] = $class;
        $testSamples[$class][] = array_values(array_slice($row, 0, 18));
    }
}
//var_dump($samples);

use Phpml\Classification\KNearestNeighbors;

$classifier = new KNearestNeighbors();
$classifier->train($samples, $labels);
foreach ($testLabels as $dataId => $labels) {
    $countGood = 0;
    $countBad = 0;
    $testLabelsPredicted = $classifier->predict($testSamples[$dataId]);
    foreach ($testLabelsPredicted as $key => $predicted) {
        if ((int)$predicted !== $testLabels[$dataId][$key]) {
            $countBad++;
            //echo $predicted . ' shouldbe ' . $testLabels[$dataId][$key] . PHP_EOL;
        } else {
            $countGood++;
           // echo $predicted . ' is GOOD_ ' . $testLabels[$dataId][$key] . PHP_EOL;
        }
    }
    echo $countGood . ' good vs bad ' . $countBad . PHP_EOL;
    echo round($countGood / count($testLabelsPredicted) * 100,2) . '%' . PHP_EOL;
}