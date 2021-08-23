<?php

namespace app\modules\transfercertificate\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\master\models\User;
/**
 * This is the model class for table "tbl_tc_raw_material".
 *
 * @property int $id
 * @property string $supplier_name
 * @property string $trade_name
 * @property string $lot_number
 * @property string $net_weight
  * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class RawMaterial extends \yii\db\ActiveRecord
{
	public $arrStatus=array('0'=>'Approved' ,'1'=>'Archived');
	public $enumStatus=array('approved'=>'0' ,'archived'=>'1');	
	
    public $arrcertifiedStatus=array('1'=>'Yes','2'=>'No','3'=>'Reclaim');
    public $enumcertifiedStatus=array('yes'=>'1','no'=>'2','reclaim'=>'3');
	
	public $arrFileType=array('tc_attachment'=>'TC Attachment','farm_sc_attachment'=>'Farm SC Attachment','farm_tc_attachment'=>'Farm TC Attachment','trader_tc_attachment'=>'Trader TC Attachment','invoice_document'=>'Invoice Attachment','declaration_document'=>'Declaration Attachment');

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_tc_raw_material';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at','updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['supplier_name', 'trade_name', 'lot_number'], 'string'],
            [['status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            //['supplier_name', 'unique', 'targetAttribute' => ['supplier_name', 'created_by'],'filter' => ['!=','status' ,1]],
            ['tc_number', 'unique', 'targetAttribute' => ['tc_number', 'created_by'],'targetAttribute' => ['tc_number', 'is_certified'],'filter' => ['!=','status' ,1],'message' => 'TC No. has already been taken.'],
            //[['tc_number', 'franchise_id'], 'uniquerole_franchise']
            //tc_number
        ];
    }	
	

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'supplier_name' => 'Supplier Name',
            'trade_name' => 'Trade Name',
			'lot_number' => 'Lot Number',
			'tc_number' => 'TC No.',
			'net_weight' => 'Net Weight',
			'is_certified' => 'Is Certified',			
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
            'certification_body_id' => 'Certification Body',

            'net_weight' => 'Balance Weight',
            'actual_net_weight' => 'Net Weight',
            'total_used_weight' => 'Total Used Weight',
            'certified_weight' => 'Certified Weight',
            'gross_weight' => 'Gross Weight',

            'form_tc_number' => 'Farm TC No.',
            'form_sc_number' => 'Farm SC No.',
            'trade_tc_number' => 'Trader TC No.',
            'invoice_number' => 'Invoice Number'
           
        ];
    }	
    
    public function getCreatedbydata()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    public function getStandard()
    {
        return $this->hasMany(RawMaterialStandard::className(), ['raw_material_id' => 'id']);
    }

    public function getRawmaterialhistory()
    {
        return $this->hasMany(RawMaterialHistory::className(), ['raw_material_id' => 'id']);
    }

    
    public function getCertificationbody()
    {
        return $this->hasOne(InspectionBody::className(), ['id' => 'certification_body_id']);
    }

    public function getLabelgrade()
    {
        return $this->hasMany(RawMaterialLabelGrade::className(), ['raw_material_id' => 'id']);
    }

    public function getProduct()
    {
        return $this->hasMany(RawMaterialProduct::className(), ['raw_material_id' => 'id']);
    }

    public function getUsedweightlist()
    {
        return $this->hasMany(TcRawMaterialUsedWeight::className(), ['tc_raw_material_id' => 'id']);
    }

    public function getUsedweightlistonly()
    {
        return $this->hasMany(TcRawMaterialUsedWeight::className(), ['tc_raw_material_id' => 'id'])->andOnCondition(['status'=>0]);
    }
	
	public function sumOfRawMaterialProductWeight($RawMaterialID)
	{
		$arrRawMaterialWeight=array();
		$arrRawMaterialWeight['balance_weight']=0;
		$arrRawMaterialWeight['gross_weight']=0;
		$arrRawMaterialWeight['certified_weight']=0;
		$arrRawMaterialWeight['net_weight']=0;
		$arrRawMaterialWeight['used_weight']=0;
		
		$rawMaterialSumQuery = 'SELECT SUM(net_weight) AS balance_weight,SUM(gross_weight) AS gross_weight,	SUM(certified_weight) AS certified_weight,
		SUM(actual_net_weight) AS net_weight,SUM(total_used_weight) AS used_weight FROM `tbl_tc_raw_material_product` WHERE raw_material_id='.$RawMaterialID;
		
		$connection = Yii::$app->getDb();	
		$connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();							
		$command = $connection->createCommand($rawMaterialSumQuery);
		$result = $command->queryOne();
		if($result !== false)
		{
			$arrRawMaterialWeight['balance_weight']=$result['balance_weight'];
			$arrRawMaterialWeight['gross_weight']=$result['gross_weight'];
			$arrRawMaterialWeight['certified_weight']=$result['certified_weight'];
			$arrRawMaterialWeight['net_weight']=$result['net_weight'];
			$arrRawMaterialWeight['used_weight']=$result['used_weight'];
		}
		return $arrRawMaterialWeight;
	}
	
	
	public function updateProductWeightRawMaterial($RawMaterialIDsArray)
	{
		$rawMaterialIds=0;
		if(is_array($RawMaterialIDsArray) && count($RawMaterialIDsArray)>0)
		{
			foreach($RawMaterialIDsArray as $rawMaterialID)
			{	
                $connection = Yii::$app->getDb();	
                $connection->createCommand("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")->execute();
                		
				$rawMaterialSumQuery = 'SELECT SUM(net_weight) AS balance_weight,SUM(total_used_weight) AS used_weight FROM `tbl_tc_raw_material_product` WHERE raw_material_id='.$rawMaterialID;
				$command = $connection->createCommand($rawMaterialSumQuery);
				$result = $command->queryOne();		
				if($result !== false)
				{
					$model = RawMaterial::find()->where(['id' => $rawMaterialID])->one();
					$model->net_weight=$result['balance_weight'];															
					$model->total_used_weight=$result['used_weight'];
					$model->save();				
				}
			}
		}			
	}
}
