<?php

namespace app\modules\application;

/**
 * application module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\application\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
		
		$this->modules = [
            'master' => [
                // you should consider using a shorter namespace here!
                'class' => 'app\modules\master\Module',
            ],
        ];
        // custom initialization code goes here
    }
}
