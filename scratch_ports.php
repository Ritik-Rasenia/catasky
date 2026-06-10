<?php
mysqli_report(MYSQLI_REPORT_OFF);
$hosts = ['127.0.0.1', 'localhost', '[::1]'];
$ports = [3306, 3307, 3308, 3309];
foreach ($hosts as $host) {
    foreach ($ports as $port) {
        echo "Testing $host:$port...\n";
        $conn = @mysqli_connect($host, 'root', '', 'catasky', $port);
        if ($conn) {
            echo "Successfully connected on $host:$port!\n";
            mysqli_close($conn);
            exit;
        } else {
            echo "Failed: " . mysqli_connect_error() . "\n";
        }
    }
}
