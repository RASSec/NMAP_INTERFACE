<?
//go Next
function GoNext($EnvCtl,$dataLink,$dataForm){
	GoNextLink($EnvCtl,$dataLink);
	GoNextForm($EnvCtl,$dataForm);
}

//go Link
function GoNextLink($EnvCtl,$dataLink){
	global $Env,$EnvAct;


	//링크가 없으면 리턴
	if($dataLink["link"]=="")return;

	//검사시 버퍼에 추가함
	//$EnvCtl["isAddBuffer"]=true;

	//이미 처리 했는지 검사(있으면true리턴, 없으면 저장후 false리턴)
	//if(CheckOutExist($EnvCtl,$LinkName=$dataLink["link"],$MergeData=$dataLink["query"],$CheckType="ALL"))return;

	//[정상 호출] sock호출
	//echo "<BR>sock호출:".$dataLink["url"];
	$EnvCtl["isCheckPrint"]=false;
	$EnvCtl["XssLinkDepth"]=null;
	Control(
		$EnvCtl,$LinkName=$dataLink["link"],$OriginQuery=$dataLink["query"],$InjectQuery=$dataLink["query"],$OriginForm=null
		,$InjectForm=null,$FormMethod="GET",$FormEnctype=null
	);

}

//go Form
function GoNextForm($EnvCtl,$dataForm){
	global $Env,$EnvAct;

	//배열이 아니면 종료
	if(!is_array($dataForm))return;

	//검사시 버퍼에 추가함
	/*
	$EnvCtl["isAddBuffer"]=true;

	
	//이미 처리 했는지 검사(있으면true리턴, 없으면 저장후 false리턴)
	if(	CheckOutExist(
			$EnvCtl,$LinkName=$dataForm[0]["actioncgi"],$MergeData=MergeQueryNForm($Query=$dataForm[0]["actionquery"],$aryForm=$dataForm)
			,$CheckType="ALL"
			)
		)return;
	*/	

	//sock호출
	//echo "<BR>sock호출:".$dataForm[0]["action"];
	$EnvCtl["isCheckPrint"]=false;
	$EnvCtl["XssLinkDepth"]=null;
	Control(
		$EnvCtl,$LinkName=$dataForm[0]["actioncgi"],$OriginQuery=$dataForm[0]["actionquery"],$InjectQuery=$dataForm[0]["actionquery"],$OriginForm=$dataForm
		,$InjectForm=$dataForm,$FormMethod=$dataForm[0]["method"],$FormEnctype=$dataForm[0]["enctype"]
		);

}
?>