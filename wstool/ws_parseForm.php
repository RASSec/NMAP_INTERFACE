<?

//링크들 배열로 받아오기
function getFormAry($EnvCtl,$body){
	global $Env,$EnvAct;

	global $slist;
	global $whitespace;
	global $root_folder; //입력한 경로 이하만 검색

	global $linkpattern,$NonQuotaLink,$QuotaLink;//정규식1
	global $reg_str_form,$p_method,$p_action,$p_name,$p_type,$p_enctype,$p_onsubmit,$p_value;//정규식2
	global $reg_str_form,$reg_str_form_end,$reg_str_formDetail,$reg_str_input,$reg_str_select,$reg_str_textarea;//정규식3

	//설정안했으면 돌아가기
	if($EnvAct["form_check_yn"]<>"Y")return null;


	//$RootUrl에서 현재 경로 추출하기
	$ParentPath=GetFolderPath($EnvCtl["Parent_doc"]);

	//폼 검색
	$reg_str=$reg_str_form;

	$FormCnt=0;
	$InputCnt=1;
	$tmp2=$body;
	$matched=null;
	while(eregi($reg_str,$tmp2,$matched)){
		$pos=strpos($tmp2, $matched[0]);
		$tmp2=substr($tmp2,strlen($matched[0])+$pos);
		$matched2=null;
		if(		( eregi($p_action,$matched[0],$matched2)	&&	strlen(StripQuota(trim($matched2[1]))) > 0 ) 
			||	($EnvAct["script_action_yn"]=="Y" && is_array($EnvCtl["ActionAry"]))
			||	$EnvAct["self_action_yn"]=="Y"
			){
			//폼에 액션이 존재하면 폼정보 생성
			if($Env["f_debug_yn"]){
				echo "<BR>action매칭:".$matched2[1];
			}
			$tmpAction=GetUrlValid($EnvCtl,StripQuota(trim($matched2[1])),$ParentPath,$pathType="action");
			//echo "<BR>".StripQuota(trim($matched2[1]))." 불▶".is_bool($tmpAction);
			if(is_bool($tmpAction))continue; //제외cgi거나 타사이트이면 다음폼으로

			if(strlen($tmpAction)==0){//엑션이 없을 경우 액션을 스크립트action,또는 셀프action
				if($EnvAct["script_action_yn"]=="Y" && is_array($EnvCtl["ActionAry"])){
					$tmpAction="";
				}else if($EnvAct["self_action_yn"]=="Y"){
					$tmpAction=$EnvCtl["Parent_doc"];		
 				}
			}
			$aryForm[$FormCnt][0]["action"]=$tmpAction;
			$aryForm[$FormCnt][0]["actioncgi"]=GetLinkName($tmpAction);
			$aryForm[$FormCnt][0]["actionquery"]=GetQueryData($tmpAction);
			$aryForm[$FormCnt][0]["method"]="GET";//디펄트
			$matched2=null;
			if(eregi($p_method,$matched[0],$matched2))		$aryForm[$FormCnt][0]["method"]=strtoupper(StripQuota($matched2[1]));
			$matched2=null;
			if(eregi($p_name,$matched[0],$matched2))		$aryForm[$FormCnt][0]["name"]=StripQuota($matched2[1]);
			$matched2=null;
			if(eregi($p_enctype,$matched[0],$matched2))		$aryForm[$FormCnt][0]["enctype"]=strtoupper(StripQuota($matched2[1]));
			$matched2=null;
			if(eregi($p_onsubmit,$matched[0],$matched2))	$aryForm[$FormCnt][0]["onsubmit"]=StripQuota($matched2[1]);
			

			//input/select/texterea 이나, </form>찾기
			$reg_str_2="(".$reg_str_form_end."|".$reg_str_formDetail.")";
			$matched3=null;
			while(eregi($reg_str_2,$tmp2,$matched3)){
				$pos=strpos($tmp2, $matched3[0]);
				$tmp2=substr($tmp2,strlen($matched3[0])+$pos);
				$matched4=null;
				if(eregi($reg_str_form_end,$matched3[0],$matched4))	break;	//폼종료</form>이면 다음으로
				$matched5=null;
				if(eregi($reg_str_formDetail,$matched3[0],$matched5)){		//input|textarea|select 종류면 다시 파싱
					$tmp_name="";
					$tmp_value="";
					$matched6=null;
					$matched7=null;
					$matched8=null;
					if(eregi($reg_str_input,$matched5[0],$matched6)){
						$matched9=null;
						if(eregi($p_name,$matched5[0],$matched9))	$tmp_name=StripQuota($matched9[1]);
						$matched9=null;
						if(eregi($p_value,$matched5[0],$matched9))	$tmp_value=StripQuota($matched9[1]);
						$matched9=null;
						if(eregi($p_type,$matched5[0],$matched9))	$tmp_type=StripQuota($matched9[1]);
						if(strlen($tmp_name)>0){
							//라이오 버튼일때, 이미 추가했으면 통과
							if(strtoupper($tmp_type)=="RADIO"){
								//내역검사
								$isRadioEqualNameExist=false;
								for($rt=1;$rt<count($aryForm[$FormCnt]);$rt++){
									if($aryForm[$FormCnt][$rt]["name"]==$tmp_name){
										$isRadioEqualNameExist=true;
										break;
									}
								}
								if(!$isRadioEqualNameExist){
									$aryForm[$FormCnt][$InputCnt]["tag"]="INPUT";
									$aryForm[$FormCnt][$InputCnt]["type"]=strtoupper($tmp_type);
									$aryForm[$FormCnt][$InputCnt]["name"]=$tmp_name;
									$aryForm[$FormCnt][$InputCnt]["value"]=$tmp_value;
								}
							}else{
								$aryForm[$FormCnt][$InputCnt]["tag"]="INPUT";
								$aryForm[$FormCnt][$InputCnt]["type"]=strtoupper($tmp_type);
								$aryForm[$FormCnt][$InputCnt]["name"]=$tmp_name;
								$aryForm[$FormCnt][$InputCnt]["value"]=$tmp_value;
							}
						}
						//echo "\n<BR>INPUT : ".$tmp_name."=>[".$tmp_value."]";
					}else if(eregi($reg_str_select,$matched5[0],$matched7)){
						$matched9=null;
						if(eregi($p_name,$matched5[0],$matched9))	$tmp_name=StripQuota($matched9[1]);
						$matched9=null;
						if(eregi($p_value,$matched5[0],$matched9))	$tmp_value=StripQuota($matched9[1]);
						if(strlen($tmp_name)>0){
							$aryForm[$FormCnt][$InputCnt]["tag"]="SELECT";
							$aryForm[$FormCnt][$InputCnt]["name"]=$tmp_name;
							$aryForm[$FormCnt][$InputCnt]["value"]=$tmp_value;
						}
					}else if(eregi($reg_str_textarea,$matched5[0],$matched8)){
						$matched10=null;
						if(eregi($p_name,$matched8[0],$matched10)) $tmp_name=StripQuota($matched10[1]);
						$pos=strpos($tmp2, "</textarea>");
						$tmp_value=substr($tmp2,0,$pos);
						$tmp2=substr($tmp2,strlen("</textarea>")+$pos);
						if(strlen($tmp_name)>0){
							$aryForm[$FormCnt][$InputCnt]["tag"]="TEXTAREA";
							$aryForm[$FormCnt][$InputCnt]["name"]=$tmp_name;
							$aryForm[$FormCnt][$InputCnt]["value"]=$tmp_value;
						}
						//echo "\n<BR>TEXTAREA : ".$tmp_name."=>[".$tmp_value."]";
					}
					if($tmp_name!=""){
						$InputCnt++;
						//echo "\n<BR> ".$tmp_name."=>[".$tmp_value."]";
					}

				}
			}
			$InputCnt=1;
			$FormCnt++;
		}
		$i++;
	}

	//폼갯수 만큼 루프(이미 처리 했는지 검사)
	$returnForm=null;
	$rcnt=0;
	
	//검사시 버퍼에 추가하지 않음
	$EnvCtl["isAddBuffer"]=false;
	for($i=0;$i<count($aryForm);$i++){
		if($aryForm[$i][0]["action"]=="" && $EnvAct["script_action_yn"]=="Y" && is_array($EnvCtl["ActionAry"])){
			for($j=0;$j<count($EnvCtl["ActionAry"]);$j++){
				//action ary가 없으면 디펄트로 가기
				if($EnvCtl["ActionAry"][$j] !=""){
					$aryForm[$i][0]["action"]=$EnvCtl["ActionAry"][$j];
					$aryForm[$i][0]["actioncgi"]=GetLinkName($EnvCtl["ActionAry"][$j]);
					$aryForm[$i][0]["actionquery"]=GetQueryData($EnvCtl["ActionAry"][$j]);

					//이미 처리 했는지 검사(있으면true리턴, 없으면 저장후 false리턴)
					if(	$EnvAct["EXCEPT_URL"][strtoupper($t_actioncgi)] == "Y" ||
						CheckOutExist(
							$EnvCtl
							,$LinkName=$aryForm[$i][0]["actioncgi"]
							,$tMergeData=MergeQueryNForm($tQuery=$aryForm[$i][0]["actionquery"],$tForm=$aryForm[$i])
							,$CheckType="ALL"
							)
						)continue;
					$returnForm[$rcnt]=$aryForm[$i];
					$rcnt++;
				}
			}
		}else{
			//이미 처리 했는지 검사(있으면true리턴, 없으면 저장후 false리턴)
			if(	$EnvAct["EXCEPT_URL"][strtoupper($aryForm[$i][0]["actioncgi"])] == "Y" ||
				CheckOutExist(
					$EnvCtl
					,$LinkName=$aryForm[$i][0]["actioncgi"]
					,$tMergeData=MergeQueryNForm($tQuery=$aryForm[$i][0]["actionquery"],$tForm=$aryForm[$i])
					,$CheckType="ALL"
					)
				)continue;
			$returnForm[$rcnt]=$aryForm[$i];
			$rcnt++;
		}
	}

	return $returnForm;
}
?>