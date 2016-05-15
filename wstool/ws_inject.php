<?
//인젝션 go
function GoInject($EnvCtl,$dataLink,$dataForm){
	global $EnvAct;
	if($EnvAct["ERR_500SQL_YN"]<>"Y")return;

	injectLink($EnvCtl,$dataLink);
	injectForm($EnvCtl,$dataForm);
}

//인젝션 Link
function injectLink($EnvCtl,$dataLink){
	global $Env,$EnvAct;
	global $AryInjection;

	//파라미터 갯수 구하기
	$tary=split("&",$dataLink["query"]);

	$ControlReturnValue=true;

	//[inject 호출] sock inject호출 (urlencode은 http에서 담당)
	for($j=0;$j<count($AryInjection["MSSQL"]) && strlen($dataLink["query"])>0;$j++){
		$tInject=$AryInjection["MSSQL"][$j];
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
					if($tvalue=="")$tvalue=$Env["FormInputDeafultValue"];
					$InjectQuery.=$tname."=".str_replace("※",$tvalue,$tInject);
				}else{
					$InjectQuery.=$tname."=".$tvalue;
				}
			}
			//sock inject호출 (urlencode은 http에서 담당)
			//echo "<BR>sock inject호출:".$dataLink["link"]."?".$InjectQuery;
			$EnvCtl["isCheckPrint"]=false;
			$EnvCtl["XssLinkDepth"]=null;
			$EnvCtl["LinkDepth"]=$EnvAct["InjectLinkDepth"];
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


//인젝션 Form
function injectForm($EnvCtl,$dataForm){
	global $Env,$EnvAct;
	global $outLink,$AryInjection;

	//배열이 아니면 종료
	if(!is_array($dataForm))return;

	$ControlReturnValue=true;

	//루프 돌면서 AryInjection모든 검사
	for($j=0;$j<count($AryInjection["MSSQL"]) && count($dataForm)>1;$j++){
		$tInject=$AryInjection["MSSQL"][$j];
		//[인젝션 1] 폼 객체 만큼 루프
		for($k=1;$k<count($dataForm) && is_array($dataForm);$k++){

			//폼Input 새로 만들기
			if($dataForm[$k]["name"]=="" || $dataForm[$k]["type"]=="FILE")continue; //파일은 인젝션 없음

			$InjectForm=$dataForm;
			for($m=1;$m<count($InjectForm);$m++){

				if($InjectForm[$m]["name"]=="")continue;

				$tmpValue=$InjectForm[$m]["value"];
				if($k==$m){
					if($InjectForm[$m]["value"]=="")$InjectForm[$m]["value"]=$Env["FormInputDeafultValue"];
					$InjectForm[$m]["value"]=str_replace("※",$InjectForm[$m]["value"],$tInject);
				}else{
					if($dataForm[$m]["type"]=="FILE"){
						$tmpValue=$Env["AttachFile"];
					}else if($tmpValue=="")$tmpValue=$Env["FormInputDeafultValue"];
					$InjectForm[$m]["value"]=$tmpValue;
				}
			}

			//sock호출
			//echo "<BR>sock inject호출:".$dataForm[0]["action"]."?".MergeQueryNForm("",$dataForm);
			$EnvCtl["isCheckPrint"]=false;
			$EnvCtl["XssLinkDepth"]=null;
			$EnvCtl["LinkDepth"]=$EnvAct["InjectLinkDepth"];

			if($EnvAct["ProcessDotPrint"]=="Y")echo ".";flush();
			$ControlReturnValue=Control(
				$EnvCtl,$tLinkName=$dataForm[0]["actioncgi"],$tOriginQuery=$dataForm[0]["actionquery"],$tInjectQuery=$dataForm[0]["actionquery"],$tOriginForm=$dataForm
				,$InjectForm,$tFormMethod=$dataForm[0]["method"],$tFormEnctype=$dataForm[0]["enctype"]
				);
			if($EnvAct["force_404_yn"]<>"Y" && $ControlReturnValue=="404")break; //404에러 이면 1회요청하고 끝내기
		}
		if($EnvAct["force_404_yn"]<>"Y" && $ControlReturnValue=="404")break; //404에러 이면 1회요청하고 끝내기

		//[인젝션 2] action에 있는 쿼리 Injection
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
					$InjectQuery.=$tname."=".$tInject;
				}else{
					$InjectQuery.=$tname."=".$tvalue;
				}
			}

			//sock호출
			//echo "<BR>sock inject호출:".$dataForm[0]["action"]."?".MergeQueryNForm("",$dataForm);
			$EnvCtl["isCheckPrint"]=false;
			$EnvCtl["XssLinkDepth"]=null;
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