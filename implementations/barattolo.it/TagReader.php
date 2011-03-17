<?
// From here: http://www.autistici.org/ermes/index.php?pag=1&post=15
// and fixed here: http://www.barattalo.it
// ------------------------------
// example:
// print_r( tagReader ("myfile.mp3") );
// ------------------------------
function tagReader($file){
	$id3v23 = array("TIT2","TALB","TPE1","TRCK","TDRC","TLEN","USLT");
	$id3v22 = array("TT2","TAL","TP1","TRK","TYE","TLE","ULT");
	$fsize = filesize($file);
	$fd = fopen($file,"r");
	$tag = fread($fd,$fsize);
	$tmp = "";
	fclose($fd);
	if (substr($tag,0,3) == "ID3") {
		$result['FileName'] = $file;
		$result['TAG'] = substr($tag,0,3);
		$result['Version'] = hexdec(bin2hex(substr($tag,3,1))).".".hexdec(bin2hex(substr($tag,4,1)));
	}
	if($result['Version'] == "4.0" || $result['Version'] == "3.0"){
		for ($i=0;$i<count($id3v23);$i++){
			if (strpos($tag,$id3v23[$i].chr(0))!= FALSE){
				$pos = strpos($tag, $id3v23[$i].chr(0));
				$len = hexdec(bin2hex(substr($tag,($pos+5),3)));
				$data = substr($tag, $pos, 10+$len);
				for ($a=0;$a<strlen($data);$a++){
					$char = substr($data,$a,1);
					if($char >= " " && $char <= "~") $tmp.=$char;
				}
				if(substr($tmp,0,4) == "TIT2") $result['Title'] = substr($tmp,4);
				if(substr($tmp,0,4) == "TALB") $result['Album'] = substr($tmp,4);
				if(substr($tmp,0,4) == "TPE1") $result['Author'] = substr($tmp,4);
				if(substr($tmp,0,4) == "TRCK") $result['Track'] = substr($tmp,4);
				if(substr($tmp,0,4) == "TDRC") $result['Year'] = substr($tmp,4);
				if(substr($tmp,0,4) == "TLEN") $result['Lenght'] = substr($tmp,4);
				if(substr($tmp,0,4) == "USLT") $result['Lyric'] = substr($tmp,7);
				$tmp = "";
			}
		}
	}
	if($result['Version'] == "2.0"){
		for ($i=0;$i<count($id3v22);$i++){
			if (strpos($tag,$id3v22[$i].chr(0))!= FALSE){
				$pos = strpos($tag, $id3v22[$i].chr(0));
				$len = hexdec(bin2hex(substr($tag,($pos+3),3)));
				$data = substr($tag, $pos, 6+$len);
				for ($a=0;$a<strlen($data);$a++){
					$char = substr($data,$a,1);
					if($char >= " " && $char <= "~") $tmp.=$char;
				}
				if(substr($tmp,0,3) == "TT2") $result['Title'] = substr($tmp,3);
				if(substr($tmp,0,3) == "TAL") $result['Album'] = substr($tmp,3);
				if(substr($tmp,0,3) == "TP1") $result['Author'] = substr($tmp,3);
				if(substr($tmp,0,3) == "TRK") $result['Track'] = substr($tmp,3);
				if(substr($tmp,0,3) == "TYE") $result['Year'] = substr($tmp,3);
				if(substr($tmp,0,3) == "TLE") $result['Lenght'] = substr($tmp,3);
				if(substr($tmp,0,3) == "ULT") $result['Lyric'] = substr($tmp,6);
				$tmp = "";
			}
		}
	}
	return $result;
}