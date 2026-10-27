<?php

namespace App\Console\Commands;

use App\Enums\BrazilianState;
use App\Enums\CardBrand;
use App\Models\Address;
use App\Models\PaymentCard;
use App\Models\Product;
use App\Models\User;
use App\Support\BlindIndex;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Fills the store with fake, fully registered accounts (address + cards) for demos and load tests.
 * Cards only use public gateway test numbers, so no real PAN is ever generated or stored.
 */
#[Signature('app:generate-users
    {count=100 : Quantidade de usuários a gerar}
    {--sellers : Gera vendedores (com produtos à venda) em vez de compradores}
    {--force : Executa em produção sem pedir confirmação}')]
#[Description('Gera usuários fictícios completos (CPF, telefone, endereço e cartões) ou vendedores com produtos')]
class GenerateUsers extends Command
{
    use ConfirmableTrait;

    /**
     * Luhn-valid test numbers published by payment gateways; the simulated gateway approves all of them.
     *
     * @var list<string>
     */
    private const TEST_CARD_NUMBERS = [
        '4111111111111111',
        '4242424242424242',
        '4012888888881881',
        '5555555555554444',
        '5105105105105100',
        '2223003122003222',
        '378282246310005',
        '371449635398431',
        '6362970000457013',
        '5066991111111118',
    ];

    public function handle(): int
    {
        if (! $this->confirmToProceed()) {
            return self::FAILURE;
        }

        $count = filter_var($this->argument('count'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if ($count === false) {
            $this->components->error('A quantidade deve ser um número inteiro maior que zero.');

            return self::FAILURE;
        }

        $isSeller = (bool) $this->option('sellers');

        // Random per run (like the seeder) so generated accounts never share a known password.
        $password = Str::password(16);
        $hashedPassword = Hash::make($password);

        DB::transaction(function () use ($count, $isSeller, $hashedPassword): void {
            // Progress bar only on a terminal: scheduled runs append to a log file, which should get just the summary.
            if ($this->output->isDecorated()) {
                $this->withProgressBar(range(1, $count), fn () => $this->createAccount($hashedPassword, $isSeller));
                $this->newLine(2);

                return;
            }

            for ($created = 0; $created < $count; $created++) {
                $this->createAccount($hashedPassword, $isSeller);
            }
        });

        $this->components->info(sprintf(
            '%d %s gerados em %s.',
            $count,
            $isSeller ? 'vendedores' : 'compradores',
            now()->format('d/m/Y H:i'),
        ));
        // Raw output: the formatter would treat symbols such as "<", "\" or "[" as markup and print a different password.
        $this->output->writeln('Senha das contas geradas: '.$password, OutputInterface::OUTPUT_RAW);

        return self::SUCCESS;
    }

    private function createAccount(string $hashedPassword, bool $isSeller): void
    {
        $name = $isSeller ? fake()->company() : fake()->firstName().' '.fake()->lastName();

        $user = User::factory()->create([
            'name' => $name,
            // Random suffix keeps e-mails unique across scheduled runs, not only within one process.
            'email' => Str::slug($name, '.').'.'.Str::lower(Str::random(6)).'@'.fake()->safeEmailDomain(),
            'password' => $hashedPassword,
        ]);

        Address::factory()->for($user)->create([
            'recipient_name' => $name,
            'street' => fake()->streetName(),
            'city' => fake()->city(),
            'state' => fake()->randomElement(BrazilianState::cases()),
        ]);

        foreach (fake()->randomElements(self::TEST_CARD_NUMBERS, fake()->numberBetween(1, 2)) as $number) {
            PaymentCard::factory()->for($user)->create([
                'holder_name' => mb_strtoupper($name),
                'number' => $number,
                'fingerprint' => BlindIndex::make('card', $number),
                'brand' => CardBrand::detect($number),
                'last_four' => substr($number, -4),
                'exp_month' => fake()->numberBetween(1, 12),
                'exp_year' => now()->addYears(fake()->numberBetween(1, 6))->year,
            ]);
        }

        if ($isSeller) {
            Product::factory(fake()->numberBetween(3, 8))->for($user, 'seller')->create();
        }
    }
}
