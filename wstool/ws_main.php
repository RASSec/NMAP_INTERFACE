<?
////////////////////////////////////////////////////////////////////////////////////////
//		라이브러리 불러오기
////////////////////////////////////////////////////////////////////////////////////////
include "ws_init.php";
include "ws_util.php";
include "ws_display.php";
include "ws_http.php";
include "ws_report.php";
include "ws_buffer.php";
include "ws_parseLink.php";
include "ws_parseForm.php";
include "ws_sock.php";
include "ws_errctl.php";
include "ws_inject.php";
include "ws_xss.php";
include "ws_gonext.php";
include "ws_admin.php";
include "ws_checkctl.php";


////////////////////////////////////////////////////////////////////
//		시작
////////////////////////////////////////////////////////////////////



//인트로 출력
if($f_host=="" || $f_port=="" || $f_method=="" || $f_html_doc==""){
	//Intro폼 출력
	IntroView();
	exit;
}



////////////////////////////////////////////////////////////////////////////////////////
//		분석시작
////////////////////////////////////////////////////////////////////////////////////////
$f_auth_cookie="";
if($f_auth_host!="" && $f_auth_url!=""){
	$f_auth_cookie=AuthCookie();
}
$f_auth_cookie.=$f_cookie_injection;


//echo "인증 쿠키 정보";
//echo "\n<BR>f_auth_cookie:".$f_auth_cookie;
//echo "\n<BR>f_cookie_injection:".$f_cookie_injection;
//exit;

StartView();//시작 메시지

echo "\n<BR><table width=100% border=0 cellpadding=5 cellspacing=0 bgcolor=darkblue><tr><td><font color=white><b>[Error Search]--------------------------------------------------------------------------------</td></tr></table>\n";
$time_start = getmicrotime();

list($LinkName,$QueryString)=split("\?",$TargetUrl,2);

$EnvCtl["TargetHost"]=$TargetHost;
$EnvCtl["TargetPort"]=$TargetPort;
$EnvCtl["TargetType"]="1";//1웹서버
$EnvCtl["Parent_doc"]="";
$EnvCtl["LinkDepth"]=$EnvAct["LinkDepth"];
$EnvCtl["isCheckPrint"]=true;
$EnvCtl["XssLinkDepth"]=null;

$get_rtn = Control(
	$EnvCtl,$LinkName,$tOriginQuery=$QueryString,$tInjectQuery=$QueryString,$tOriginForm=null,
	$tInjectForm=null,$tFormMethod=$TargetMethod,$tFormEnctype=""
	);
EndView();//종료후 메시지



//컨트롤러
function Control(
		$EnvCtl,$LinkName,$OriginQuery,$InjectQuery,$OriginForm,
		$InjectForm,$FormMethod,$FormEnctype
	){
	global $Env,$EnvAct;

	//[STEP 010] 값초기화
	//[STEP 020] SOCK을 통해 컨텐츠 가져오기
	//[STEP 030] 에러처리 (여기서부터 parent는 linkname이 됨)
	//[STEP 040] 4xx 또는 5xx에러시 컨텐츠분석 중지
	//[STEP 045] 3xx에러시 리다이렉트
	//[STEP 050] LinkDepth 처리
	//[STEP 060] 내용분석후 다음 링크를 위한 초기화
	//[STEP 070] 컨텐츠 파싱 (link) 링크 뽑아내기
	//[STEP 080] 컨텐츠 파싱 (form) 링크 뽑아내기
	//[STEP 090] 통합 체크





	//[STEP 010] 값초기화
	$dataC=null;
	$dataLink=null;
	$dataForm=null;
	$StatusCode="";

	//[STEP 020] SOCK을 통해 컨텐츠 가져오기
	$dataC=GetSock($EnvCtl,$LinkName,$InjectQuery,$InjectForm,$FormMethod,$FormEnctype);
	if($Env["f_debug_yn"]){
		echo "<hr><BR>헤더:".$dataC["HEADER"]["RAW"];
		echo "<BR>길이:".$dataC["HEADER"][strtoupper("Content-Length")];
		for($i=0;$i<count($dataC["HEADER"]["SET-COOKIE"]);$i++){
			echo "<BR>SET-COOKIE(".$i."):".$dataC["HEADER"]["SET-COOKIE"][$i];
		}
		if(eregi("[5][0-9]{2}",$dataC["HEADER"]["STATUSCODE"])){
			echo "<BR>내용:".strip_tags($dataC["BODY"]);
		}else{
			echo "<BR>내용:".htmlspecialchars($dataC["BODY"]);
		};

		//echo "<BR>내용:".$dataC["BODY"];
	}

	//[STEP 030] 에러처리 (여기서부터 parent는 linkname이 됨)
	ErrCtl(	$EnvCtl,$LinkName,$OriginQuery,$InjectQuery
			,$OriginForm,$InjectForm,$FormMethod,$dataC);

	//변수에 StatusCode받기
	if(isset($dataC["HEADER"]["STATUSCODE"]))$StatusCode=$dataC["HEADER"]["STATUSCODE"];


	//[STEP 040] 4xx 또는 5xx에러, 또는 statuscode가 없을때, 컨텐츠분석 중지
	if(eregi("[45][0-9]{2}",$StatusCode) || $StatusCode=="")return $StatusCode;

	//[STEP 045] 3xx에러시 리다이렉트
	if(eregi("[3][0-9]{2}",$StatusCode) && $dataC["HEADER"]["LOCATION"]!=""){
		$tmpLocation=GetUrlValid($EnvCtl,$tlink=$dataC["HEADER"]["LOCATION"],$tarentPath=GetFolderPath($LinkName),$tpathType="LOCATION");
		if(!is_bool($tmpLocation)){
			$dataLink=null;
			$EnvCtl["Parent_doc"]=$LinkName;
			$EvnCtl["Action"]=null;

			list($link,$query)=split("\?",$tmpLocation,2);			
			$dataLink[0]["url"]=$tmpLocation;
			$dataLink[0]["link"]=$link;
			$dataLink[0]["query"]=$query;

			GoCheck($EnvCtl,$tdataLink=$dataLink,$tdataForm=null);
		}
		return $StatusCode;
	}

	//[STEP 050] LinkDepth 처리
	if(!is_null($EnvCtl["LinkDepth"])){
		if($EnvCtl["LinkDepth"]<=0)return $StatusCode;
		$EnvCtl["LinkDepth"]--;
	}
	//[STEP 051] 링크 xsslinkdepth처리
	if(!is_null($EnvCtl["XssLinkDepth"])){
		if($EnvCtl["XssLinkDepth"]<=0)return $StatusCode;
		$EnvCtl["XssLinkDepth"]--;
	}
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	
	//	여기서부터 다음페이지, 바뀌는것들(Parent_doc=linkmame,LinkDepth--, xss초기화
	//	
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//[STEP 060] 내용분석후 다음 링크를 위한 초기화
	$EnvCtl["Parent_doc"]=$LinkName;
	$EvnCtl["Action"]=null;



	//[STEP 070] 컨텐츠 파싱 (link) 링크 뽑아내기
	list($dataLink,$EnvCtl["ActionAry"])=getLinkAry($EnvCtl,$tbody=$dataC["BODY"]);
	if($Env["f_debug_yn"]){
		for($i=0;$i<count($EnvCtl["ActionAry"]);$i++){
			echo "<BR>EnvCtl ActionAry(".$i."):".$EnvCtl["ActionAry"][$i];
		}

		for($i=0;$i<count($dataLink);$i++){
			echo "<BR>Link(".$i.")[url]:".$dataLink[$i]["url"];
			echo "<BR>Link(".$i.")[link]:".$dataLink[$i]["link"];
			echo "<BR>Link(".$i.")[query]:".$dataLink[$i]["query"];
			echo "<BR>Link(".$i.")[type]:".$dataLink[$i]["type"];
		}
	}

	//[STEP 080] 컨텐츠 파싱 (form) 링크 뽑아내기
	$dataForm=getFormAry($EnvCtl,$tbody=$dataC["BODY"]);
	if($Env["f_debug_yn"]){
		echo "<BR><BR><hr noshade> main form";
		for($i=0;$i<count($dataForm);$i++){
				echo "<BR>method=".$dataForm[$i][0]["method"];
				echo "<BR>enctype=".$dataForm[$i][0]["enctype"];
				echo "<BR>action=".$dataForm[$i][0]["action"];
				echo "<BR>actioncgi=".$dataForm[$i][0]["actioncgi"];
				echo "<BR>actionquery=".$dataForm[$i][0]["actionquery"];
				for($t=1;$t<count($dataForm[$i]);$t++){
				   echo "<BR>".$dataForm[$i][$t]["name"].":".$dataForm[$i][$t]["value"];
				}
				echo "<hr>";
		}
	}

	//[STEP 090] 통합 체크
	GoCheck($EnvCtl,$dataLink,$dataForm);

/*
$EnvCtl["TargetHost"]
$EnvCtl["TargetPort"]
$EnvCtl["TargetType"]
$EnvCtl["Parent_doc"]
$EnvCtl["LinkDepth"]
$EnvCtl["isCheckPrint"]
$EnvCtl["XssLinkDepth"]
*/
	return $StatusCode;
}
?>