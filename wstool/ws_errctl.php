<?
function ErrCtl($EnvCtl,$LinkName,$OriginQuery,$InjectQuery,$OriginForm,$InjectForm,$FormMethod,$tAry){
	global $Env,$EnvAct;
	global $whitespace;
	global $AryXss;
	global $p_type,$reg_str_form,$reg_str_input,$p_typefile;//Á¤±Ô½Ä°ü·Ã

	//¹è¿­ÀÌ ¾Æ´Ï¸é ÇÔ¼ö Á¾·á
	if(!is_array($tAry)) return;

	$HeaderStatusCode=$tAry["HEADER"]["STATUSCODE"];

	$OriginMergeData=MergeQueryNForm($OriginQuery,$OriginForm);
	$InjectMergeData=MergeQueryNForm($InjectQuery,$InjectForm);

	//¿¡·¯¸é ¿¡·¯ ¸µÅ©¿¡ ÀúÀå 4xx,5xx
	if($EnvAct["ERR_2xx_YN"]=="Y" && eregi("[2][0-9]{2}",$HeaderStatusCode) &&$tAry["HEADER"][strtoupper("Content-Length")]!=0){
		ErrMsg($HeaderStatusCode,$FontColor="black");
		errLink($EnvCtl,$LinkName,"Y",$OriginMergeData,$InjectMergeData,$HeaderStatusCode,$FormMethod,$InjectQuery,$InjectForm);
	//³»¿ë±æÀÌ ºÐ¼®ÇØ¼­ ³»¿ë±æÀÌ ¾øÀ¸¸é 404¿Í µ¿ÀÏ Ãë±Þ
	}else if($EnvAct["ERR_4xx_YN"]=="Y" && $tAry["HEADER"][strtoupper("Content-Length")]==0){
		$HeaderStatusCode="404";
		ErrMsg($HeaderStatusCode,$FontColor="black");
		errLink($EnvCtl,$LinkName,"Y",$OriginMergeData,$InjectMergeData,$HeaderStatusCode,$FormMethod,$InjectQuery,$InjectForm);
	}else if($EnvAct["ERR_4xx_YN"]=="Y" && eregi("[4][0-9]{2}",$HeaderStatusCode)){
		ErrMsg($HeaderStatusCode,$FontColor="black");
		errLink($EnvCtl,$LinkName,"Y",$OriginMergeData,$InjectMergeData,$HeaderStatusCode,$FormMethod,$InjectQuery,$InjectForm);
	}else if($EnvAct["ERR_5xx_YN"]=="Y" && eregi("[5][0-9]{2}",$HeaderStatusCode)){
		ErrMsg($HeaderStatusCode,$FontColor="black");
		errLink($EnvCtl,$LinkName,"Y",$OriginMergeData,$InjectMergeData,$HeaderStatusCode,$FormMethod,$InjectQuery,$InjectForm);
	}
	
	//ÆÄÀÏ ¾÷·Îµå °Ë»öÀÏ¶§
	if($EnvAct["ERR_FILEFORM_YN"]=="Y" 
		&& eregi($reg_str_form,$tAry["BODY"]) 
		&& eregi($p_typefile,$tAry["BODY"])
		){
		$HeaderStatusCode="200FILE";
		ErrMsg($HeaderStatusCode,$FontColor="vilot");
		errLink($EnvCtl,$LinkName,"Y",$OriginMergeData,$InjectMergeData,$HeaderStatusCode,$FormMethod,$InjectQuery,$InjectForm);		
	}

	//if($EnvAct["ERR_500SQL_YN"]=="Y" && eregi("[5][0-9]{2}",$HeaderStatusCode)){
	if($EnvAct["ERR_500SQL_YN"]=="Y"){
		//sql server¿¡·¯
		if(eregi("varchar",$tAry["BODY"]) && eregi("",$tAry["BODY"]) ){
			$HeaderStatusCode="500SQLP";
			ErrMsg($HeaderStatusCode,$FontColor="red");
			errLink($EnvCtl,$LinkName,"Y",$OriginMergeData,$InjectMergeData,$HeaderStatusCode,$FormMethod,$InjectQuery,$InjectForm);
		}else //sql server¿¡·¯
		if(eregi("80040e14",$tAry["BODY"]) || eregi("SQL Server error",$tAry["BODY"]) ){
			$HeaderStatusCode="500SQL";
			ErrMsg($HeaderStatusCode,$FontColor="red");
			errLink($EnvCtl,$LinkName,"Y",$OriginMergeData,$InjectMergeData,$HeaderStatusCode,$FormMethod,$InjectQuery,$InjectForm);
		}
		//Access Driver ¿¡·¯(Ãë¾àÁ¡¾øÀ½)
		if(eregi("Access Driver",$tAry["BODY"]) ){
			$HeaderStatusCode="500Access";
			ErrMsg($HeaderStatusCode,$FontColor="black");
		}
		//ADODB 800a0d5d ¿¡·¯(Ãë¾àÁ¡¾øÀ½)
		if(eregi("800a0d5d",$tAry["BODY"]) || eregi("ADODB",$tAry["BODY"]) ){
			$HeaderStatusCode="500ADO";
			ErrMsg($HeaderStatusCode,$FontColor="black");
		}
		//ADODB 800a0d5d ¿¡·¯(Ãë¾àÁ¡¾øÀ½)
		if(eregi("JET Database",$tAry["BODY"]) ){
			$HeaderStatusCode="500JET";
			ErrMsg($HeaderStatusCode,$FontColor="black");
		}
	}

	//xss¿¡·¯ °Ë»ö
	if(!is_null($EnvCtl["XssLinkDepth"]) && $EnvCtl["XssLinkDepth"]>0 && $EnvAct["ERR_200XSS_YN"]){
		//echo "<BR>Xss°Ë»ç½ÃÀÛ";
		foreach ($AryXss as $Str => $Reg){
			if(eregi($Reg,$tAry["BODY"])){
				ErrMsg($HeaderStatusCode="200XSS",$FontColor="black");
				errLink($EnvCtl,$LinkName,"Y",$OriginMergeData,$InjectMergeData,"200XSS",$FormMethod,$InjectQuery,$InjectForm);
			}
		}
	}
}



//500¿¡·¯ ¸µÅ© ¸ðÀ½
function errLink($EnvCtl,$tLink,$ParamYN,$OriginMergeData,$InjectMergeData,$ErrCode,$FormMethod,$InjectQuery,$InjectForm){
	global $Env;
	global $errLink;

	if($Env["f_debug_yn"]){
		echo "<BR><BR>errLink";
		echo "<BR>tLink:".$tLink;
		echo "<BR>$ParamYN:".$ParamYN;
		echo "<BR>OriginMergeData:".$OriginMergeData;
		echo "<BR>ErrQueryString:".$ErrQueryString;
		echo "<BR>Parent_doc:".$EnvCtl["Parent_doc"];
	}

	//±âÁ¸¿¡ Á¸Àç ÇÏ´ÂÁö °Ë»ç
	for($j=0;$j<count($errLink);$j++){
		if(strtoupper($errLink[$j][0])==strtoupper($tLink)){
			//ÆÄ¶ó¹ÌÅÍ°¡ Á¸ÀçÇÏ¸é ³Ö±â
			if( ($ParamYN=="Y"||$errLink[$j][1]=="Y")){
				//ÀÌ¹Ì ±âÁ¸ÀÇ ¿¡·¯ÄÚµå¿Í ÆÄ¶ó¹ÌÅÍ°¡ µ¿ÀÏÇÑ ¸µÅ©°¡ ÀÖÀ¸¸é Åë°ú
				for($t=0;$t<count($errLink[$j][3]);$t++)
					if($ErrCode==$errLink[$j][3][$t][1] && $InjectMergeData==$errLink[$j][3][$t][0])return;
				//echo " ¿¡·¯¸µÅ©(ÆÄ¶÷)Á¸Àç ";

				$errLink[$j][1]="Y";
			}else if($ParamYN=="N" && $errLink[$j][1]=="N"){
				//ÆÄ¶ó¹ÌÅÍ°¡ ¾øÀ»°æ¿ì ¿¡·¯ÄÚµå°¡ ´Ù¸¦°æ¿ì Ãß°¡
				for($t=0;$t<count($errLink[$j][3]);$t++)
					if($ErrCode==$errLink[$j][3][$t][1])return;
				//echo " ¿¡·¯¸µÅ©Á¸Àç ";

				$errLink[$j][1]="N";
			}

			//¿¡·¯ÄÚµå °°Àº°Í±îÁö ¾øÀ¸¸é Ãß°¡
			if($Env["f_debug_yn"])echo "<BR>¿¡·¯¸µÅ© Ãß°¡1:".$EnvCtl["Parent_doc"];

			$errLink[$j][2]++;
			$errLink[$j][3][count($errLink[$j][3])]=array($InjectMergeData,$ErrCode,$InjectQuery,$InjectForm);
			$errLink[$j][4]=$EnvCtl["Parent_doc"];
			$errLink[$j][5]=$OriginMergeData;
			$errLink[$j][6]=$FormMethod;

			return;
		}
	}
	//¸µÅ©¸í,ÆÄ¶ó¹ÌÅÍ¿©ºÎ,ÆÄ¶ó¹ÌÅÍÃ¼Å©¼ö,Äõ¸®½ºÆ®¸µ¹è¿­
	if($Env["f_debug_yn"])echo "<BR>¿¡·¯¸µÅ© ½Å±Ô2:".$EnvCtl["Parent_doc"];
	//echo "<BR>$ErrCode Ãß°¡";	
	$errLink[count($errLink)]=array($tLink,$ParamYN,1,array(array($InjectMergeData,$ErrCode,$InjectQuery,$InjectForm)),$EnvCtl["Parent_doc"],$OriginMergeData,$FormMethod);
	//echo "\n¾Æ¿ô¸µÅ© »çÀÌÁî :".count($errLink);
}
?>