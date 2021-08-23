<style type="text/css">a { color:#b0ba25; text-decoration:underline;} a:hover { color:#f59f1a; text-decoration:none; } </style>
<table cellspacing='0' cellpadding='5'  class="templatetable"  width='100%'>
 <tr class="templatetable-tr">
  <td class="templatetable-firstheader-td">
  <img src="<?php echo Yii::$app->params['site_path']; ?>backend/web/images/header-img.jpg" border='0'>
  </td>
  <td class="templatetable-secondheader-td"><img src='' border='0'><br><strong>GCL International</strong></td>
 </tr>
 <tr class="templatetable-tr">
  <td colspan="2" class="templatetable-content-td">
    <table cellspacing='0' cellpadding='0' border='0' style="line-height:18px;"  width='100%'>
    <tr class="templatetable-tr">
        <td colspan="2" class="templatetable-td"><?php echo $content;?></td>
    </tr>
    <tr class="templatetable-tr">
        <td colspan="2" class="templatetable-td">
        <img src="<?php echo Yii::$app->params['site_path'];?>backend/web/signature_files/<?php echo $sign; ?>" border='0' height='5%' class="templatetable-signimg"></td>
    </tr>
    </table>
  </td>
 </tr>
 <tr bgColor='#3E85C5'>
  <td colspan="2" class="templatetable-footer-td">&copy; <?php echo date('Y');?> GCL International. All Rights Reserved.</td>
 </tr>
</table>
