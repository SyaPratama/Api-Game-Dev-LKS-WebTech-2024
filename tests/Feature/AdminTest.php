<?php

namespace Tests\Feature;

use App\Http\Controllers\Admins;
use Illuminate\Http\Request;
use Tests\TestCase;

class AdminTest extends TestCase
{

    public function test_create_admin()
    {
        $admin = $this->app->make(Admins::class);
        $request = Request::create('/admin/create',"POST",[
            "username" => "admin123",
            "password" => "admin123"
        ]);
        $create = $admin->createAdmin($request);

        $this->assertEquals(200,$create->getStatusCode());
    }
}
