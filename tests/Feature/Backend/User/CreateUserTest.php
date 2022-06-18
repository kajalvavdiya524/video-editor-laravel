<?php

namespace Tests\Feature\Backend\User;

use App\Domains\Auth\Models\Role;
use App\Domains\Auth\Models\User;
use App\Domains\Auth\Notifications\Frontend\VerifyEmail;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Class CreateUserTest.
 */
class CreateUserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_admin_can_access_the_create_user_page()
    {
        $this->withoutMiddleware(RequirePassword::class);

        $this->loginAsAdmin();

        $response = $this->get('/admin/auth/user/create');

        $response->assertOk();
    }

    /** @test */
    public function create_user_requires_validation()
    {
        $this->withoutMiddleware(RequirePassword::class);

        $this->loginAsAdmin();

        $response = $this->post('/admin/auth/user');

        $response->assertSessionHasErrors(['name', 'email', 'password', 'roles']);
    }

    /** @test */
    public function user_email_needs_to_be_unique()
    {
        $this->withoutMiddleware(RequirePassword::class);

        $this->loginAsAdmin();

        factory(User::class)->create(['email' => 'john@example.com']);

        $response = $this->post('/admin/auth/user', [
            'email' => 'john@example.com',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function admin_can_create_new_user()
    {
        $this->withoutMiddleware(RequirePassword::class);

        $this->loginAsAdmin();

        $response = $this->post('/admin/auth/user', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'OC4Nzu270N!QBVi%U%qX',
            'password_confirmation' => 'OC4Nzu270N!QBVi%U%qX',
            'active' => '1',
            'roles' => [
                Role::whereName(config('boilerplate.access.role.admin'))->first()->id,
            ],
        ]);

        $this->assertDatabaseHas(
            'users',
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'active' => true,
            ]
        );

        $this->assertDatabaseHas('model_has_roles', [
            'role_id' => Role::whereName(config('boilerplate.access.role.admin'))->first()->id,
            'model_type' => User::class,
            'model_id' => User::whereEmail('john@example.com')->first()->id,
        ]);

        $response->assertSessionHas(['flash_success' => __('The user was successfully created.')]);
    }

    /** @test */
    public function when_an_unconfirmed_user_is_created_a_notification_will_be_sent()
    {
        $this->withoutMiddleware(RequirePassword::class);

        Notification::fake();

        $this->loginAsAdmin();

        $response = $this->post('/admin/auth/user', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'OC4Nzu270N!QBVi%U%qX',
            'password_confirmation' => 'OC4Nzu270N!QBVi%U%qX',
            'send_confirmation_email' => '1',
            'roles' => [
                Role::whereName(config('boilerplate.access.role.admin'))->first()->id,
            ],
        ]);

        $response->assertSessionHas(['flash_success' => __('The user was successfully created.')]);

        $user = User::where('email', 'john@example.com')->first();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /** @test */
    public function only_admin_can_create_users()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->get('/admin/auth/user/create');

        $response->assertSessionHas('flash_danger', __('You do not have access to do that.'));
    }
}
