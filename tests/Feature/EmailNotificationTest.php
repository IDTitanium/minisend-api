<?php

namespace Tests\Feature;

use App\Jobs\SendEmail;
use App\Mail\GenericMailer;
use App\Models\EmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Mail;

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

    public function testCanGetEmailNotificationStats() {
        Mail::fake();
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

        $response = $this->get('/api/emails/stats');
        $response->assertStatus(Response::HTTP_OK);
        $response->assertSeeText('Stats fetched successfully');
        $data = $response->decodeResponseJson();
        $data = $data['data'];
        $this->assertEquals(0, $data['countPosted']);
        $this->assertEquals(1, $data['countSent']);
        $this->assertEquals(0, $data['countFailed']);
        Mail::assertSent(GenericMailer::class);
    }

    public function testCanGetEmailsByReceiver() {
        Mail::fake();
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

        $response = $this->get('/api/emails/receiver?receiver='.$data['receiver']);
        $response->assertStatus(Response::HTTP_OK);
        $response->assertSeeText('Emails fetched successfully');
        $data = $response->decodeResponseJson();
        $data = $data['data'];
        $this->assertNotEmpty($data);
        $this->assertEquals(1, count($data));
        Mail::assertSent(GenericMailer::class);
    }

}
