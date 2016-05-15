<?
		$f_body=str_replace("\\\"","\"",$f_body);
		$f_data=str_replace("\\\"","\"",$f_data);

	if($f_com!="newwin"){
?><table border=0 width=800 bgcolor=gray>
<script language=javascript>
function go_header_get_default(){
	tf=document.tform;
	tf.f_header.value="GET / HTTP/1.0\nHost: "+tf.f_host.value;
}
function go_header_post_default(){
	tf=document.tform;
	tf.f_header.value="POST / HTTP/1.0\nHost: "+tf.f_host.value+"\nContent-Type: application/x-www-form-urlencoded\nContent-Length: "+cal_text_byte(tf.f_body.value);
}

function cal_text_byte(source_text) 
{
    var text_Length = 0;
    var tot_count = 0;
    var onechar;

    text_Length = source_text.length;       // 텍스트의 문자열 길이를 받습니다 

    for (i=0;i < text_Length;i++)                // 문자열 길이만큼 루프를 돌겠습니다 
    {
        onechar = source_text.charAt(i);   // 문자열 하나를 받습니다 

        if (escape(onechar).length > 4)    // escape 함수를 이용해 길이를 구해봅니다  (자바스크립트 함수표 참조)
        {
            tot_count += 2;                        // 만약 한글이라면 2를 더하고
        }
        else if (onechar!='\r')                // 엔터가 아니라면
        {
            tot_count++;                           // 1을 더한다 
        }
    }
	return tot_count;
}

function go_newwin(){
	tf=document.tform;
	tf.f_com.value="newwin";
	tf.target="_blank";
	tf.submit();
}

function go_submit(){
	tf=document.tform;
	tf.f_com.value="";
	tf.target="";
	return true;
}

</script>
<form name="tform" action="<?=$PHP_SELF;?>" method="post" onsubmit="return go_submit();">
<input type="hidden" name="f_com" value="">
<tr><td align=center width=150 bgcolor=#efefef>IP(HOST)</td><td bgcolor=white><input type="text" size=40 name="f_host" value="<?=$f_host?>"></td></tr>
<tr><td align=center width=150 bgcolor=#efefef>PORT</td><td bgcolor=white><input type="text" size=40 name="f_port" value="80"></td></tr>
<tr><td align=center width=150 bgcolor=#efefef>HEADER</td><td bgcolor=white><textarea type="text" style="width:650px;height=100px" name="f_header"><?=htmlspecialchars($f_header)?></textarea>
<input type="button" onclick="go_header_get_default()" value="Default header GET">
<input type="button" onclick="go_header_post_default()" value="POST">
</td></tr>
<tr><td align=center width=150 bgcolor=#efefef>BODY</td><td bgcolor=white><textarea type="text" style="width:650px;height=200px" name="f_body"><?=htmlspecialchars($f_body)?></textarea></td></tr>
<tr><td align=center width=150 bgcolor=#efefef>DATA</td><td bgcolor=white><textarea type="text" style="width:650px;height=200px" name="f_data"><?=htmlspecialchars($f_data)?></textarea></td></tr>
<tr><td align=center width=150 bgcolor=#efefef>Option </td><td bgcolor=white><input type="checkbox" name="f_auto_cl"  value="1">Auto Content-Length</td></tr>
<tr><td align=center colspan=2 bgcolor=white><input type="submit" value="전송"><input type="button" 
value="새창보기" name="f_newwin" onclick="go_newwin();"></td></tr>
</form>
</table>
<hr>
<?
}
?>
<base href="http://<?=$f_host?>/"> 
<?
$f_data=trim($f_data);
$f_header=trim($f_header);
$f_body=trim($f_body);

if($f_host!="" && $f_port!="" && ($f_header || $f_data)){
	
	if($f_data==""){
		$PutData=$f_header;
		if(strlen($f_body)>0){
			if($f_auto_cl){
				$PutData.="
Content-Length: ".strlen($f_body)."
";
			}
			$PutData.="\r\n\r\n".$f_body;
		}
	}else{
		$PutData=$f_data;
	}

	$PutData.="\r\n\r\n";
	echo "<hr><table border=0 bgcolor=black><tr><td><font color=white><B>REQUEST</font></td></tr></table><pre>".htmlspecialchars($PutData)."</pre>";

	if($f_port==443){
		$fp = @fsockopen("ssl://".$f_host, $f_port, $errno, $errstr, 3);
	}else{
		$fp = @fsockopen($f_host, $f_port, $errno, $errstr, 3);
	}
	if (!$fp) {
		return "$errstr ($errno) ".$f_host.":".$f_port;
	} else {
		//웹
		$PutData=$PutData;
		fputs ($fp,$PutData);
		$Line =0;
		$step=0;
		while (!feof($fp)) {
			$Line++;
			$tmps =fgets($fp,1024); //fgets($fp,4096)
			if($step<1){
				//헤더추가
				$header.=$tmps;
			}else{
				//바디추가
				$body.=$tmps;
			}
			if(ereg("^.[\r\n]$",$tmps)) $step++;
		}
	}
	fclose($fp);
	echo "<hr><table border=0 bgcolor=black><tr><td><font color=white><B>HEADER</font></td></tr></table><pre>".$header;
	echo "<hr><table border=0 bgcolor=black><tr><td><font color=white><B>BODY HTML</font></td></tr></table><pre>".htmlspecialchars($body)."</pre>";
	if($f_com=="newwin"){
		echo "<hr><table border=0 bgcolor=black><tr><td><font color=white><B>BODY VIEW</font></td></tr></table>".$body;
	}
}






function getData(){
	srand((double)microtime()*1000000);
	$boundary = "---------------------------".substr(md5(rand(0,32000)),0,10);

	$tmp_value=join("", file("ws_sample.gif"));
	$data="--$boundary
Content-Disposition: form-data; name=\"f_file1\"; filename=\"ws_sample.gif\"
Content-Type: image/gif

$tmp_value
--$boundary";

$data.="--\r\n\r\n";

	$msg="POST /asp/test/req.asp HTTP/1.0
Accept: image/gif, image/jpeg, image/pjpeg, */*
Referer: http://cms.shinsegae.com/
Accept-Language: ko
Content-Type: multipart/form-data; boundary=".$boundary."
Proxy-Connection: Keep-Alive
User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; .NET CLR 1.1.4322)
Host: cms.shinsegae.com
Content-Length: " . strlen ( $data ). "
Pragma: no-cache
Cookie:\r\n\r\n".$data;

	echo "<pre>".$msg."</pre>";
	return $msg;
}


?>

