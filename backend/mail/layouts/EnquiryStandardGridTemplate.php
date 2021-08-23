<?php
$standards='';
$es=$model->enquirystandard; 
$eStandardArr=array();
if(count($es)>0)
{
	foreach($es as $enquirystandard)
	{
		$eStandardArr[]=$enquirystandard->standard->name;
	}
}
?>
<table border="0" cellpadding="7" cellspacing="0" style="background:#909194;" width="100%">
   <thead>	
	 <tr>
		<td style="font-family:Arial; font-size:14px; font-weight:bold; text-align:left; color:#312F2F; background:#93AE2D;">Standards</td>		
	 </tr>
   </thead> 	
   <tbody>
	<tr>
		<td style="background:#F2F0F1;font-family:Arial; font-size:12px; color:#000000; text-align:left;border-left:1px solid #909194; border-right:1px solid #909194; border-bottom:1px solid #909194;"><?php echo implode(', ',$eStandardArr); ?></td>
	</tr>
	</tbody>
</table>
