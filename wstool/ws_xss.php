<?
//go Xss
function GoXss($EnvCtl,$dataLink,$dataForm){
	global $EnvAct;
	if($EnvAct["ERR_200XSS_YN"]<>"Y")return;

	inXssLink($EnvCtl,$dataLink);
	inXssForm($EnvCtl,$dataForm);
}

//Xss Link
function inXssLink($EnvCtl,$dataLink){
	global $Env,$EnvAct;
	global $AryXss;


	//파라미터 갯수 구하기
	$tary=split("&",$dataLink["query"]);

	$ControlReturnValue=true;

	//루프 돌면서 AryXss모든 검사
	foreach ($AryXss as $Str => $Reg){
		$tXss=$Str;
		$ControlReturnValue=true;
		for($k=0;$k<count($tary) && is_array($tary);$k++){
			//쿼리스트링 만들기
			list($tname,$tvalue)=split("=",$tary[$k],2);
			if($tname=="")continue;
			$InjectQuery="";
			for($m=0;$m<count($tary);$m++){
				list($tname,$tvalue)=split("=",$tary[$m],2);
				if($tname=="")continue;

				if(strlen($InjectQuery)>0)$InjectQuery.="&";
				//쿼리스트링: 인젝션할때는 완전새것, 인젝션없는것을 그값 그대로
				if($k==$m){
					$InjectQuery.=$tname."=".$tXss;
				}else{
					$InjectQuery.=$tname."=".$tvalue;
				}
			}
			//sock inject호출 (urlencode은 http에서 담당)
			//echo "<BR>sock inject호출:".$dataLink["link"]."?".$InjectQuery;
			$EnvCtl["isCheckPrint"]=false;
			$EnvCtl["XssLinkDepth"]=3;
			if($EnvAct["ProcessDotPrint"]=="Y")echo ".";flush();
			$ControlReturnValue=Control(
				$EnvCtl,$tLinkName=$dataLink["link"],$tOriginQuery=$dataLink["query"],$InjectQuery,$tOriginForm=null
				,$tInjectForm=null,$tFormMethod="GET",$tFormEnctype=null
				);
			if($EnvAct["force_404_yn"]<>"Y" && $ControlReturnValue=="404")break; //404에러 이면 1회요청하고 끝내기
		}
		if($EnvAct["force_404_yn"]<>"Y" && $ControlReturnValue=="404")break; //404에러 이면 1회요청하고 끝내기
	}


}


//Xss Form
function inXssForm($EnvCtl,$dataForm){
	global $Env,$EnvAct;
	global $outLink,$AryXss;
	$ParamYN="N";

	//배열이 아니면 종료
	if(!is_array($dataForm))return;

	$ControlReturnValue=true;

	//루프 돌면서 AryXss모든 검사
	foreach ($AryXss as $Str => $Reg){
		$tXss=$Str;
		//[xss 1] input스캔
		$ControlReturnValue=true;
		for($k=1;$k<count($dataForm) && is_array($dataForm);$k++){

			//폼Input 새로 만들기
			if($dataForm[$k]["name"]=="" || $dataForm[$k]["type"]=="FILE")continue;

			$InjectForm=$dataForm;
			//echo "<BR>InjectForm:".count($InjectForm);
			//echo "<BR>Xss:".$tXss;
			for($m=1;$m<count($InjectForm);$m++){
				
				if($InjectForm[$m]["name"]=="")continue;
				$tmpValue=$InjectForm[$m]["value"];
				if($k==$m){
					$InjectForm[$m]["value"]=$tXss;
				}else{
					if($InjectForm[$m]["type"]=="FILE"){
						$tmpValue=$Env["AttachFile"];
					}else if($tmpValue==""){
						$tmpValue=$Env["FormInputDeafultValue"];
					}
					$InjectForm[$m]["value"]=$tmpValue;
				}
			}

			//sock호출
			//echo "<BR>sock inject호출:".$dataForm[0]["actioncgi"]."?".htmlspecialchars(MergeQueryNForm($dataForm[0]["actionquery"],$InjectForm));
			$EnvCtl["isCheckPrint"]=false;
			$EnvCtl["XssLinkDepth"]=3;
			if($EnvAct["ProcessDotPrint"]=="Y")echo ".";flush();
			$ControlReturnValue=Control(
				$EnvCtl,$LinkName=$dataForm[0]["actioncgi"],$OriginQuery=$dataForm[0]["actionquery"],$InjectQuery=$dataForm[0]["actionquery"],$OriginForm=$dataForm,
				$InjectForm,$FormMethod=$dataForm[0]["method"],$FormEnctype=$dataForm[0]["enctype"]
				);
			if($EnvAct["force_404_yn"]<>"Y" && $ControlReturnValue=="404")break; //404에러 이면 1회요청하고 끝내기
		}
		if($EnvAct["force_404_yn"]<>"Y" && $ControlReturnValue=="404")break; //404에러 이면 1회요청하고 끝내기

		//[xss 2] action에 있는 쿼리 Injection
		$tary=split("&",$dataForm[0]["actionquery"]);
		for($k=0;$k<count($tary) && is_array($tary);$k++){
			//쿼리스트링 만들기
			list($tname,$tvalue)=split("=",$tary[$k],2);
			if($tname=="")continue;
			$InjectQuery="";
			for($m=0;$m<count($tary);$m++){
				list($tname,$tvalue)=split("=",$tary[$m],2);
				if($tname=="")continue;

				if(strlen($InjectQuery)>0)$InjectQuery.="&";
				//쿼리스트링: 인젝션할때는 완전새것, 인젝션없는것을 그값 그대로
				if($k==$m){
					$InjectQuery.=$tname."=".$tXss;
				}else{
					$InjectQuery.=$tname."=".$tvalue;
				}
			}

			//sock호출
			//echo "<BR>sock inject호출:".$dataForm[0]["actioncgi"]."?".htmlspecialchars(MergeQueryNForm($InjectQuery,$dataForm));
			$EnvCtl["isCheckPrint"]=false;
			$EnvCtl["XssLinkDepth"]=3;
			if($EnvAct["ProcessDotPrint"]=="Y")echo ".";flush();
			$ControlReturnValue=Control(
				$EnvCtl,$tLinkName=$dataForm[0]["actioncgi"],$tOriginQuery=$dataForm[0]["actionquery"],$tInjectQuery=$InjectQuery,$tOriginForm=$dataForm
				,$dataForm,$tFormMethod=$dataForm[0]["method"],$tFormEnctype=$dataForm[0]["enctype"]
				);
			if($EnvAct["force_404_yn"]<>"Y" && $ControlReturnValue=="404")break; //404에러 이면 1회요청하고 끝내기
		}
		if($EnvAct["force_404_yn"]<>"Y" && $ControlReturnValue=="404")break; //404에러 이면 1회요청하고 끝내기
	}


}
?>