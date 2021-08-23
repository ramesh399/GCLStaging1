<?php
namespace app\controllers;

use Yii;

class TestController extends \yii\rest\Controller
{

    /**
     * @inheritdoc
     */
	 /* 
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => \sizeg\jwt\JwtHttpBearerAuth::class,
        ];

        return $behaviors;
    }
	*/
	 public function behaviors()
	 {
		 return [
			[
				'class' => \yii\filters\ContentNegotiator::className(),
				//'only' => ['index', 'view'],
				'formats' => [
					'application/json' => \yii\web\Response::FORMAT_JSON,
				],
			],
			'corsFilter' => [
                'class' => \yii\filters\Cors::className(),
			],			
		];	
	}
	
	public function actionTest()
	{
		
		$mpdf = new \Mpdf\Mpdf(['mode' => 'ta-IN']);
		$html='
			<style>
			table {
				border-collapse: collapse;
			}
			
			
			table, td, th {
				border: 1px solid black;
			}
			
			table.reportDetailLayout {				
				border-collapse: collapse;
				width:100%;
				font-size:12px;
				font-family:Arial;
				text-align: left;
				margin-top:5px;
			}
			
			td.reportDetailLayout {
				text-align: center;
				border: 1px solid #000000;
				font-size:12px;
				font-family:Arial;
				/*
				background-color:#DFE8F6;
				*/
				padding:3px;
			}

			td.reportDetailLayoutInner {
				border: 1px solid #000000;
				font-size:12px;
				font-family:Arial;
				text-align: left;
				/*
				background-color:#ffffff;
				*/
				padding:3px;
				vertical-align:top;
			}
			
			td.reportDetailLayoutInnerWithoutBorder {	
				border:none;
				font-size:12px;
				font-family:Arial;
				text-align: left;
				/*
				background-color:#ffffff;
				*/				
				vertical-align:top;
			}
			
			.innerTitleMain
			{
				color:#000000;
				font-size:12px;
				font-family:Arial;
				text-align: left;
				margin-bottom:5px;
				font-weight:bold;
			}
			.innerTitle
			{
				color:#000000;
				font-size:12px;
				font-family:Arial;
				text-align: left;
				margin-bottom:5px;
			}
			div.reportDetailLayoutInner {
				border-left: 1px solid #000000;
				border-right: 1px solid #000000;
				border-bottom: 1px solid #000000;
				font-size:12px;
				font-family:Arial;
				text-align: left;
				/*
				background-color:#ffffff;
				*/
				padding:3px;
				vertical-align:top;
			}
			</style>';
		$html.= 'test ராஜா வாசு மூர்த்தி';
		$html.='<img  style="width:65%;margin-top:8px; float:left;" src="https://ssl.gcl-intl.com/demo/backend/web/images/front.jpg">';
		$html.=Yii::$app->params["image_files"].'front.jpg';
		$html.='<img src="'.file_get_contents(Yii::$app->params["image_files"].'front.jpg').'" />';		
		/*
		@page { 			
				background: url('.Yii::$app->params["image_files"].'front.jpg) no-repeat 0 0;
				width:300px;
				background-image-resize: 6;			
			}
		*/
		
		//$mpdf->Image(Yii::$app->params["image_files"].'front.jpg', 0, 0, 210, 297, 'jpg', '', true, false);		
		$mpdf->WriteHTML($html);
		$mpdf->Output();
		die;
		$passphrase = '20GCL191nTlLtDcM';
		$jsonString = '{"ct": "ZrD3Imh91CtZ7/Nnq/EaPhtE3ANCVuKElU9nzla+jPMS22a4Qa…7bA/V/ToX8o0j+y33hjgD4Yj45wOzF/omBNPDUjxnpW9XIA==", "iv": "bcecdc6a054ac01cbb341a2564f55968", "s": "673d65afa48e9284"}';
		
		$jsondata = json_decode($jsonString, true);
		
		$salt = hex2bin($jsondata["s"]);
		$ct = base64_decode($jsondata["ct"]);
		$iv  = hex2bin($jsondata["iv"]);
		$concatedPassphrase = $passphrase.$salt;
		$md5 = array();
		$md5[0] = md5($concatedPassphrase, true);
		$result = $md5[0];
		for ($i = 1; $i < 3; $i++) {
			$md5[$i] = md5($md5[$i - 1].$concatedPassphrase, true);
			$result .= $md5[$i];
		}
		$key = substr($result, 0, 32);
		$data = openssl_decrypt($ct, 'aes-256-cbc', $key, true, $iv);
		print_r($data);
		print_r(json_decode($data, true));
		//die;
		//echo json_decode($data, true);
		die;
		//echo json_decode($data, true);
		 
	}
	
}