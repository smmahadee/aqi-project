<?php

$city = null;
if (!empty($_GET['city'])) {
    $city = $_GET['city'];
}

$city_content = null;
if (!empty('city')) {
    $cities = json_decode(file_get_contents(__DIR__ . '/data/index.json'), true);

    foreach ($cities as $currentCity) {
        if ($currentCity['city'] === $city) {
            $city_content = $currentCity;
            break;
        }
    }
}

$results = null;
if (!empty($city_content)) {
    $json_content = json_decode(file_get_contents('compress.bzip2://' . __DIR__ . '/data/' . $city_content['filename']), true);
    $results = $json_content['results'];
}

if (!empty($results)) {

    foreach ($results as $result) {
        if ($result['parameter'] !== 'pm25' && $result['parameter'] !== 'pm10') continue;

        // set months to stats
        $month = substr($result['date']['local'], 0, 7);
        if (!isset($stats[$month])) {
            $stats[$month] = [
                'pm25' => [],
                'pm10' => []
            ];
        }

        if ($result['value'] < 0) continue;

        $stats[$month][$result['parameter']][] = $result['value'];
    }
    // var_dump($stats);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="styles/simple.css" />
    <title>Document</title>

</head>

<body>
    <header>
        <h1>AQI-</h1>
        <nav><a href="index.php">Overview</a></nav>
    </header>
    <main>

        <?php if (empty($city_content)) : ?>
            <p>The city couldn't be loaded</p>
        <?php endif; ?>

        <?php if (!empty($stats)) : ?>
            <canvas style="width: 300px; height: 200px;" id="ctx"></canvas>


            <?php
            $labels = array_keys($stats);
            sort($labels);

            $pm25 = [];
            $pm10 = [];
            foreach ($labels as $label) {
                $measurements = $stats[$label];

                if (count($measurements['pm25']) !== 0) {
                    $pm25[] = round(array_sum($measurements['pm25']) / count($measurements['pm25']));
                }else {
                    $pm[] = 0;
                }

                if (count($measurements['pm10']) !== 0) {
                    $pm10[] = round(array_sum($measurements['pm10']) / count($measurements['pm10']));
                }else {
                    $pm[] = 0;
                }
            }

            $datasets = [];
            if ($measurements['pm25'] > 0) {
                $datasets[] = [
                    'label' => 'My First Dataset',
                    'data' =>  $pm25,
                    'fill' => false,
                    'borderColor' => 'rgb(75, 192, 192)',
                    'tension' => 0.1
                ];
            }

            if ($measurements['pm10'] > 0) {
                $datasets[]  = [
                    'label' => 'My Second Dataset',
                    'data' => $pm10,
                    'fill' => false,
                    'borderColor' => 'rgb(75, 12, 192)',
                    'tension' => 0.1
                ];
            }
            ?>

            <table>
                <thead>
                    <th>Month</th>
                    <th>PM 2.5</th>
                    <th>PM 1.0</th>
                </thead>
                <?php foreach ($stats as $month => $measurements) : ?>
                    <tr>
                        <th><?php echo $month ?></th>
                        <?php if (count($measurements['pm10']) !== 0) : ?>
                            <th><?php echo round(array_sum($measurements['pm10']) / count($measurements['pm10']), 2) ?> </th>
                        <?php else : ?>
                            <th><i>No data available</i></th>
                        <?php endif; ?>

                        <?php if (count($measurements['pm25']) !== 0) : ?>
                            <th><?php echo round(array_sum($measurements['pm25']) / count($measurements['pm25']), 2) ?> </th>
                        <?php else : ?>
                            <th><i>No data available</i></th>
                        <?php endif; ?>

                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

    </main>

    <script src="scripts/chart.umd.js"> </script>
    <script>
        const ctx = document.getElementById('ctx');

        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels) ?>,
                datasets: <?php echo json_encode($datasets); ?>,
            },
            options: {
                onClick: (e) => {
                    const canvasPosition = getRelativePosition(e, chart);

                    // Substitute the appropriate scale IDs
                    const dataX = chart.scales.x.getValueForPixel(canvasPosition.x);
                    const dataY = chart.scales.y.getValueForPixel(canvasPosition.y);
                }
            }
        });
    </script>

</body>

</html>