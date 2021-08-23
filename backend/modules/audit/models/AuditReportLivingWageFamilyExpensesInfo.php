<?php

namespace app\modules\audit\models;

use Yii;

/**
 * This is the model class for table "tbl_audit_report_living_wage_family_expense_info".
 *
 * @property int $id
 * @property int $client_information_family_expense_id
 * @property int $unit_id
 * @property int $category_id
 * @property string $category
 * @property float $cost_in_local_currency
 * @property float $number_of_individuals
 * @property float $total
 */
class AuditReportLivingWageFamilyExpensesInfo extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_audit_report_living_wage_family_expense_info';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['client_information_family_expense_id', 'category_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'client_information_family_expense_id' => 'Review ID',
            'category_id' => 'category ID',
            'cost_in_local_currency' => 'Answer',
            'number_of_individuals' => 'Comment',
        ];
    }
	
	
}
