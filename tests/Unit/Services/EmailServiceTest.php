<?php

use App\Mail\SimpleEmailMailable;
use App\Services\EmailService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    // Isola os testes em sqlite para nao depender do banco real do projeto.
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');

    // Cria a tabela de configuracoes SMTP usada pelo service antes dos envios.
    Schema::create('system_settings', function (Blueprint $table) {
        $table->id();
        $table->string('key')->unique();
        $table->longText('value')->nullable();
        $table->boolean('is_encrypted')->default(false);
        $table->timestamps();
    });

    // Cria apenas a tabela usada por este service para manter o teste focado.
    Schema::create('email_dispatch_logs', function (Blueprint $table) {
        $table->id();
        $table->string('recipient_email');
        $table->string('recipient_name')->nullable();
        $table->string('subject');
        $table->string('template')->default('emails.simple');
        $table->string('status', 20);
        $table->text('error_message')->nullable();
        $table->json('payload')->nullable();
        $table->timestamp('sent_at')->nullable();
        $table->timestamps();
    });
});

afterEach(function () {
    // Limpa a tabela temporaria para manter independencia entre cenarios.
    Schema::dropIfExists('system_settings');
    Schema::dropIfExists('email_dispatch_logs');
});

test('it sends a single email and stores a success log', function () {
    Mail::fake();

    $service = app(EmailService::class);

    // Executa o envio simples com personalizacao basica de nome.
    $result = $service->send(
        ['email' => 'joao@example.com', 'name' => 'Joao'],
        'Boas-vindas',
        ['message_body' => 'Sua conta foi criada com sucesso.'],
    );

    expect($result['success'])->toBeTrue();

    // Garante que o mailable recebeu o assunto e os dados do template corretamente.
    Mail::assertSent(SimpleEmailMailable::class, function (SimpleEmailMailable $mail) {
        return $mail->subjectLine === 'Boas-vindas'
            && $mail->data['recipient_name'] === 'Joao'
            && $mail->data['message_body'] === 'Sua conta foi criada com sucesso.';
    });

    // Confirma o registro do envio para auditoria basica.
    $this->assertDatabaseHas('email_dispatch_logs', [
        'recipient_email' => 'joao@example.com',
        'recipient_name' => 'Joao',
        'subject' => 'Boas-vindas',
        'status' => 'success',
        'template' => 'emails.simple',
    ], 'sqlite');
});

test('it sends emails to multiple recipients and stores one log per recipient', function () {
    Mail::fake();

    $service = app(EmailService::class);

    // Dispara o mesmo conteudo para mais de um destinatario no mesmo lote.
    $result = $service->sendMany(
        [
            ['email' => 'ana@example.com', 'name' => 'Ana'],
            ['email' => 'maria@example.com', 'name' => 'Maria'],
        ],
        'Comunicado',
        ['message_body' => 'Mensagem padrao para a lista.'],
    );

    expect($result['success'])->toBeTrue()
        ->and($result['total'])->toBe(2)
        ->and($result['success_count'])->toBe(2)
        ->and($result['error_count'])->toBe(0);

    // Verifica que o Laravel recebeu os dois envios esperados.
    Mail::assertSent(SimpleEmailMailable::class, 2);

    // Confere que cada destinatario teve seu proprio log persistido.
    $this->assertDatabaseCount('email_dispatch_logs', 2, 'sqlite');
    $this->assertDatabaseHas('email_dispatch_logs', [
        'recipient_email' => 'ana@example.com',
        'status' => 'success',
    ], 'sqlite');
    $this->assertDatabaseHas('email_dispatch_logs', [
        'recipient_email' => 'maria@example.com',
        'status' => 'success',
    ], 'sqlite');
});

test('it stores an error log when the email send fails', function () {
    // Simula falha do SMTP sem depender de servidor externo nos testes.
    Mail::shouldReceive('to')
        ->once()
        ->with('falha@example.com')
        ->andReturnSelf();

    Mail::shouldReceive('send')
        ->once()
        ->andThrow(new Exception('SMTP indisponivel'));

    $service = app(EmailService::class);

    // Executa o fluxo de erro para validar a persistencia do status de falha.
    $result = $service->send(
        ['email' => 'falha@example.com', 'name' => 'Falha'],
        'Teste de erro',
        ['message_body' => 'Nao deve ser enviado.'],
    );

    expect($result['success'])->toBeFalse()
        ->and($result['error'])->toBe('SMTP indisponivel');

    // Garante que a falha tambem fica auditavel na base.
    $this->assertDatabaseHas('email_dispatch_logs', [
        'recipient_email' => 'falha@example.com',
        'recipient_name' => 'Falha',
        'subject' => 'Teste de erro',
        'status' => 'error',
        'error_message' => 'SMTP indisponivel',
    ], 'sqlite');
});
