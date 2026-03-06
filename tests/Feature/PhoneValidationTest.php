<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhoneValidationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        // Create an admin user to act as
        $this->admin = User::factory()->create(['role' => \App\Enums\UserRole::Admin]);
    }

    public function test_staff_phone_must_be_exactly_10_digits()
    {
        $this->actingAs($this->admin);

        // Test less than 10 digits
        $response = $this->post(route('admin.staff.store'), [
            'name' => 'John Doe',
            'phone' => '123456789', // 9 digits
            'staff_code' => '123456',
            'password' => 'password',
            'password_confirmation' => 'password',
            'monthly_salary' => 1000,
            'weekly_off_day' => 'Sunday',
        ]);
        $response->assertSessionHasErrors('phone');

        // Test more than 10 digits
        $response = $this->post(route('admin.staff.store'), [
            'name' => 'John Doe',
            'phone' => '12345678901', // 11 digits
            'staff_code' => '123456',
            'password' => 'password',
            'password_confirmation' => 'password',
            'monthly_salary' => 1000,
            'weekly_off_day' => 'Sunday',
        ]);
        $response->assertSessionHasErrors('phone');

        // Test exactly 10 digits
        $response = $this->post(route('admin.staff.store'), [
            'name' => 'John Doe',
            'phone' => '1234567890', // 10 digits
            'staff_code' => '123456',
            'password' => 'password',
            'password_confirmation' => 'password',
            'monthly_salary' => 1000,
            'weekly_off_day' => 'Sunday',
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['phone' => '1234567890']);
    }

    public function test_vendor_phone_must_be_exactly_10_digits()
    {
        $this->actingAs($this->admin);

        // Test less than 10 digits
        $response = $this->post(route('vendors.store'), [
            'name' => 'Test Vendor',
            'phone' => '123456789',
        ]);
        $response->assertJsonValidationErrors('phone');

        // Test more than 10 digits
        $response = $this->post(route('vendors.store'), [
            'name' => 'Test Vendor',
            'phone' => '12345678901',
        ]);
        $response->assertJsonValidationErrors('phone');

        // Test exactly 10 digits
        $response = $this->post(route('vendors.store'), [
            'name' => 'Test Vendor',
            'phone' => '1234567890',
        ]);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('vendors', ['phone' => '1234567890']);
    }

    public function test_profile_phone_must_be_exactly_10_digits()
    {
        $user = User::factory()->create(['role' => \App\Enums\UserRole::Staff]);
        $this->actingAs($user);

        // Test less than 10 digits
        $response = $this->put(route('profile.update'), [
            'name' => 'Updated Name',
            'phone' => '123456789',
        ]);
        $response->assertSessionHasErrors('phone');

        // Test exactly 10 digits
        $response = $this->put(route('profile.update'), [
            'name' => 'Updated Name',
            'phone' => '9876543210',
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'phone' => '9876543210']);
    }
}
