<?
//링크에서 CGI경로만 추출하기
function GetLinkName($tmpUrl){
	list($LinkName,$QueryData)=split("\?",$tmpUrl,2);
	return $LinkName;
}

//링크에서 Query데이터만 추출하기
function GetQueryData($tmpUrl){
	list($LinkName,$QueryData)=split("\?",$tmpUrl,2);
	return $QueryData;
}

//링크에서 패스경로 추출하기
function GetFolderPath($tmp){
	//$RootUrl에서 현재 경로 추출하기
	list($h1,$h2)=split("\?",$tmp,2);
	$ParentPath=substr( $h1, 0, strrpos( $h1, '/') )."/";
	return $ParentPath;
}

//쿼리스트링+폼ary를 data로 합치기
function MergeQueryNForm($Query,$aryForm){
	$data=$Query;
	for($t=1;$t<count($aryForm);$t++){
		if($data!="")$data.="&";
		$data.=$aryForm[$t]["name"]."=".$aryForm[$t]["value"];
	}
	return $data;
}

//쿼리스트링+폼ary를 data로 합치기
function MergeQueryNFormNType($Query,$aryForm){
	$data=$Query;
	for($t=1;$t<count($aryForm);$t++){
		if($data!="")$data.="&";
		$data.=$aryForm[$t]["name"]."=".$aryForm[$t]["value"]."ⓣ".$aryForm[$t]["type"];
	}
	return $data;
}

//실행 시간 구하기
function getmicrotime(){ 
   list($usec, $sec) = explode(" ", microtime()); 
   return ((float)$usec + (float)$sec); 
} 

//좌우 ',",>, 공백제거
function StripQuota($tmp){
	$tmp=trim($tmp);
	if($tmp==""){
		return $tmp;
	}else if(substr($tmp,0,1)=="\"" || substr($tmp,0,1)=="'"){
		$tmp=substr($tmp,1,strlen($tmp)-2);
	}else if(substr($tmp,strlen($tmp)-1)==">"){
		$tmp=substr($tmp,0,strlen($tmp)-1);
	}else{
		$tmp=$tmp;
	}

	return $tmp;
}

//파라이터스트링을 URL을 urlencode형식으로변환하여 리턴
function GetQueryUrlEncode($tmp,$ForceEncode){
	$ReturnValue="";
	//echo "<BR>\nGetQueryUrlEncode 입력:".htmlspecialchars($tmp);

	//이미 인코딩 되었는지 검사
	//if(!$ForceEncode && eregi("(\%5F|\%2E|\%3F|\%09|\%2F|\%26|\%0D|\+|\%0A|\%3D)",$tmp))return $tmp;
	if(!$ForceEncode && eregi("\%[0-9a-z]{2}",$tmp))return $tmp;

	//파라미터 갯수 구하기
	$tary=split("&",$tmp);
	
	//루프 돌면서 AryInjection모든 검사
	for($k=0;$k<count($tary);$k++){
		//쿼리스트링 만들기
		list($tname,$tvalue)=split("=",$tary[$k],2);
		if($tname=="")continue;
		if($ReturnValue!="")$ReturnValue.="&";
		//숫자나영문나_이외의문자가 있으면 urlencode
		if( (eregi("[^a-z0-9\_]",$tvalue) && !eregi("\%[0-9a-z]{2}",$tvalue)) || $ForceEncode){
			$ReturnValue.=$tname."=".urlencode($tvalue);
		}else{
			$ReturnValue.=$tname."=".$tvalue;
		}
	}
	//echo "<BR><BR>\nGetQueryUrlEncode 출력:".htmlspecialchars($ReturnValue);
	return $ReturnValue;
}


//검사할 패스가 올바른 패스 인지 검사(리턴이 문자나/공백문자""면 성공,false 실패(타사이트로링크,제외링크)
function GetUrlValid($EnvCtl,$path,$ParentPath,$pathType){
	global $Env;
	global $elist,$slist,$EnvAct,$root_folder;
	global $TargetHost;
	
	if($Env["f_debug_yn"])echo "<BR>path A:".$path;

	//링크에서 cgi명과 쿼리스트링 분리
	$path=trim($path);

	//링크명이 전혀 없으면 리턴
	if($path=="")return "";
	
	list($LinkName,$QueryString)=split("\?",$path,2);
	//echo "<BR>LinkName:".$LinkName;
	//echo "<BR>QueryString:".$QueryString;

	//제외할 리스트에 있으면 false
	if( eregi("\.(".$elist.")$",$LinkName,$tb) )return false;

	//src가 스크립트 아니면 다음루프로
	if( eregi("src",$pathType,$ta) && !eregi("\.(".$slist.")$",$LinkName,$tb))return false;
	//echo sprintf("\n %3d src후: [%s]",$i,$path);

	//내호스트명이 앞에 있을경우 제거
	if( eregi("(http|https)(\:\/\/".$TargetHost.")(.*)",$LinkName,$myreg) ){
		$LinkName=$myreg[3];
	}

	//링크앞에 ./있으면 ./제거
	if( eregi("^(\.\/)(.*)",$LinkName,$myreg) ){
		$LinkName=$myreg[2];
	}

	//외부 링크,이메일,자바스크립트 일때
	if(	!eregi("^(http\:\/\/|https\:\/\/|mailto\:|javascript\:)",$LinkName)){
		//echo sprintf("\n %3d 매칭후: [%s]",$i,$path);
	
		//../../../를 상위폴더로 이동
		$tLink=$LinkName;
		$tmpParent=$ParentPath;
		/*
		echo "<hr><BR>pathAll[".$pathAll."]";
		echo "<hr><BR>ParentPath[".$ParentPath."]";
		echo "<BR>tmpPath[".$tLink."]";
		echo "<BR>tmpParent[".$tmpParent."]";
		echo "<BR>tmpPath[".substr($tLink,0,3)."]";
		echo "<BR>tmpParent[".strlen($tmpParent)."]";
		*/
		while(substr($tLink,0,3)=="../" && strlen($tmpParent)>1){
			//상위폴더로 이동
			$tLink=substr($tLink,3);
			$tmpParent=substr( substr($tmpParent,0,strlen($tmpParent)-1), 0, strrpos( substr($tmpParent,0,strlen($tmpParent)-1), '/', -2 ) )."/";
		}

		if(substr($tLink,0,1)!="/" && !eregi("http:\/\/",$tLink) )$tLink=$tmpParent.$tLink;			
		if($EnvAct["folder_yn"]=="Y" && !eregi("^".$root_folder,$tLink))return false;//입력폴더이하만 검색
		//echo sprintf("\n %3d 변환: ",$i).$tLink;
		
		if($Env["f_debug_yn"])echo "<BR>path B:".$tLink;
		
		//원본에 쿼리스트링이 있었으면 붙여서 리턴
		if($QueryString)$tLink.="?".$QueryString;
		return $tLink;
	}
	return false;
}
?>
