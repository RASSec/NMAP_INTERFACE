<?
//fsockopen커넥션 하기
function GetSock($EnvCtl,$LinkName,$QueryString,$FormAryInput,$FormMethod,$FormEnctype){
	global $Env,$EnvAct;
	global $whitespace;
	
	$PutData="";
	//리턴 배열
	$tAry=null; //HEADER,BODY

	//링크가 없으면 실행하지 않기
	if($LinkName=="")return;

	if($Env["f_debug_yn"]){
		echo "<BR><BR>GetSock";
		echo "<BR>TargetHost:".$EnvCtl["TargetHost"];
		echo "<BR>TargetPort:".$EnvCtl["TargetPort"];
		echo "<BR>TargetType:".$EnvCtl["TargetType"];
		echo "<BR>LinkName:".$LinkName;	
		echo "<BR>MergeData:".MergeQueryNForm($QueryString,$FormAryInput);	
	}

	//임시서버로 요청전해 보기
	if($Env["f_debug_yn"] && 0){
		$fp2=fsockopen("localhost",80, $errno, $errstr, 3);
		if($fp2){
			echo "<font color=blue>".MakeContent("localhost","/t.php",$QueryString,$EnvCtl["Parent_doc"],$FormMethod,$FormEnctype,$FormAryInput)."</font>";
			fputs ($fp2,MakeContent("localhost","/t.php",$QueryString,$EnvCtl["Parent_doc"],$FormMethod,$FormEnctype,$FormAryInput));
			while (!feof($fp2)) {
				echo fgets($fp2,128);
			}
		}else{
			echo "\n<BR>파일열기실패";
		}
		fclose($fp2);
	}

	if($EnvCtl["TargetPort"]==443){
		$fp = @fsockopen("ssl://".$EnvCtl["TargetHost"], $EnvCtl["TargetPort"], $errno, $errstr, 3);
	}else{
		$fp = @fsockopen($EnvCtl["TargetHost"], $EnvCtl["TargetPort"], $errno, $errstr, 3);
	}
	if (!$fp) {
		return "$errstr ($errno) ".$EnvCtl["TargetHost"].":".$EnvCtl["TargetPort"];
	} else {
		//웹
		if($EnvCtl["TargetType"]=="1"){
			$PutData=MakeContent($EnvCtl["TargetHost"],$LinkName,$QueryString,$EnvCtl["Parent_doc"],$FormMethod,$FormEnctype,$FormAryInput);
			if($Env["f_debug_yn"]){
				echo "<pre><font color=green style='font-size:8pt;'>".$PutData."</font></pre>";flush();
			}		
			fputs ($fp,$PutData);
			$Line =0;
			$step=0;
			while (!feof($fp)) {
				$Line++;
				$tmps =fgets($fp,1024); //fgets($fp,4096)
				if($step<1){
					//헤더추가
					$header.=$tmps;
					if($Line==1 && eregi("([".$whitespace."]+)([0-9]{3})([".$whitespace."]+)",$tmps,$matched)){
						$tAry["HEADER"]["STATUSCODE"]=trim($matched[2]);
					}else if($Line>1 && eregi("^Set\-Cookie\:(.*)",$tmps,$matched)  ){
						list($NameValue,$Ext)=split(";",$matched[1],2);
						$tAry["HEADER"]["SET-COOKIE"][count($tAry["HEADER"]["SET-COOKIE"])]=trim($NameValue);
					}else if($Line>1 && eregi("([^:]+):(.+)",$tmps,$matched)  ){
						$tAry["HEADER"][trim(strtoupper($matched[1]))]=trim($matched[2]);
					}
				}else{
					//바디추가
					$body.=$tmps;
				}
				if(ereg("^.[\r\n]$",$tmps)) $step++;
			}
		}
	}

	//배열세팅
	$tAry["HEADER"]["RAW"]=$header;
	$tAry["BODY"]=$body;

	fclose($fp);
	sleep($Env["DelayParamTime"]);

	return $tAry;
}
?>