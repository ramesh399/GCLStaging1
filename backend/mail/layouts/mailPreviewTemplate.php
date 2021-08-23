<style type="text/css">a { color:#b0ba25; text-decoration:underline;} a:hover { color:#f59f1a; text-decoration:none; } </style>
<table cellspacing='0' cellpadding='5'    width='100%'> 
 <tr class="templatetable-tr">
  <td colspan="2" class="templatetable-content-td">
    <table cellspacing='0' cellpadding='0' border='0' style="line-height:18px;"  width='100%'>
    <tr class="templatetable-tr">
        <td colspan="2" class="templatetable-td"><?php echo nl2br($content);?></td>
    </tr>
    <tr class="templatetable-tr">
        <td colspan="2" class="templatetable-td">
        <img src="<?php echo Yii::$app->params['site_path'];?>backend/web/signature_files/<?php echo $sign; ?>" border='0' class="templatetable-signimg"></td>
    </tr>
    </table>
  </td>
 </tr> 
</table>
