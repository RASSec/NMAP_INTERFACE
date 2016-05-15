<?
//인젝션 go
function GoCheck($EnvCtl,$dataLink,$dataForm){
	global $EnvAct;

	CheckLink($EnvCtl,$dataLink);
	CheckForm($EnvCtl,$dataForm);
}

//체크 Link
function CheckLink($EnvCtl,$dataLink){
	global $Env,$EnvAct;
	global $AryInjection;

	//배열이 아니면 종료
	if(!is_array($dataLink))return;

	//검사시 버퍼에 추가함
	$EnvCtl["isAddBuffer"]=true;

	//링크 루프 돌면서처리
	for($i=0;$i<count($dataLink);$i++){

		//이미 처리 했는지 검사(있으면true리턴, 없으면 저장후 false리턴)
		if(
			$EnvAct["EXCEPT_URL"][strtoupper($dataLink[$i]["link"])] == "Y" || 
			CheckOutExist($EnvCtl,$tLinkName=$dataLink[$i]["link"],$tMergeData=$dataLink[$i]["query"],$tCheckType="ALL")
			)continue;

		//검사항목 출력
		PrintCheckNum($dataLink[$i]["url"],$EnvCtl["Parent_doc"],$FormMethod="GET");

		//inject check
		GoInject($EnvCtl,$dataLink[$i],$dataForm=null);

		//xss check
		GoXss($EnvCtl,$dataLink[$i],$dataForm=null);

		//nextgo로 보내기
		GoNext($EnvCtl,$dataLink[$i],$dataForm=null);

	}
}


//체크 Form
function CheckForm($EnvCtl,$dataForm){
	global $Env,$EnvAct;
	global $outLink,$AryInjection;

	//배열이 아니면 종료
	if(!is_array($dataForm))return;

	//검사시 버퍼에 추가안함
	$EnvCtl["isAddBuffer"]=true;

	//폼갯수 만큼 루프
	for($i=0;$i<count($dataForm);$i++){		
		//echo "<BR>dataForm $i:".$dataForm[$i][0]["action"];

		//이미 처리 했는지 검사(있으면true리턴, 없으면 저장후 false리턴)
		if(	$EnvAct["EXCEPT_URL"][strtoupper($dataForm[$i][0]["actioncgi"])] == "Y" ||
			CheckOutExist(
				$EnvCtl
				,$tLinkName=$dataForm[$i][0]["actioncgi"]
				,$tMergeData=MergeQueryNForm($tQuery=$dataForm[$i][0]["actionquery"],$taryForm=$dataForm[$i])
				,$tCheckType="ALL"
				)
			)continue;

		//검사항목 출력
		PrintCheckNum(
			$tTargetUrl=$dataForm[$i][0]["action"]
			,$tParent_doc=$EnvCtl["Parent_doc"]
			,$tFormMethod=$dataForm[$i][0]["method"]
			);

		//inject check
		GoInject($EnvCtl,$tdataLink=null,$dataForm[$i]);

		//xss check
		GoXss($EnvCtl,$tdataLink=null,$dataForm[$i]);

		//nextgo로 보내기
		GoNext($EnvCtl,$tdataLink=null,$dataForm[$i]);
	}
}
?>