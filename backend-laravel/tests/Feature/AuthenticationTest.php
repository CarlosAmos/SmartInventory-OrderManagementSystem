<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    // ! Pass
    public function test_unauthorised_user_cannot_retrieve_orders(): void
    {
        $response = $this->getJson('/api/orders');
        
        // Should return 401 Unauthorized
        $response->assertStatus(401);
    }
}