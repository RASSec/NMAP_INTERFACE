<?

//헤더 및 enctype에 따른 컨텐츠 만들기
function MakeContent($TargetHost,$LinkName,$QueryString,$Parent_doc,$FormMethod,$FormEnctype,$FormAryInput){
	global $Env;
	if($Env["f_debug_yn"]){
		echo "<BR><BR>MakeContent";
		echo "<BR>TargetHost:".$TargetHost;
		echo "<BR>LinkName:".$LinkName;
		echo "<BR>QueryString:".$QueryString;
		echo "<BR>Parent_doc:".$Parent_doc;
		echo "<BR>FormMethod:".$FormMethod;
	}
	if($FormMethod=="POST" && $FormEnctype=="MULTIPART/FORM-DATA"){
		if($Env["f_debug_yn"])echo "<br><font color=blue>POST MULTIPART/FORM-DATA</font>";
		return MakePostDataHeader($TargetHost,$LinkName,$QueryString,$Parent_doc,$FormAryInput);
	}else if($FormMethod=="POST"){
		if($Env["f_debug_yn"])echo "<br><font color=blue>POST</font>";
		return MakePostHeader($TargetHost,$LinkName,$QueryString,$Parent_doc,$FormAryInput);
	}else{
		//GET
		if($Env["f_debug_yn"])echo "<br><font color=blue>GET</font>";
		return MakeGetHeader($TargetHost,$LinkName,$QueryString,$Parent_doc,$FormAryInput);
	}
}

//POST헤더 (파일첨부 MUTLTI_PART/FORM-DATA
function MakePostDataHeader($TargetHost,$LinkName,$QueryString,$Parent_doc,$FormAryInput){
	global $Env;
	global $f_auth_cookie;

	$ReturnValue="";

	srand((double)microtime()*1000000);
	$boundary = "---------------------------".substr(md5(rand(0,32000)),0,10);

	$data = "--$boundary";
	
	//링크
	if($QueryString!=""){
		$GetLastPath = $LinkName."?".GetQueryurlencode($QueryString,$ForceEncode=false);
	}else{
		$GetLastPath = $LinkName;
	}
	if($Env["f_debug_yn"] ) echo "\n<BR>GetLastPath:".$GetLastPath;

	for($i=1;$i<count($FormAryInput);$i++){
		$tmp_name=$FormAryInput[$i]["name"];
		$tmp_value=GetQueryurlencode($FormAryInput[$i]["value"],$ForceEncode=false);
		$tmp_type=$FormAryInput[$i]["type"];
		if($tmp_type=="FILE"){
			$content_type="image/gif";
			$tmp_filename=$tmp_name.$i.".gif";
			$tmp_value=join("", file($Env["AttachFile"]));
	   $data.="
Content-Disposition: form-data; name=\"".$tmp_name."\"; filename=\"".$tmp_filename."\"
Content-Type: $content_type

$tmp_value
--$boundary";
		}else{
	   $data.="
Content-Disposition: form-data; name=\"".$tmp_name."\"

$tmp_value
--$boundary";
		}
	}

	$data.="--\r\n\r\n";

$header =
"POST ".$GetLastPath." HTTP/1.0
Accept: image/gif, image/jpeg, image/pjpeg, */*
Referer: http://".$TargetHost.$Parent_doc."
Accept-Language: ko
Content-Type: multipart/form-data; boundary=".$boundary."
Proxy-Connection: Keep-Alive
User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; .NET CLR 1.1.4322)
Host: $TargetHost
Content-Length: " . strlen ( $data ). "
Pragma: no-cache
Cookie: ".$f_auth_cookie."\r\n\r\n"; 

	$ReturnValue=$header.$data;

	return $ReturnValue;
}

//POST헤더 만들어 오기
function MakePostHeader($TargetHost,$LinkName,$QueryString,$Parent_doc,$FormAryInput){
	global $Env;
	global $f_auth_cookie;

	$data="";

	//echo "<BR>QueryString:".$QueryString;
	//echo "<BR>FormAryInput CNT:".count($FormAryInput);

	//링크
	if($QueryString!=""){
		$GetLastPath = $LinkName."?".GetQueryurlencode($QueryString,$ForceEncode=false);
	}else{
		$GetLastPath = $LinkName;
	}

	//폼 데이터 만들기
	for($i=1;$i<count($FormAryInput);$i++){
		if($data!="")$data.="&";
		$tmp_name=$FormAryInput[$i]["name"];
		$tmp_value=$FormAryInput[$i]["value"];
		$tmp_type=$FormAryInput[$i]["type"];
		$data.=$tmp_name."=".GetQueryurlencode($tmp_value,$ForceEncode=false);
	}

	if($Env["f_debug_yn"] ) echo "\n<BR>GetLastPath:".$GetLastPath;
$msg =
"POST ".$GetLastPath." HTTP/1.0
Accept: image/gif, image/jpeg, image/pjpeg, */*
Referer: http://".$TargetHost.$QueryString."
Accept-Language: ko
Content-Type: application/x-www-form-urlencoded
Proxy-Connection: Keep-Alive
User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; .NET CLR 1.1.4322)
Host: $TargetHost
Content-Length: " . strlen ( $data ). "
Pragma: no-cache
Cookie: $f_auth_cookie 

$data
";
	return $msg;
}


//GET헤더 만들어 오기
function MakeGetHeader($TargetHost,$LinkName,$QueryString,$Parent_doc,$FormAryInput){
	global $Env;
	global $f_auth_cookie;

	$GetLastPath="";

	if($Env["f_debug_yn"] ){
		echo "<BR><BR>MakeGetHeader:";
		echo "<BR>TargetHost:".$TargetHost;
		echo "<BR>LinkName:".$LinkName;
		echo "<BR>QueryString:".$QueryString;
		echo "<BR>Parent_doc:".$Parent_doc;
		echo "<BR>FormMethod:".$FormMethod;
	}


	//폼 데이터 만들기
	$data=GetQueryurlencode(MergeQueryNForm($QueryString,$FormAryInput),$ForceEncode=false);

	if($QueryString!="" || count($FormAryInput)>0){
		$GetLastPath=$LinkName."?".$data;
	}else{
		$GetLastPath=$LinkName;
	}
	if($Env["f_debug_yn"] ) echo "\n<BR>GetLastPath:".$GetLastPath;
$msg =
"GET ".$GetLastPath." HTTP/1.0
Accept: image/gif, image/jpeg, image/pjpeg, */*
Referer: http://".$TargetHost.$Parent_doc."/
Accept-Language: ko
Proxy-Connection: Keep-Alive
User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; .NET CLR 1.1.4322)
Host: ".$TargetHost."
Cookie: $f_auth_cookie

";
	return $msg;
}

//GET헤더 심플 버젼 만들기
function MakeGetHeaderSimple($target_path){
	global $Env;
	global $TargetHost;
	global $f_auth_cookie;
$msg =
"GET ".$target_path." HTTP/1.0
Accept: image/gif, image/jpeg, image/pjpeg, */*
Referer: http://$TargetHost/
Accept-Language: ko
Proxy-Connection: Keep-Alive
User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; .NET CLR 1.1.4322)
Host: $TargetHost
Cookie: $f_auth_cookie\r\n\r\n";

	return $msg;
}



?>