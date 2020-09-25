<?php

namespace Tests\Unit;

use App\Exceptions\PolicyMissingException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserUnitTest extends TestCase
{

    use WithFaker, RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = factory(\App\User::class)->create(['group' => null]);
        $this->user->handover = factory(\App\Handover::class)->make();
    }

    /** @test */
    public function user_can_have_a_first_name()
    {
        $name = $this->faker->firstName;
        $this->user->handover->first_name = $name;

        $this->assertEquals($name, $this->user->first_name);
    }

    /** @test */
    public function user_can_have_a_last_name()
    {
        $name = $this->faker->lastName;
        $this->user->handover->last_name = $name;

        $this->assertEquals($name, $this->user->last_name);
    }

    /** @test */
    public function user_can_have_full_name()
    {
        $firstName = $this->faker->firstName;
        $lastName = $this->faker->lastName;
        $name = $firstName . " " . $lastName;

        $handover = $this->user->handover;
        $handover->first_name = $firstName;
        $handover->last_name = $lastName;

        $this->assertEquals($name, $this->user->name);
    }

    /** @test */
    public function user_can_have_trainings_they_can_access()
    {
        $training = factory(\App\Training::class)->create(['user_id' => $this->user->id]);

        $this->user->can('view', $training)
            ? $this->assertTrue($this->user->viewableModels('\App\Training')->contains($training))
            : $this->assertFalse($this->user->viewableModels('\App\Training')->contains($training));

    }

    /** @test */
    public function trainings_can_exist_with_out_user_being_able_to_see_them()
    {
        $otherUser = factory(\App\User::class)->create(['id' => ($this->user->id + 1)]);
        $training = factory(\App\Training::class)->create(['user_id' => $otherUser->id]);

        $this->user->can('view', $training)
            ? $this->assertTrue($this->user->viewableModels('\App\Training')->contains($training))
            : $this->assertFalse($this->user->viewableModels('\App\Training')->contains($training));
    }

    /** @test */
    public function an_exception_is_thrown_if_a_policy_does_not_exist_for_class()
    {
        $this->expectException(PolicyMissingException::class);

        $this->user->viewableModels('\App\Test');
    }


}
