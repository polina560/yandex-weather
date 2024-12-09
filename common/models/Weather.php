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
 * @property string $file       JSON файл
 * @property int    $created_at Дата создания
 */

#[Schema ( properties: [
    new Property(property: 'file', type: 'string')
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
            [['file'], 'required'],
            [['created_at'], 'integer'],
            [['key', 'file'], 'string', 'max' => 255]
        ];
    }

    public function fields()
    {
        return [
          'file' => fn() => file_get_contents(Yii::$app->request->hostInfo . $this->file)
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
            'file' => Yii::t('app', 'File'),
            'created_at' => Yii::t('app', 'Created At'),
        ];
    }
}
