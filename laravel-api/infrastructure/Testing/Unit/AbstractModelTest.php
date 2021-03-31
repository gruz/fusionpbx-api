<?php

namespace Tests\Unit;

use Api\User\Models\User;
use Infrastructure\Testing\TestCase;

class AbstractModelTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testGetTableColumnsInfo_Success()
    {
        // simulate initialization of model 
        $userObj = new User;
        $userObj->fillable([]);
        $userObj->guard([]);
        // dump($userObj->getGuarded());
        // dump($userObj->getFillable());

        // set guarded attributes
        $userObj->guard([
            'domain_uuid',
            'contact_uuid',
            'salt',
            'api_key',
            'user_status'
        ]);
        // assert that after we get columns with getTableColumnsInfo
        // table columns can`t be empty
        $tableColumns = $userObj->getTableColumnsInfo();
        $this->assertNotEmpty($tableColumns);
        $expectedTableColumns = array_diff(
            array_keys($userObj->getTableColumnsInfo(true)),
            $userObj->getGuarded()
        );
        $this->assertEquals($expectedTableColumns, $tableColumns);
        // remove guarded and tableColumns
        $userObj->guard([]);
        $tableColumns = [];

        // set fillable attributes
        $userObj->fillable([
            'username',
            'user_email',
            'user_status',
        ]);
        // assert that after we get columns with getTableColumnsInfo
        // table columns can`t be empty
        $tableColumns = $userObj->getTableColumnsInfo();
        dump($tableColumns);
        $this->assertNotEmpty($tableColumns);
        // remove fillable and tableColumns
        $userObj->fillable([]);
        $tableColumns = [];

        // assert logic without fillable and guarded
        $tableColumns = $userObj->getTableColumnsInfo();
        $this->assertNotEmpty($tableColumns);

        // set guarded and fillable
        $userObj->fillable([
            'username',
            'user_email',
            'user_status',
        ]);
        $userObj->guard([
            'domain_uuid',
            'contact_uuid',
            'salt',
            'api_key',
            'user_status'
        ]);
        // assert some logic
        $tableColumns = $userObj->getTableColumnsInfo();
        $this->assertNotEmpty($tableColumns);


        


        // ____________________________________________________

            // $response = $this->get('/');
            // $response->dumpHeaders();

            // $response->dumpSession();

            // $response->dump();

            // $response
            //     ->assertStatus(200)
            //     ->assertJson([
            //         'title' => 'FusionPBX API',
            // ]);

        // ______________________________________________________

    }
}