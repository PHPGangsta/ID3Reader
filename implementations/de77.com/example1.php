<?php

include 'Id3v2.php';

$i = new Id3v2;
$res = $i->read('music.mp3');

print_r($res);
