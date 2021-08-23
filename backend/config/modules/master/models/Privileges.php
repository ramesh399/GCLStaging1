<?php

namespace app\modules\master\models;

use Yii;

/**
 * This is the model class for table "tbl_privileges".
 *
 * @property int $id
 * @property int $parent_id
 * @property string $name
 * @property string $code
 * @property int $status
 */
class Privileges extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_privileges';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['parent_id', 'status'], 'integer'],
            [['name', 'code'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'parent_id' => 'Parent ID',
            'name' => 'Name',
            'code' => 'Code',
            'status' => 'Status',
        ];
    }
	
	public function buildTree($privileges, $parent_id = 0, $tree = array())
	{
		
		foreach($privileges as $idx => $row)
		{
			if($row['parent_id'] == $parent_id)
			{
				foreach($row as $k => $v)
				{
                    $tree[$row['id']][$k] = $v;
					$tree[$row['id']]['checked'] = false;
				}
				unset($privileges[$idx]);
				
				$tree[$row['id']]['children'] = $this->buildTree($privileges, $row['id']);
				
			}
		}
		ksort($tree);
		
		
		return $tree;
    }
    /*
    public function buildTree($privileges, $parent_id = 0, $tree = array())
	{
		
		foreach($privileges as $idx => $row)
		{
			if($row['parent_id'] == $parent_id)
			{
				foreach($row as $k => $v)
				{
					$tree[$row['id']][$k] = $v;
					$tree[$row['id']]['checked'] = true;
				}
				unset($privileges[$idx]);
				
				$tree[$row['id']]['children'] = $this->buildTree($privileges, $row['id']);
				
			}
		}
		ksort($tree);
		
		
		return $tree;
    }
    */
}
