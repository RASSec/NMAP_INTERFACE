<?

//링크들 배열로 받아오기
function getLinkAry($EnvCtl,$body){
	global $Env,$EnvAct;

	global $slist;
	global $whitespace;
	global $root_folder; //입력한 경로 이하만 검색

	global $linkpattern,$NonQuotaLink,$QuotaLink;//정규식1
	global $reg_str_form,$p_method,$p_action,$p_name,$p_type,$p_enctype,$p_onsubmit,$p_value;//정규식2
	global $reg_str_form,$reg_str_form_end,$reg_str_formDetail,$reg_str_input,$reg_str_select,$reg_str_textarea;//정규식3
	global $reg_link_type;
	global $EnvAct;
	//리턴할 배열
	$tLink=null;
	$tActionAry=null;

	//$RootUrl에서 현재 경로 추출하기
	$ParentPath=GetFolderPath($EnvCtl["Parent_doc"]);


	//링크 검색
	/*

	$reg_str="(".$reg_link_type.")([".$whitespace."]*=[".$whitespace."]*)".
		"(".
			"(\')".$QuotaLink."(\')".
			"|(\")".$QuotaLink."(\")".
			"|()".$NonQuotaLink."([\>".$whitespace."])".
		")";//:제거
	*/
	//<META HTTP-EQUIV=Refresh CONTENT="10; URL=http://www.htmlhelp.com/">  반영
	$reg_str="(".$reg_link_type.")([".$whitespace."]*=[".$whitespace."]*)".
		"(".
			"(\')([^\#\'][^\']*)(\')".
			"|(\")([^\#\"][^\"]*)(\")".
			"|()([^\#".$whitespace."\>\"\'][^".$whitespace."\>\"\']*)([\'\"\>".$whitespace."])".
		")";//:제거

	/*
	$reg_str="(href|action|location|src)([".$whitespace."]*=[".$whitespace."]*)".
		"(".
			"(\')([^\']+)(\')".
			"|(\")([^\"]+)(\")".
			"|()([^\>]+)(\>)".
			"|()([^".$whitespace."]+)([".$whitespace."])".
		")";//:제거
	*/

	$pos=0;
	$i=0;
	$matched=null;
	//echo "<BR>파싱 link:";
	while(eregi($reg_str,$body,$matched)){
		//echo "<BR>매칭:".$matched[0];
		$pos=strpos($body, $matched[0]);
		$body=substr($body,strlen($matched[0])+$pos);

		$pathAll=trim($matched[0]);
		$pathType=strtoupper(trim($matched[1]));
		$path=StripQuota($matched[3]);

		if($path=="")continue;//없는링크이면통과
		list($link,$query)=split("\?",$path,2);

		//링크 올바른지 검사후
		$tmpLink=GetUrlValid($EnvCtl,$link,$ParentPath,$pathType);
		if(is_bool($tmpLink))continue; //제외cgi거나 타사이트이면 다음폼으로

		//해당 링크가 이미처리에 존재하는지 검사
		$EnvCtl["isAddBuffer"]=false;
		if(	!CheckOutExist($EnvCtl,$tLinkName=$tmpLink,$tMergeData=$query,$tCheckType="ALL")){
			$turl=$tmpLink;
			if($pathType=="ACTION"){
				//엑션 리스트에 저장
				$tActionAry[count($tActionAry)]=$turl;
				//echo "<br>▶ACTION ".$turl;
			}else{
				//링크 에러 저장
				if(strlen($query)>0)$turl.="?".$query;
				//echo "<BR>파싱 URL:".strtoupper($pathType)." " .$turl;
				$tLink[$i]["url"]=$turl;
				$tLink[$i]["link"]=$tmpLink;
				$tLink[$i]["query"]=$query;
				$tLink[$i]["type"]=$pathType;

				//echo "<br>▶$pathType ".$turl;
				$i++;
			}
		}else{
			$turl=$tmpLink;
			if(strlen($query)>0)$turl.="?".$query;
			//echo "<BR>존재 URL:".$turl;
		}
	}
	return array($tLink,$tActionAry);
}
?>