<?php

use App\Models\AuditLog;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;

test('a denied access is written to the audit trail', function () {
    $order = Order::factory()->create();
    $intruder = User::factory()->create();

    $this->actingAs($intruder)->get(route('orders.show', $order))->assertForbidden();

    expect(AuditLog::where('event', 'security.access_denied')->sole())
        ->user_id->toBe($intruder->id)
        ->context->toBe(['method' => 'GET', 'path' => '/pedidos/'.$order->code]);
});

test('a non admin opening the admin area is written to the audit trail', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('admin.audit'))->assertForbidden();

    expect(AuditLog::where('event', 'security.access_denied')->sole())
        ->user_id->toBe($user->id)
        ->context->toMatchArray(['path' => '/admin/auditoria']);
});

test('tampering with a locked livewire property is written to the audit trail', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    report(new CannotUpdateLockedPropertyException('order'));

    expect(AuditLog::where('event', 'security.tampering')->sole())
        ->user_id->toBe($user->id)
        ->context->toMatchArray(['property' => 'order']);
});

test('tampering is still answered with an empty 419, revealing nothing', function () {
    config(['app.debug' => false]);

    $response = app(ExceptionHandler::class)->render(request(), new CannotUpdateLockedPropertyException('order'));

    expect($response->getStatusCode())->toBe(419)
        ->and($response->getContent())->toBe('');
});
