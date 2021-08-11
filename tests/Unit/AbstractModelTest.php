<?php

namespace Tests\Unit;

use Arr;
use Cache;
use Tests\TestCase;
use Gruz\FPBX\Models\User;


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
            'user_uuid',
            'domain_uuid',
            'contact_uuid',
            'salt',
            'api_key',
            'user_status'
        ]);
        // assert that after we get columns with getTableColumnsInfo
        // table columns can`t be empty
        // For future maybe it will be better to disable cache while test is running ???
        Cache::flush();
        $tableColumns = $userObj->getTableColumnsInfo();
        // $this->assertNotEmpty($tableColumns);
        // $expectedTableColumns = array_diff(
        //     array_keys($userObj->getTableColumnsInfo(true)),
        //     $userObj->getGuarded()
        // );
        Cache::flush();
        $expectedTableColumns = Arr::except(
            $userObj->getTableColumnsInfo(true),
            $userObj->getGuarded()
        );

        // dump($tableColumns);
        // dd($expectedTableColumns);
        $this->assertEquals($expectedTableColumns, $tableColumns);
        // remove guarded and tableColumns
        $userObj->guard([]);
        $tableColumns = [];
        $expectedTableColumns = [];

        // set fillable attributes
        $userObj->fillable([
            'username',
            'user_email',
            'user_status',
        ]);
        // assert that after we get columns with getTableColumnsInfo
        // table columns can`t be empty
        Cache::flush();
        $tableColumns = $userObj->getTableColumnsInfo();
        // $this->assertNotEmpty($tableColumns);
        Cache::flush();
        $expectedTableColumns = Arr::only($userObj->getTableColumnsInfo(true), $userObj->getFillable());
        // dump($tableColumns);
        // dd($expectedTableColumns);
        $this->assertEquals($expectedTableColumns, $tableColumns);
        // remove fillable and tableColumns
        $userObj->fillable([]);
        $tableColumns = [];

        // assert logic without fillable and guarded
        Cache::flush();
        $tableColumns = $userObj->getTableColumnsInfo();
        // $this->assertNotEmpty($tableColumns);
        Cache::flush();
        $expectedTableColumns = $userObj->getTableColumnsInfo(true);
        $this->assertEquals($expectedTableColumns, $tableColumns);

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
        Cache::flush();
        $tableColumns = $userObj->getTableColumnsInfo();
        // $this->assertNotEmpty($tableColumns);
        Cache::flush();
        $expectedTableColumns = Arr::except(
            $userObj->getTableColumnsInfo(true),
            $userObj->getGuarded()
        );
        $this->assertEquals($expectedTableColumns, $tableColumns);
    }
}
