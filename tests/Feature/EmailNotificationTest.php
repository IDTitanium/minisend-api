<?php

namespace Tests\Feature;

use App\Jobs\SendEmail;
use App\Models\EmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Http\File;

class EmailNotificationTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testValidationErrorsReturnedWhenPostRouteIsCalledWithoutParams()
    {
        $response = $this->post('/api/emails');
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testValidationErrorsReturnedWhenPostRouteIsCalledWithInvalidParams()
    {
        $attributes = [
            'from' => 'xyz',
            'to' => 'abc',
            'subject' => 'a test',
            'body' => 'test body'
        ];
        $response = $this->post('/api/emails', $attributes);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertSeeText('validation failed');
    }

    public function testCanCreateEmailNotification()
    {
        Queue::fake();
        Queue::assertNothingPushed();
        $attributes = [
            'from' => 'xyz@gmail.com',
            'from_name' => 'idris',
            'to' => 'abc@gmail.com',
            'subject' => 'a test',
            'body' => 'test body'
        ];
        $response = $this->post('/api/emails', $attributes);
        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertSeeText('Email posted successfully');
        Queue::assertPushed(SendEmail::class);
    }

    public function testCanCreateEmailNotificationWithAttachment()
    {
        Queue::fake();
        Queue::assertNothingPushed();
        $attributes = [
            'from' => 'xyz@gmail.com',
            'from_name' => 'idris',
            'to' => 'abc@gmail.com',
            'subject' => 'a test',
            'body' => 'test body',
            'attachments' => [new File(base_path('tests/resources/testFile.txt'))]
        ];
        $response = $this->post('/api/emails', $attributes);
        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertSeeText('Email posted successfully');
        Queue::assertPushed(SendEmail::class);
    }

    public function testCanGetAllEmailNotifications()
    {
        $attributes = [
            'from' => 'xyz@gmail.com',
            'from_name' => 'idris',
            'to' => 'abc@gmail.com',
            'subject' => 'a test',
            'body' => 'test body',
            'attachments' => [new File(base_path('tests/resources/testFile.txt'))]
        ];
        $response = $this->post('/api/emails', $attributes);
        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertSeeText('Email posted successfully');

        $response = $this->get('/api/emails');
        $response->assertStatus(Response::HTTP_OK);
        $response->assertSeeText('Records retrieved successfully');
        $data = $response->decodeResponseJson();
        $data = $data['data'][0];
        $response->assertJson([
                'data' => [
                    [
                        'id' => $data['id'],
                        'uuid' => $data['uuid'],
                        'sender' => $data['sender'],
                        'receiver' => $data['receiver'],
                        'body' => $data['body']
                    ]
                ]
        ]);
    }

    public function testCanGetSingleNotificationEmail()
    {
        $attributes = [
            'from' => 'xyz@gmail.com',
            'from_name' => 'idris',
            'to' => 'abc@gmail.com',
            'subject' => 'a test',
            'body' => 'test body',
            'attachments' => [new File(base_path('tests/resources/testFile.txt'))]
        ];
        $response = $this->post('/api/emails', $attributes);
        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertSeeText('Email posted successfully');

        $data = $response->decodeResponseJson();
        $data = $data['data'];

        $response = $this->get('/api/emails/'.$data['uuid']);
        $response->assertStatus(Response::HTTP_OK);
        $response->assertSeeText('Record retrieved successfully');
        $data = $response->decodeResponseJson();
        $data = $data['data'];
        $response->assertJson([
                'data' => [
                    'id' => $data['id'],
                    'uuid' => $data['uuid'],
                    'sender' => $data['sender'],
                    'receiver' => $data['receiver'],
                    'body' => $data['body']
                ]
        ]);
    }

}
