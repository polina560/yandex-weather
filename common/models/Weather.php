<?php

namespace common\models;

use common\models\AppActiveRecord;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%weather}}".
 *
 * @property int    $id
 * @property string $key        Ключ
 * @property string $json       JSON
 * @property int    $created_at Дата создания
 */
#[Schema (properties: [
    new Property(property: 'json', type: 'string')
])]
class Weather extends AppActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%weather}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['json'], 'required'],
            [['json'], 'string'],
            [['created_at'], 'integer'],
            [['key'], 'string', 'max' => 255]
        ];
    }

    public function fields() {
        return [
            'json'
        ];
    }


    /**
     * {@inheritdoc}
     */
    final public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'key' => Yii::t('app', 'Key'),
            'json' => Yii::t('app', 'Json'),
            'created_at' => Yii::t('app', 'Created At'),
        ];
    }
}
