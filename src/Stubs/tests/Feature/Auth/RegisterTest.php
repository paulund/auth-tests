<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Class RegisterTest
 * @package Tests\Feature\Auth
 *
 * @group auth
 */
class RegisterTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function it_can_register_a_user()
    {
        // Given
        Event::fake();
            
        // When
        $response = $this->post(route('register'), [
            'name' => 'John Smith',
            'email' => 'john.smith@email.com',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);
            
        // Then
        $users = User::all();
        $user = $users->first();
        $this->assertCount(1, $users);
        $this->assertAuthenticatedAs($user);
        $this->assertEquals('John Smith', $user->name);
        $this->assertEquals('john.smith@email.com', $user->email);
        $this->assertTrue(Hash::check('password', $user->password));
        Event::assertDispatched(Registered::class, function ($e) use ($user) {
            return $e->user->id === $user->id;
        });
    }
    
    /** @test */
    public function it_validates_a_user_without_name()
    {
        // Given
        Event::fake();
            
        // When
        $response = $this->post(route('register'), [
            'name' => '',
            'email' => 'john.smith@email.com',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);
            
        // Then
        $users = User::all();
        $this->assertCount(0, $users);
        $this->assertGuest();
        $response->assertSessionHasErrors('name');
        Event::assertNotDispatched(Registered::class);
    }
    
    /** @test */
    public function it_validates_a_user_without_email()
    {
        // Given
        Event::fake();

        // When
        $response = $this->post(route('register'), [
            'name' => 'John Smith',
            'email' => '',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        // Then
        $users = User::all();
        $this->assertCount(0, $users);
        $this->assertGuest();
        $response->assertSessionHasErrors('email');
        Event::assertNotDispatched(Registered::class);
            
    }

    /** @test */
    public function it_validates_a_user_without_password()
    {
        // Given
        Event::fake();

        // When
        $response = $this->post(route('register'), [
            'name' => 'John Smith',
            'email' => 'john.smith@email.com',
            'password' => '',
            'password_confirmation' => 'password'
        ]);

        // Then
        $users = User::all();
        $this->assertCount(0, $users);
        $this->assertGuest();
        $response->assertSessionHasErrors('password');
        Event::assertNotDispatched(Registered::class);

    }

    /** @test */
    public function it_validates_a_user_without_password_confirmation()
    {
        // Given
        Event::fake();

        // When
        $response = $this->post(route('register'), [
            'name' => 'John Smith',
            'email' => 'john.smith@email.com',
            'password' => 'password',
            'password_confirmation' => ''
        ]);

        // Then
        $users = User::all();
        $this->assertCount(0, $users);
        $this->assertGuest();
        $response->assertSessionHasErrors('password');
        Event::assertNotDispatched(Registered::class);

    }
    
    /** @test */
    public function it_validates_a_user_without_matching_password()
    {
        // Given
        Event::fake();

        // When
        $response = $this->post(route('register'), [
            'name' => 'John Smith',
            'email' => 'john.smith@email.com',
            'password' => 'password',
            'password_confirmation' => 'doesntmatch'
        ]);

        // Then
        $users = User::all();
        $this->assertCount(0, $users);
        $this->assertGuest();
        $response->assertSessionHasErrors('password');
        Event::assertNotDispatched(Registered::class);
            
    }
    
    /** @test */
    public function it_validates_email_if_already_exists()
    {
        // Given
        Event::fake();
        $user = factory(User::class)->create([
            'name' => 'John Smith',
            'email' => 'john.smith@email.com',
            'password' => bcrypt('password')
        ]);
            
        // When
        $response = $this->post(route('register'), [
            'name' => 'John Smith',
            'email' => 'john.smith@email.com',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);
            
        // Then
        $users = User::all();
        $this->assertCount(1, $users);
        $this->assertGuest();
        Event::assertNotDispatched(Registered::class);
    }
}