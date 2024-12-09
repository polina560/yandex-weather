<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%weather}}`.
 */
class m241209_134857_create_weather_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    final public function safeUp()
    {
        $this->createTable('{{%weather}}', [
            'id' => $this->primaryKey(),
            'key' => $this->string()->defaultValue('yandex_weather_json')->notNull()->comment('Ключ'),
            'file' => $this->string()->notNull()->comment('JSON файл'),
            'created_at' => $this->integer()->notNull()->comment('Дата создания'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    final public function safeDown()
    {
        $this->dropTable('{{%weather}}');
    }
}
