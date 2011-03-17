<?php
class id3 {

    function id3($file) {
        $this->filename = $file;
        $this->print_errors = false;
        $this->error = false;
        $this->error_texts = array();

        define (UNSYNC, 128);
        define (EXT, 64);
        define (EXPER, 32);
        define (FOOT, 16);
        define (UNKNOWN, 15);
    }

    function adderror($text) {
        $this->error = true;
        $this->error_texts[] = $text;
        if ($this->print_errors == true) {
            echo $text;
        }
    }

    function genre($genrenumber=false) {

        $genrearray = array(
     "Blues", "Classic Rock", "Country",
     "Dance", "Disco", "Funk", "Grunge", "Hip-Hop", "Jazz",
     "Metal", "New Age", "Oldies", "Other", "Pop", "R&B",
     "Rap", "Reggae", "Rock", "Techno", "Industrial", "Alternative",
     "Ska", "Death Metal", "Pranks", "Soundtrack", "Euro-Techno", "Ambient",
     "Trip-Hop", "Vocal", "Jazz+Funk", "Fusion", "Trance", "Classical",
     "Instrumental", "Acid", "House", "Game", "Sound Clip", "Gospel",
     "Noise", "AlternRock", "Bass", "Soul", "Punk", "Space", "Meditative",
     "Instrumental Pop", "Instrumental Rock", "Ethnic", "Gothic", "Darkwave",
     "Techno-Industrial", "Electronic", "Pop-Folk", "Eurodance", "Dream",
     "Southern Rock", "Comedy", "Cult", "Gangsta", "Top 40", "Christian Rap",
     "Pop/Funk", "Jungle", "Native American", "Cabaret", "New Wave", "Psychadelic",
     "Rave", "Showtunes", "Trailer", "Lo-Fi", "Tribal", "Acid Punk", "Acid Jazz",
     "Polka", "Retro", "Musical", "Rock & Roll", "Hard Rock", "Folk", "Folk-Rock",
     "National Folk", "Swing", "Fast Fusion", "Bebob", "Latin",  "Revival",
     "Celtic", "Bluegrass", "Avantgarde", "Gothic Rock", "Progressive Rock",
     "Psychedelic Rock", "Symphonic Rock", "Slow Rock", "Big Band", "Chorus",
     "Easy Listening", "Acoustic", "Humour", "Speech", "Chanson", "Opera",
     "Chamber Music", "Sonata", "Symphony", "Booty Bass", "Primus", "Porn Groove",
     "Satire", "Slow Jam", "Club", "Tango", "Samba", "Folklore", "Ballad",
     "Power Ballad", "Rhythmic Soul", "Freestyle", "Duet", "Punk Rock",
     "Drum Solo", "A capella", "Euro-House", "Dance Hall"
    );

        if ($genrenumber === false) { return $genrearray; }
        elseif (isset($genrearray[$genrenumber])) { return $genrearray[$genrenumber]; }
        else { $this->adderror('No such genrenumber ('.$genrenumber.')'); return false; }
    }

    function artist() {
        if (isset($this->user['artist']))
            return trim($this->user['artist']);
        elseif (isset($this->v2['TPE1']) && $this->v2tag)
            return trim($this->v2['TPE1']);
        elseif (isset($this->v1['artist']) && $this->v1tag)
            return trim($this->v1['artist']);
    }

    function title() {
        if (isset($this->user['title']))
            return trim($this->user['title']);
        elseif (isset($this->v2['TIT2']) && $this->v2tag)
            return trim($this->v2['TIT2']);
        elseif (isset($this->v1['title']) && $this->v1tag)
            return trim($this->v1['title']);
    }

    function album() {
        if (isset($this->user['album']))
            return trim($this->user['album']);
        elseif (isset($this->v2['TALB']) && $this->v2tag)
            return trim($this->v2['TALB']);
        elseif (isset($this->v1['album']) && $this->v1tag)
            return trim($this->v1['album']);
    }

    function trackno() {
        return trim(( isset($this->v2['TRCK']) ? $this->v2['TRCK'] : $this->v1['track']));
    }
    function year() {
        return trim(( isset($this->v2['TYER']) ? $this->v2['TYER'] : $this->v1['year']));
    }
    function get_genre() {
        return trim(( isset($this->v2['TCON']['v2genre']) ? $this->v2['TCON']['v2genre'] : $this->genre($this->v1['genre'])));
    }
    function genreno() {
        return trim(( isset($this->v2['TCON']['v1genreno']) ? $this->v2['TCON']['v1genreno'] : $this->v1['genreno']));
    }
    function comment() {
        return trim($this->v1['comment']);
    }

    function readv1($file = -1) {
        if ($file == -1) { $file = $this->filename; }

        if (!file_exists($file)) { $this->adderror("File $file doesn't exist"); return false; }
        $fp = fopen($file, 'rb');

        if(filesize($file)<128) { $this->adderror("File $file is less than 128 bytes long"); fclose($fp); return false; }
        fseek($fp, -128, SEEK_END);
        $rawtag = fread($fp, 128);
        $tag = unpack("a3tag/a30title/a30artist/a30album/a4year/A30comment/C1genre", $rawtag);

        if ($tag['tag'] != 'TAG') {
            $this->adderror("File $file doesn't contain a id3v1 tag");
            $this->v1tag = false;
            unset($this->v1);
            fclose($fp);
            return false; }
        else { $this->v1tag = true; }

        if (substr($tag['comment'], -2, 1) == "\0") {
            $temp = unpack("a28comment/A1null/C1track", $tag['comment']);
            $this->track = $temp['track'];
            $this->comment = $temp['comment'];
        }
        else { $this->comment = trim($tag['comment']); }

        $this->v1['title'] = $tag['title'];
        $this->v1['artist'] = $tag['artist'];
        $this->v1['album'] = $tag['album'];
        $this->v1['year'] = $tag['year'];
        $this->v1['genreno'] = $tag['genre'];
        $this->v1['genre'] = $this->genre($tag['genre']);
        $this->rawv1tag = $rawtag;

        fclose($fp);
        return true;
    }

    function writev1() {
        if ($this->trackno())
            $rawtag = pack("a3a30a30a30a4a28x1C1C1", "TAG", $this->title(), $this->artist(), $this->album(), $this->year(), $this->comment(), $this->trackno(), $this->genreno());
        else
            $rawtag = pack("a3a30a30a30a4a30C1", "TAG", $this->title(), $this->artist(), $this->album(), $this->year(), $this->comment(), $this->genreno());
        if (!file_exists($this->filename)) { $this->adderror("File $file doesn't exist"); return false; }

        if ($this->readv1()) {
            rename($this->filename, $this->filename.'.temp');
            $fr = fopen($this->filename.'.temp', 'r');
            $fp = fopen($this->filename, 'w');
            fwrite($fp, fread($fr, (filesize($this->filename.'.temp') - 128)));
            fwrite($fp, $rawtag);
            fclose($fr);
            unlink($this->filename.'.temp');
        }
        else {
            if (!$fp = @fopen($this->filename, 'ab')) {
                $this->adderror("Failed opening $this->filename for writing, check permissions");
                return false;
            }
            fwrite($fp, $rawtag);
        }
        fclose($fp);
    }

    function decode_synchsafe($hex) {
        // This function shamelessly stolen from ID3v2 reader by Anders Bruun Olsen <anders@gerf.dk>
        // found at http://www.inspired.sk/php/tricks/trick.php?ID=41

        // Only works on synchsafed integers as far as I can see.

        $int = base_convert($hex, 16, 10);
        $int1 = floor($int/256) * 128 + ($int%256);
        $int2 = floor($int1/32768) * 16384 + ($int1%32768);
        $int = floor($int2/4194304) * 2097152 + ($int2%4194304);

        return $int;
    }

    function encode_synchsafe($number, $binary=0) {
        // xxx: only works on integers, and not even that.
        $return = '';
        $int = decbin($number);

        for ($i=0;$i<4;$i++) {
            $temp = str_pad(substr($int, -7), 8, 0, PAD_LEFT);
            $int = substr($int, 0, -7);
            $return = chr(bindec($temp)) . $return;
        }
        return $return;
    }

    function framesize($string) {
        $return = '';
        for($i=0;$i < strlen($string);$i++) {
            $temp = sprintf("%08b", ord($string[$i]));
            $return .= substr($temp, 1, 7);
        }
        return bindec($return);
    }

    function loadframe($name, $frame) {
        switch($name) {
            case "WXXX":
                $temp = explode("\0", substr($frame, 1));
                if (!$temp[2]) { // winamp only sets the url, not the description
                    $this->v2['WXXX']['url'] = trim($temp[1]);
                }
                else {
                    $this->v2['WXXX']['url'] = $temp[2];
                    $this->v2['WXXX']['url-description'] = trim($temp[1]);
                }
                break;
            case "COMM": // xxx: eh, there's some language thing, and summary
                $encoding = $frame[0];
                $temp = explode("\0", substr($frame, 1));
                $this->v2['COMM']['language'] = $temp[0];
                $this->v2['COMM']['content-description'] = $temp[1];
                $this->v2['COMM']['content'] = $temp[2];
                break;
            case "APIC": // xxx: not at all working, needs a proper decode_synchsafe()
                $encoding = $frame[0];
                $temp = explode("\0", substr($frame, 1));
                $this->v2['APIC']['mime-type'] = $temp[0];
                if ($this->v2['unsync']) {
                    $this->v2['APIC']['content'] = $this->decode_synchsafe($temp[1]);
                }
                break;
            case "TCON":
                $encoding = $frame[0];
                    if ($this->v2['major-version'] == 4) {

                    }
                    else {
                        // "\((\d+)\)+(.*)"
                        if (ereg("\(([[:digit:]]+)\)(.*)", substr($frame, 1), $results)) {
                            $this->v2['TCON']['v2genre'] = $results[2];
                            $this->v2['TCON']['v1genre'] = $this->genre($results[1]);
                            $this->v2['TCON']['v1genreno'] = $results[1];
                        }
                    }
                break;

            default:
                $encoding = $frame[0];
                $this->v2[$name] = substr($frame, 1);
            break;
        }
    }

    function readv2($file = -1) {
        if ($file == -1) { $file = $this->filename; }

        if (!file_exists($file)) {
            $this->adderror("File $file doesn't exist");
            return false;
        }

        $fp = fopen($file, 'rb');
        fseek($fp, 0); // xxx: we assume for now, that the tag is prepended

        // Read tag header
        $rawheader = fread($fp, 10);
        $header = unpack("a3id3/C1major/C1revision/C1flags/H8size", $rawheader);

        // Test if ID3v2 tag is present
        if ($header['id3'] != 'ID3') {
            $this->adderror("File $file doesn't contain a id3v2 tag");
            $this->v2tag = false;
            unset($this->v2);
            fclose($fp);
            return false;
        }
        else { $this->v2tag = true; }

        // Test if id3v2 is used in a format other than the supported
        $this->v2['major-version'] = $header['major'];
        if ($header['major'] > 4 && $header['major'] < 3) { $this->adderror("File $file contains a tag in a format that this script can't handle"); fclose($fp); return false; }

        // Detect header flags
        if ($header['flags'] & UNSYNC) { $this->v2['unsync'] = true; }
        if ($header['flags'] & EXT)    { $this->v2['extended'] = true; }
        if ($header['flags'] & EXPER)  { $this->v2['experimental'] = true; }
        if ($header['flags'] & FOOT)   { $this->v2['footer'] = true; }
        if ($header['flags'] & UNKNOWN){ $this->adderror("File $file has unknown header flags set, parsing aborted"); return false; }

        // Calculate tag size
        $this->v2['size'] = $this->decode_synchsafe($header['size']);

        // xxx: what should be done about unsynconisation?

        if ($this->v2['extended'] == true) {
            // Figure out the size of the extended header
            $extendedsize = fread($fp, 4);
            $temp = unpack("H8size", $extendedsize);
            $extendedsize = $this->decode_synchsafe($temp['size']);

            $rawextended = fread($fp, $extendedsize);
            // xxx: I'm not doing anything with the extended header, this
            // shouldn't do any harm in any way, but isn't exactly nice.

            $extended = 4 + $extendedsize;
        }
        else { $extended = 0; }

        // Read [extended header,] frames [and padding]
        $rawframes = fread($fp, $this->v2['size'] - $extended);

        fclose($fp); // Might as well close it, we already read the whole basheeba.

        $read = $extended;

        while($read < ($this->v2['size'] - $extended)) {
            $frameheader = unpack("a4name/a4size/C1statusflags/C1formatflags", substr($rawframes, $read, 10));
            if ($frameheader['name'] == false) {
                $this->v2['padding'] = $this->v2['size'] - $read + $extended;
                break;
            }

            $read += 10;
            $size = $this->framesize($frameheader['size']);
            $flags = $frameheader['flags'];

            $this->loadframe($frameheader['name'], substr($rawframes, $read, $size));
            //$this->v2[$frameheader['name'].'-size'] = $size;

            $read += $size;
        }

        return true;
    }

    function readtags($file = -1) {
        $this->readv2($file);
        $this->readv1($file);
    }


    function writev2dirty($outfile = NULL) {
        if ($outfile === NULL) {
            $outfile = $this->filename;
        }

        $TENC = "TENC" . $this->encode_synchsafe(0 + 1) . "@\0\0" . '';
        $WXXX = "WXXX" . $this->encode_synchsafe(0 + 2) . "\0\0\0\0" . '';
        $TCOP = "TCOP" . $this->encode_synchsafe(0 + 1) . "\0\0\0" . '';
        $TOPE = "TOPE" . $this->encode_synchsafe(0 + 1) . "\0\0\0" . '';
        $TCOM = "TCOM" . $this->encode_synchsafe(0 + 1) . "\0\0\0" . '';
        $COMM = "COMM" . $this->encode_synchsafe(5) . "\0\0\0\0" . chr(06) . "\0\0" . '';

        $TIT2 = "TIT2" . $this->encode_synchsafe(strlen($this->title()) +1) . "\0\0\0" . $this->title();
        $TRCK = "TRCK" . $this->encode_synchsafe(strlen($this->trackno()) +1) . "\0\0\0" . $this->trackno();
        $TCON = "TCON" . $this->encode_synchsafe(strlen('(' . $this->genreno() . ')' . $this->get_genre()) +1) . "\0\0\0" . '(' . $this->genreno() . ')' . $this->get_genre();
        $TYER = "TYER" . $this->encode_synchsafe(strlen($this->year()) +1) . "\0\0\0" . $this->year();
        $TALB = "TALB" . $this->encode_synchsafe(strlen($this->album()) +1) . "\0\0\0" . $this->album();
        $TPE1 = "TPE1" . $this->encode_synchsafe(strlen($this->artist()) +1) . "\0\0\0" . $this->artist();

        $tag  = '';
        $tag .= $WXXX;
        $tag .= $TCOP;
        $tag .= $TOPE;
        $tag .= $TCOM;
        $tag .= $COMM;
        $tag .= $TALB;
        $tag .= $TPE1;
        $tag .= $TENC;
        $tag .= $TIT2;
        $tag .= $TRCK;
        $tag .= $TCON;
        $tag .= $TYER;

        if ($this->readv2()) {
            if ( (strlen($tag)+10) < $this->v2['size']) {
                $tagsize = $this->v2['size'];
            }
            else {
                $tagsize = strlen($tag) +  1400;
            }
        }
        else {
            $tagsize = strlen($tag) +  1400;
        }

        $header = pack('a3h1h1h1a4', 'ID3', 0x03, 0x00, 0x00, $this->encode_synchsafe($tagsize));

        $entiretag = $header.$tag.str_repeat("\0", $tagsize - strlen($tag));

        if ($this->v2tag == true) {
            $this->adderror('remove tag, and write new file');

            if ($outfile == $this->filename) {
                rename($this->filename, $this->filename.".temp");
                $fp_read = fopen($this->filename.".temp", 'rb');
            }
            else {
                $fp_read = fopen($this->filename, 'rb');
            }
            $fp_write = fopen($outfile, 'wb');
            fwrite($fp_write, $entiretag);
            fseek($fp_read, $this->v2['size'] + 10);
            while (!feof($fp_read)) {
                fwrite($fp_write, fread($fp_read, 1024));
            }
            fclose($fp_read);
            fclose($fp_write);
                if ($outfile == $this->filename) {
                unlink($this->filename.".temp");
            }
        }
        else {
            $header = pack('a3h1h1h1a4', 'ID3', 0x03, 0x00, 0x00, $this->encode_synchsafe($tagsize));
            $entiretag = $header.$tag.str_repeat("\0", $tagsize - strlen($tag));

            if ($outfile == $this->filename) {
                rename($this->filename, $this->filename.".temp");
                $fp_read = fopen($this->filename.".temp", 'rb');
            }
            else {
                if (!$fp_read = fopen($this->filename, 'rb')) {
                    echo "error!!! failed to open ".$this->filename."\n";
                    die();
                }
            }
            if (!$fp_write = fopen($outfile, 'wb')) {
                echo "error!!! failed to open $outfile\n";
                die();
            }

            fwrite($fp_write, $entiretag);
            fseek($fp_read, 0);
            while (!feof($fp_read)) {
                fwrite($fp_write, fread($fp_read, 1024));
            }

        }
    }
}
?> 