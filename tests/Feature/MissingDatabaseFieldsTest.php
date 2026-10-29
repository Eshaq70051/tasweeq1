<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MissingDatabaseFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_required_schema_fields_exist(): void
    {
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasColumn('users', 'role'));
        $this->assertTrue(Schema::hasColumn('users', 'is_admin'));
        $this->assertTrue(Schema::hasColumn('users', 'status'));
        $this->assertTrue(Schema::hasColumn('users', 'phone'));
        $this->assertTrue(Schema::hasColumn('users', 'avatar'));
        $this->assertTrue(Schema::hasColumn('users', 'google_id'));
        $this->assertTrue(Schema::hasColumn('users', 'is_google_account'));
        $this->assertTrue(Schema::hasColumn('users', 'is_available'));

        $this->assertTrue(Schema::hasTable('favorites'));
        $this->assertTrue(Schema::hasTable('marketing_sliders'));
        $this->assertTrue(Schema::hasTable('site_settings'));

        $this->assertTrue(Schema::hasColumn('orders', 'assigned_captain_id'));
        $this->assertTrue(Schema::hasColumn('order_items', 'product_id'));
    }
}
