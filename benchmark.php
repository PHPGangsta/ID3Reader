<?php

$allFilenames = array('mp3s/Jonay Ft Jasmine Kara - Skydiving.mp3', 'mp3s/Meditative.mp3', 'mp3s/The Vision.mp3', 'mp3s/Heroines.mp3', 'mp3s/5-jahre-cre.mp3', 'mp3s/Security Now 195_ SSL.mp3');

// http://de77.com/php/php-class-how-to-read-id3v2-tags-from-mp3-files
require 'implementations/de77.com/Id3v2.php';
$i = new Id3v2();

echo "####### de77.com #######\n";
foreach ($allFilenames as $filename) {
    echo "Working on file ".$filename."\n";
    $res = $i->read($filename);
    print_r($res);
}


// http://code.google.com/p/php-reader/wiki/ID3v2
set_include_path('implementations/php-reader-1.8/library/');

require 'Zend/Media/Id3v1.php'; // or using autoload

echo "####### Zend/Media/Id3v1 #######\n";
foreach ($allFilenames as $filename) {
    echo "Working on file ".$filename."\n";
    try {
        $id3 = new Zend_Media_Id3v1($filename);
        echo "Title: " . $id3->getTitle() . "\n";
        echo "Album: " . $id3->getAlbum() . "\n";
        echo "Year: " . $id3->getYear() . "\n";
    } catch (Zend_Media_Id3_Exception $e) {
        echo "No ID3v1 Tag found.\n";
    }
}


require 'Zend/Media/Id3v2.php'; // or using autoload

echo "####### Zend/Media/Id3v2 #######\n";
foreach ($allFilenames as $filename) {
    echo "Working on file ".$filename."\n";
    try {
        $id3 = new Zend_Media_Id3v2($filename);
        $frame = $id3->getFramesByIdentifier("TIT2");
        echo "Title: " . $frame[0]->getText() . "\n";

        $frame = $id3->getFramesByIdentifier("TALB");
        echo "Album: " . $frame[0]->getText() . "\n";

        $frame = $id3->getFramesByIdentifier("APIC"); // for attached picture
        echo "ImageType: " . $frame[0]->getImageType() . "\n";
    } catch (Zend_Media_Id3_Exception $e) {
        echo "No ID3v2 Tag found.\n";
    }
}




// http://www.barattalo.it/2010/02/22/reading-mp3-informations-with-php-id3-tags/
require 'implementations/GetID3/getid3/getid3.php';
$getID3 = new getID3();

echo "####### getID3 #######\n";
foreach ($allFilenames as $filename) {
    echo "Working on file ".$filename."\n";

    $ThisFileInfo = $getID3->analyze($filename);

    echo $ThisFileInfo['tags']['id3v2']['title'][0] . "\n";
    echo $ThisFileInfo['audio']['bitrate'] . "\n";
    echo $ThisFileInfo['playtime_string'] . "\n";

    //print_r($ThisFileInfo);
}
