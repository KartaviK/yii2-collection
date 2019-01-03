<?php

namespace Kartavik\Yii\Tests\Migrations;

use yii\db\Migration;

/**
 * Class M190103132334CreateCustomersTable
 */
class M190103132334CreateCustomersTable extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('customers', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'age' => $this->integer()->notNull()
        ]);
    }

    public function safeDown(): void
    {
        $this->dropTable('customers');
    }
}
