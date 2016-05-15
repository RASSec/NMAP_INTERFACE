<?
//검색한 링크 추가
function fnOutLink($EnvCtl,$tLink,$MergeData,$CheckType){
	global $Env;
	global $outLink;
	global $EnvAct;

	if(strlen($MergeData)>0){
		$ParamYN="Y";
	}else{
		$ParamYN="N";
	}

	//기존에 존재 하는지 검사
	for($j=0;$j<count($outLink);$j++){
		if(strtoupper($outLink[$j][0])==strtoupper($tLink)){
			if($ParamYN=="Y")$outLink[$j][1]="Y";
			//echo sprintf("<br>fnOutLink  %s  %d %d ■",$tLink,$outLink[$j][8][$CheckType],$EnvAct["LimitParamCnt"]);
			
			//아웃링크에 추가
			//echo " 추가";
			//$outLink[$j][1]="Y";
			$outLink[$j][2]++;
			$outLink[$j][3][count($outLink[$j][3])]=$MergeData;
			$outLink[$j][4]=$EnvCtl["Parent_doc"];
			$outLink[$j][5]=$EnvCtl["TargetHost"];
			$outLink[$j][6]=$EnvCtl["TargetPort"];
			$outLink[$j][7]=$EnvCtl["TargetType"];
			if(is_numeric($outLink[$j][8][$CheckType])){
				$outLink[$j][8][$CheckType]++;
			}else{
				$outLink[$j][8][$CheckType]=1;
			}
			//echo "<BR>fnOutLink 추가:".$CheckType." ".$tLink."?".$MergeData;

			return;
		}
	}
	//링크명,파라미터여부,파라미터체크수,쿼리스트링배열
	//echo "◆1";
	//echo "<BR>fnOutLink 신규:".$CheckType." ".$tLink."?".$MergeData;
	$outLink[count($outLink)]=array(strtoupper($tLink),$ParamYN,1,array($MergeData),$EnvCtl["Parent_doc"],$EnvCtl["TargetHost"],$EnvCtl["TargetPort"],$EnvCtl["TargetType"],array($CheckType => 1));
	//echo "\n아웃링크 사이즈 :".count($outLink);
}

//기존 리스트에 이미 존재하는지 검사
function CheckOutExist($EnvCtl,$LinkName,$MergeData,$CheckType){
	global $Env,$EnvAct;
	global $outLink;

	$LinkName=strtoupper($LinkName);
	if(strlen($MergeData)>0){
		$ParamYN="Y";
	}else{
		$ParamYN="N";
	}
	//echo "<BR>파라미터:".strlen($MergeData);
	//기존에 존재 하는지 검사
	for($j=0;$j<count($outLink);$j++){
		if($LinkName==$outLink[$j][0]){
			if($ParamYN=="Y")$outLink[$j][1]="Y";
			//echo sprintf("<br>CheckOutExist %s %s %d %d ■",$LinkName,$MergeData,$outLink[$j][8][$CheckType],$EnvAct["LimitParamCnt"]);
			//echo " <font color=blue>존재</font> ";
			if(
				$outLink[$j][1]=="Y" 
				&& is_numeric($outLink[$j][8][$CheckType]) 
				&& $outLink[$j][8][$CheckType]>=$EnvAct["LimitParamCnt"]){//파라미터 존재하고 파라미터체크제한수량도달
				//echo " <font color=gray>파람있고 제한초과</font> ";
				return true;
			}else if($outLink[$j][1]=="N" && is_numeric($outLink[$j][8][$CheckType]) && $outLink[$j][8][$CheckType]>0){
				//echo " <font color=gray>파람없고 이미처리</font> ";
				return true;				
			}else if($outLink[$j][1]=="Y"){
				//이미 등록된 쿼리스트링 리스트에 있는지 검사
				for($k=0;$k<count($outLink[$j][3]);$k++)if($outLink[$j][3][$k]==$MergeData){
					//echo " <font color=gray>존재하고 파람일치</font> ";
					return true;
				}
			}
		}
	}
	//echo " <font color=gray>존재 않음</font> ";

	//존재하지 않은 경우 추가하고 리턴
	if($EnvCtl["isAddBuffer"])fnOutLink($EnvCtl,$LinkName,$MergeData,$CheckType);
	return false;
}

?>