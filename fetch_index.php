<?php
$html = file_get_contents('http://localhost:8000/index.php');
if (!$html) { echo "no response\n"; exit(1); }
if (preg_match('/<head.*?>(.*?)<\/head>/is', $html, $m)) {
    echo substr($m[1], 0, 1200);
} else {
    echo substr($html, 0, 1200);
}
