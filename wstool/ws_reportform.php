<?
	echo "<BR>ACTION:".$f_action;
	echo "<BR>METHOD:".$f_method;
	echo "<BR>ENCTYPE:".$f_enctype;
	//echo "<BR>f_querystring:".$f_querystring;
	//echo "<BR>f_inputstring:".$f_inputstring;
	
	if($f_method=="POST"){
		$aryInput=split("&",$f_inputstring);
	}else{
		//GET¹æ½Ä
		$aryInput=split("&",$f_querystring);
		$f_querystring="";
	}
	
?>
<table border=0 bgcolor=gray cellspacing=1 cellpadding=3>
<script language=javascript>
	function go_submit(target){
		tf=document.tform;
		tf.target=target;
		tf.submit();
	}
</script>
<form name="tform" onsubmit="return false;" action="<?=$f_action?>?<?=str_replace("\'","'",$f_querystring)?>" method="<?=$f_method?>" enctype="<?=$f_enctype?>">
	<tr><th width=150 bgcolor="gray"><font color=white>INPUT name</th><th bgcolor=gray><font color=white>value</th></tr>
	<?
	for($i=0;$i<count($aryInput);$i++){
		list($NameValue,$InputType)=split("¨à",$aryInput[$i]);
		list($name,$value)=split("=",$NameValue,2);

		if(strtolower($InputType)!="file"){
			$PrintType="text";
		}else{
			$PrintType="file";
		}
	?>
	<tr><td width=150 bgcolor="#efefef" align=center><?=$name?></td><td bgcolor=white><input type="<?=$PrintType?>" name="<?=$name?>" size="60" value="<?=urldecode(str_replace("\'","'",$value))?>"></td></tr>
	<?	
	}
	?>
	<tr><td colspan=2 bgcolor=white align=center><input type="button" onclick="go_submit('')" value="Submit"><input type="button" onclick="go_submit('_blank')"  value="New window"></td></tr>
</form>