<?php

namespace Database\Seeders;

use App\Enums\BrazilianState;
use App\Models\Address;
use App\Models\PaymentCard;
use App\Models\Product;
use App\Models\User;
use App\Services\ProductImageStorage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed demo accounts. Passwords are random (or taken from SEED_USER_PASSWORD)
     * so the public repository never contains working credentials.
     */
    public function run(ProductImageStorage $images): void
    {
        $password = env('SEED_USER_PASSWORD') ?: Str::password(16);

        $admin = User::factory()->admin()->create([
            'name' => 'Administrador',
            'email' => 'admin@secureshop.test',
            'password' => $password,
        ]);

        $seller = User::factory()->create([
            'name' => 'Loja do Vendedor',
            'email' => 'vendedor@secureshop.test',
            'password' => $password,
        ]);

        $buyer = User::factory()->create([
            'name' => 'Maria Compradora',
            'email' => 'comprador@secureshop.test',
            'password' => $password,
        ]);

        Address::factory()->for($buyer)->create([
            'label' => 'Casa',
            'recipient_name' => 'Maria Compradora',
            'zip_code' => '80010000',
            'street' => 'Rua XV de Novembro',
            'number' => '100',
            'district' => 'Centro',
            'city' => 'Curitiba',
            'state' => BrazilianState::PR,
        ]);

        PaymentCard::factory()->for($buyer)->create(['holder_name' => 'MARIA COMPRADORA']);
        PaymentCard::factory()->for($buyer)->declined()->create(['holder_name' => 'MARIA COMPRADORA']);

        $this->resetProductImages();

        $otherSellers = User::factory(2)->create();

        $catalog = [
            [$seller, 'Fone de ouvido Bluetooth', 'Fone sem fio com cancelamento de ruído e 30h de bateria.', 29990, 15, 'fone.jpg'],
            [$seller, 'Teclado mecânico ABNT2', 'Switches blue, iluminação RGB e cabo destacável.', 34900, 8, 'teclado.jpg'],
            [$seller, 'Mouse sem fio', 'Sensor óptico de alta precisão, receptor USB nano e 2 anos de pilha.', 15990, 20, 'mouse.jpg'],
            [$seller, 'Notebook 14"', 'Processador de 8 núcleos, 16 GB de RAM e SSD de 512 GB.', 389900, 4, 'notebook.jpg'],
            [$seller, 'Cadeira ergonômica', 'Apoio lombar ajustável, encosto em tela e braços 3D.', 124900, 3, 'cadeira.jpg'],
            [$seller, 'Webcam Full HD', 'Webcam 1080p com microfone estéreo e correção automática de luz.', 19990, 12, 'webcam.jpg'],
            [$otherSellers[0], 'Smartphone 128 GB', 'Tela de 6,5", câmera tripla de 50 MP e bateria de 5000 mAh.', 149900, 10, 'smartphone.jpg'],
            [$otherSellers[0], 'Smartwatch esportivo', 'Monitor cardíaco, GPS integrado e resistência à água 5 ATM.', 89900, 6, 'smartwatch.jpg'],
            [$otherSellers[1], 'Caixa de som Bluetooth', 'Som 360°, à prova d\'água IPX7 e 12h de reprodução.', 49990, 9, 'caixa-de-som.jpg'],
            [$otherSellers[1], 'Câmera DSLR', 'Sensor full-frame de 45 MP e gravação em 4K.', 1599900, 2, 'camera.jpg'],
        ];

        foreach ($catalog as [$owner, $name, $description, $price, $stock, $image]) {
            Product::factory()->for($owner, 'seller')->create([
                'name' => $name,
                'description' => $description,
                'price_cents' => $price,
                'stock' => $stock,
                'image_path' => $images->store(new UploadedFile(database_path('seeders/images/'.$image), $image, 'image/jpeg', null, true)),
            ]);
        }

        $this->command?->warn('Contas de demonstração (senha: '.$password.')');
        $this->command?->table(['Perfil', 'E-mail'], [
            ['Administrador', $admin->email],
            ['Vendedor', $seller->email],
            ['Comprador', $buyer->email],
        ]);
        $this->command?->line('Cartões de teste: 4111 1111 1111 1111 (aprovado) · 4000 0000 0000 0002 (recusado)');
    }

    /**
     * Remove images left over from a previous seed, but only when the catalog is empty
     * (e.g. after migrate:fresh), so real uploads are never touched.
     */
    private function resetProductImages(): void
    {
        if (Product::query()->doesntExist()) {
            Storage::disk('public')->deleteDirectory('products');
        }
    }
}
