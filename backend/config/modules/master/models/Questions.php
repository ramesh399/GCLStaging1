<?php

namespace app\modules\master\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tbl_questions".
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $guidance
 * @property int $category 1=Application Review,2=Application Unit Review
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */
class Questions extends \yii\db\ActiveRecord
{

    public $riskList= [6=>'Critical',5=>'High',4=>'Medium',3=>'Low',2=>'Very Low',1=>'N/A'];
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_questions';
    }

    /**
     * {@inheritdoc}
     */
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

    
    public function rules()
    {
        return [
            [['guidance'], 'string'],
            [['category', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['code'], 'string', 'max' => 255],
			//[['name'], 'unique', 'targetAttribute' => ['name', 'category']],
			//['name', 'unique', 'attribute' => ['name', 'category']]
			//['name', 'unique'],
			['name', 'unique', 'targetAttribute' => ['name', 'category'],'filter' => ['!=','status' ,2]]
        ];
    }

    /**
     * {@inheritdoc}
     */


    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'code' => 'Code',
            'guidance' => 'Guidance',
            'category' => 'Category',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }
    public function riskListArray(){
        $riskList= [];
        foreach($this->riskList as $key => $risk){
			$riskArr['id']= $key;
            $riskArr['name']= $risk;
            $riskList[] = $riskArr;
        }
        return $riskList;
    }
}
