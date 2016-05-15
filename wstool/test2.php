<?
//echo "<BR>".$_SERVER["SERVER_NAME"];


echo urlencode("1' or 1=1--");

echo urldecode("%27");
echo urldecode("%5F");

echo "<BR>urlencode:".urlencode("abc_001ÇÑ±Û?=&/%+@");
echo "<BR>rawurlencode:".urlencode("abc_001ÇÑ±Û?=&/%+@");
echo "<BR>".eregi("[^a-z0-9]","qiewrsdfsdkfhsh2ÇÑ342y432421");

echo "<BR>ÇÑ±ÛÀÖ¾î?:".eregi("^[°¡-ÆR]+$","ÇÑ±Û");
?>