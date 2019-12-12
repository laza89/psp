<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    /**
     * Test for api/customer endpoint
     *
     * @return void
     */
    public function testPostCustomer()
    {
        $response = $this->post('/api/customer', [
            'gender' => 'male',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'country' => 'RS',
            'email' => 'john@doe.com'
        ]);

        $response->assertJson([
            'customer' => [
                'gender' => 'male',
                'firstname' => 'John',
                'lastname' => 'Doe',
                'country' => 'RS',
                'email' => 'john@doe.com'
            ]
        ]);
    }
}
