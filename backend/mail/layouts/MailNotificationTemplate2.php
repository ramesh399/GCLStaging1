<table border="0" cellpadding="7" cellspacing="0" style="background:#909194;" width="100%">
   <thead>	
	 <tr>
		<td colspan="4" style="font-family:Arial; font-size:14px; font-weight:bold; text-align:left; color:#312F2F; background:#93AE2D;">Customer Details</td>		
	 </tr>
   </thead> 	
   <tbody>
	<tr>
		<td width="15%" style="background:#CCCDD1;font-family:Arial; font-size:12px; color:#000000; text-align:left;border-left:1px solid #909194; border-right:1px solid #909194; border-bottom:1px solid #909194;">First Name</td>
		<td style="font-family:Arial; font-size:12px; text-align:left;background:#F2F0F1;color:#000000;border-right:1px solid #909194; border-bottom:1px solid #909194;"><?php echo $model->first_name; ?></td>		
		<td width="15%" style="background:#CCCDD1;font-family:Arial; font-size:12px; text-align:left;color:#000000;border-right:1px solid #909194; border-bottom:1px solid #909194;">Last Name</td>
		<td style="font-family:Arial; font-size:12px; text-align:left;background:#F2F0F1;color:#000000;border-right:1px solid #909194; border-bottom:1px solid #909194;"><?php echo $model->last_name; ?></td>		
	</tr>
	<tr style="background:#FFFFFF;">
		<td width="20%" style="background:#CCCDD1;font-family:Arial; font-size:12px; text-align:left;color:#000000;border-left:1px solid #909194; border-right:1px solid #909194; border-bottom:1px solid #909194;">Email</td>
		<td style="font-family:Arial; font-size:12px; text-align:left;background:#F2F0F1;color:#000000;border-right:1px solid #909194; border-bottom:1px solid #909194;"><?php echo $model->email; ?></td>
		<td width="15%" style="background:#CCCDD1;font-family:Arial; font-size:12px; text-align:left;color:#000000;border-right:1px solid #909194; border-bottom:1px solid #909194;">Phone</td>
		<td style="font-family:Arial; font-size:12px; text-align:left;background:#F2F0F1;color:#000000;border-right:1px solid #909194; border-bottom:1px solid #909194;"><?php echo $model->telephone; ?></td>		
	</tr>
	<tr style="background:#FFFFFF;">
		<td width="20%" style="background:#CCCDD1;font-family:Arial; font-size:12px; text-align:left;color:#000000;border-left:1px solid #909194; border-right:1px solid #909194; border-bottom:1px solid #909194;">Country</td>
		<td style="font-family:Arial; font-size:12px; text-align:left;background:#F2F0F1;color:#000000;border-right:1px solid #909194; border-bottom:1px solid #909194;"><?php echo $model->country->name; ?></td>
		<td width="15%" style="background:#CCCDD1;font-family:Arial; font-size:12px; text-align:left;color:#000000;border-right:1px solid #909194; border-bottom:1px solid #909194;">State</td>
		<td style="font-family:Arial; font-size:12px; text-align:left;background:#F2F0F1;color:#000000;border-right:1px solid #909194; border-bottom:1px solid #909194;"><?php echo $model->state->name; ?></td>		
	</tr>
	</tbody>
</table>
<br>
<table border="0" cellpadding="7" cellspacing="0" style="background:#909194;" width="100%">
   <thead>	
	 <tr>
		<td colspan="4" style="font-family:Arial; font-size:14px; font-weight:bold; text-align:left; color:#312F2F; background:#93AE2D;">Company Details</td>		
	 </tr>
   </thead> 	
   <tbody>
	<tr>
		<td width="15%" style="background:#CCCDD1;font-family:Arial; font-size:12px; color:#000000; text-align:left;border-left:1px solid #909194; border-right:1px solid #909194; border-bottom:1px solid #909194;">First Name</td>
		<td style="font-family:Arial; font-size:12px; text-align:left;background:#F2F0F1;color:#000000;border-right:1px solid #909194; border-bottom:1px solid #909194;"><?php echo $model->first_name; ?></td>		
		<td width="15%" style="background:#CCCDD1;font-family:Arial; font-size:12px; text-align:left;color:#000000;border-right:1px solid #909194; border-bottom:1px solid #909194;">Last Name</td>
		<td style="font-family:Arial; font-size:12px; text-align:left;background:#F2F0F1;color:#000000;border-right:1px solid #909194; border-bottom:1px solid #909194;"><?php echo $model->last_name; ?></td>		
	</tr>
	<tr style="background:#FFFFFF;">
		<td width="20%" style="background:#CCCDD1;font-family:Arial; font-size:12px; text-align:left;color:#000000;border-left:1px solid #909194; border-right:1px solid #909194; border-bottom:1px solid #909194;">Email</td>
		<td style="font-family:Arial; font-size:12px; text-align:left;background:#F2F0F1;color:#000000;border-right:1px solid #909194; border-bottom:1px solid #909194;"><?php echo $model->email; ?></td>
		<td width="15%" style="background:#CCCDD1;font-family:Arial; font-size:12px; text-align:left;color:#000000;border-right:1px solid #909194; border-bottom:1px solid #909194;">Phone</td>
		<td style="font-family:Arial; font-size:12px; text-align:left;background:#F2F0F1;color:#000000;border-right:1px solid #909194; border-bottom:1px solid #909194;"><?php echo $model->telephone; ?></td>		
	</tr>
	<tr style="background:#FFFFFF;">
		<td width="20%" style="background:#CCCDD1;font-family:Arial; font-size:12px; text-align:left;color:#000000;border-left:1px solid #909194; border-right:1px solid #909194; border-bottom:1px solid #909194;">Country</td>
		<td style="font-family:Arial; font-size:12px; text-align:left;background:#F2F0F1;color:#000000;border-right:1px solid #909194; border-bottom:1px solid #909194;"><?php echo $model->country->name; ?></td>
		<td width="15%" style="background:#CCCDD1;font-family:Arial; font-size:12px; text-align:left;color:#000000;border-right:1px solid #909194; border-bottom:1px solid #909194;">State</td>
		<td style="font-family:Arial; font-size:12px; text-align:left;background:#F2F0F1;color:#000000;border-right:1px solid #909194; border-bottom:1px solid #909194;"><?php echo $model->state->name; ?></td>		
	</tr>
	</tbody>
</table>

