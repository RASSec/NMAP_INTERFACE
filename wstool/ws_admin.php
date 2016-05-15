<?

//검색한 링크 추가
function check_adminfolder(){
	global $Env,$EnvAct,$AdminCheckCnt;
	global $outLink,$AryAdminFolder;
	global $TargetHost,$TargetPort;
	//기존에 존재 하는지 검사
	echo "\n<BR><table width=100% border=0 cellpadding=5 cellspacing=0 bgcolor=darkblue><tr><td><font color=white><b>[Admin Search](Check count:".count($AryAdminFolder).")--------------------------------------------------------------------------------</td></tr></table>\n";

	$EnvAct["ERR_2xx_YN"]="Y";
	$Env["CheckNum"]=0;
	$EnvAct["CheckNumLimit"]=count($AryAdminFolder);
	$AdminCheckCnt=0;

	$tfolder="/";
	for($t=0;$t<count($AryAdminFolder);$t++){
		//스캔
		//$outLink[$j][5]=$TargetHost;
		//$outLink[$j][6]=$TargetPort;
		//$outLink[$j][7]=$TargetType;
		/*
		echo "<BR>TargetHost:".$outLink[$j][5];
		echo "<BR>TargetPort:".$outLink[$j][6];
		echo "<BR>TargetType:".$outLink[$j][7];
		echo "<BR>Link:".$tfolder.$AryAdminFolder[$t]."/";
		flush();
		*/

		//echo ($AdminCheckCnt++)." ";
		$tLink=null;
		
		$EnvCtl["TargetHost"]=$TargetHost;
		$EnvCtl["TargetPort"]=$TargetPort;
		$EnvCtl["TargetType"]="1";//1 웹서버
		$EnvCtl["Parent_doc"]="";
		$EnvCtl["LinkDepth"]=0;
		$EnvCtl["XssLinkDepth"]=null;

		$tLink[0]["url"]=$tfolder.$AryAdminFolder[$t]."/";
		$tLink[0]["link"]=$tfolder.$AryAdminFolder[$t]."/";
		$tLink[0]["query"]="";
		$tLink[0]["type"]="HREF";

		GoCheck($EnvCtl,$tdataLink=$tLink,$tdataForm=null);
	}
}
?>