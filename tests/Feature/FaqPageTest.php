<?php

use App\Models\Faq;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('faq page loads and displays categories', function () {
    Faq::create([
        'question' => 'Test question one?',
        'answer' => 'Test answer one.',
        'category' => 'General',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    Faq::create([
        'question' => 'Test question two?',
        'answer' => 'Test answer two.',
        'category' => 'Payments & Orders',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $response = $this->get(route('faq'));

    $response->assertOk();
    $response->assertSee('Frequently Asked');
    $response->assertSee('Test question one?');
    $response->assertSee('Test question two?');
});

test('faq page excludes inactive faqs', function () {
    Faq::create([
        'question' => 'Visible question?',
        'answer' => 'Visible answer.',
        'category' => 'General',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    Faq::create([
        'question' => 'Hidden question?',
        'answer' => 'Hidden answer.',
        'category' => 'General',
        'sort_order' => 1,
        'is_active' => false,
    ]);

    $response = $this->get(route('faq'));

    $response->assertOk();
    $response->assertSee('Visible question?');
    $response->assertDontSee('Hidden question?');
});

test('faq seeder creates expected number of faqs', function () {
    $this->seed(\Database\Seeders\FaqSeeder::class);

    expect(Faq::count())->toBe(47);
    expect(Faq::distinct('category')->pluck('category')->count())->toBe(11);
});
