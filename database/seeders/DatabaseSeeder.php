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

        // The demo seller owns every fifth product; the rest are spread across other
        // stores so the catalog shows several different "Vendido por".
        $sellers = [$seller, ...User::factory(4)->create()];

        // Each product gets its own stored copy even when two share a source file:
        // deleting a product also deletes its image, so a shared path would blank out
        // the image of every other product using it.
        foreach ($this->catalog() as $index => [$image, $name, $description, $priceCents, $stock]) {
            Product::factory()->for($sellers[$index % count($sellers)], 'seller')->create([
                'name' => $name,
                'description' => $description,
                'price_cents' => $priceCents,
                'stock' => $stock,
                'image_path' => $images->store(
                    new UploadedFile(database_path('seeders/images/'.$image), $image, 'image/jpeg', null, true)
                ),
                // Staggered so "Mais recentes" has a stable, meaningful order.
                'created_at' => now()->subHours($index * 5),
                'updated_at' => now()->subHours($index * 5),
            ]);
        }

        $this->command?->warn('Contas de demonstração (senha: '.$password.')');
        $this->command?->table(['Perfil', 'E-mail'], [
            ['Administrador', $admin->email],
            ['Vendedor', $seller->email],
            ['Comprador', $buyer->email],
        ]);
        $this->command?->line('Produtos no catálogo: '.count($this->catalog()));
        $this->command?->line('Cartões de teste: 4111 1111 1111 1111 (aprovado) · 4000 0000 0000 0002 (recusado)');
    }

    /**
     * Demo catalog: [image file, name, description, price in cents, stock].
     *
     * Prices straddle the free-shipping threshold and some stocks are <= 5, so the
     * "Frete grátis" and "Últimas unidades" badges both appear while browsing.
     *
     * @return list<array{0: string, 1: string, 2: string, 3: int, 4: int}>
     */
    private function catalog(): array
    {
        return [
            ['fone.jpg', 'Fone de ouvido Bluetooth', 'Fone sem fio com cancelamento de ruído e 30h de bateria.', 29990, 15],
            ['teclado.jpg', 'Teclado mecânico ABNT2', 'Switches blue, iluminação RGB e cabo destacável.', 34900, 8],
            ['mouse.jpg', 'Mouse sem fio', 'Sensor óptico de alta precisão, receptor USB nano e 2 anos de pilha.', 15990, 20],
            ['notebook.jpg', 'Notebook 14"', 'Processador de 8 núcleos, 16 GB de RAM e SSD de 512 GB.', 389900, 4],
            ['cadeira.jpg', 'Cadeira ergonômica', 'Apoio lombar ajustável, encosto em tela e braços 3D.', 124900, 3],
            ['webcam.jpg', 'Webcam Full HD', 'Webcam 1080p com microfone estéreo e correção automática de luz.', 19990, 12],
            ['smartphone.jpg', 'Smartphone 128 GB', 'Tela de 6,5", câmera tripla de 50 MP e bateria de 5000 mAh.', 149900, 10],
            ['smartwatch.jpg', 'Smartwatch esportivo', 'Monitor cardíaco, GPS integrado e resistência à água 5 ATM.', 89900, 6],
            ['caixa-de-som.jpg', 'Caixa de som Bluetooth', "Som 360°, à prova d'água IPX7 e 12h de reprodução.", 49990, 9],
            ['camera.jpg', 'Câmera DSLR', 'Sensor full-frame de 45 MP e gravação em 4K.', 1599900, 2],

            ['fone.jpg', 'Fone intra-auricular esportivo', 'Resistente a suor IPX5, encaixe com aletas e estojo de recarga.', 13990, 22],
            ['teclado.jpg', 'Teclado mecânico 60%', 'Layout compacto, switches red e estrutura em alumínio.', 42900, 4],
            ['mouse.jpg', 'Mouse gamer 16000 DPI', 'Sensor óptico ajustável, 7 botões programáveis e cabo trançado em náilon.', 22990, 13],
            ['notebook.jpg', 'Notebook 15,6" i5', 'SSD de 256 GB, 8 GB de RAM e tela antirreflexo Full HD.', 269900, 6],
            ['cadeira.jpg', 'Cadeira gamer reclinável', 'Reclinação de 180°, almofadas de pescoço e lombar e rodízios silenciosos.', 99900, 7],
            ['webcam.jpg', 'Webcam 4K com HDR', 'Campo de visão ajustável em 3 níveis e obturador de privacidade.', 79900, 4],
            ['smartphone.jpg', 'Smartphone 256 GB 5G', 'Chip 5G, carregamento de 67 W e tela AMOLED de 120 Hz.', 229900, 6],
            ['smartwatch.jpg', 'Smartwatch com tela AMOLED', 'Tela sempre ativa, 14 dias de bateria e mais de 100 modos de treino.', 54900, 11],
            ['caixa-de-som.jpg', 'Caixa de som portátil compacta', 'Cabe na mão, 8h de bateria e viva-voz integrado.', 17990, 25],
            ['camera.jpg', 'Câmera mirrorless APS-C', 'Sensor de 24 MP, tela articulada e lente 18-55 mm inclusa.', 699900, 3],

            ['fone.jpg', 'Headset gamer 7.1', 'Som surround virtual, microfone removível e almofadas em espuma viscoelástica.', 24990, 11],
            ['teclado.jpg', 'Teclado sem fio slim', 'Conexão Bluetooth para até 3 dispositivos e teclas de baixo perfil.', 18990, 18],
            ['mouse.jpg', 'Mouse ergonômico vertical', 'Pega vertical que reduz a torção do punho e apoio para o polegar.', 19990, 6],
            ['notebook.jpg', 'Notebook gamer 16"', 'Placa de vídeo dedicada de 8 GB, tela de 165 Hz e teclado RGB.', 749900, 2],
            ['cadeira.jpg', 'Cadeira de escritório giratória', 'Assento em espuma injetada, regulagem a gás e base de nylon.', 64900, 12],
            ['webcam.jpg', 'Webcam 720p compacta', 'Plug and play, clipe universal e microfone com redução de ruído.', 8990, 26],
            ['smartphone.jpg', 'Smartphone básico 64 GB', 'Tela de 6,1", bateria de 4000 mAh e leitor de digital lateral.', 79900, 18],
            ['smartwatch.jpg', 'Pulseira fitness', 'Contagem de passos, monitor de sono e notificações no pulso.', 19990, 22],
            ['caixa-de-som.jpg', 'Caixa de som para festa 200 W', 'Luzes sincronizadas, entrada para microfone e rodinhas para transporte.', 129900, 3],
            ['camera.jpg', 'Câmera de ação 4K', 'Estabilização eletrônica, caixa estanque e duas baterias.', 119900, 8],

            ['fone.jpg', 'Fone over-ear studio', 'Drivers de 50 mm, resposta plana e cabo espiralado de 3 m.', 45900, 5],
            ['teclado.jpg', 'Teclado gamer TKL', 'Sem bloco numérico, anti-ghosting e apoio de punho removível.', 27990, 9],
            ['mouse.jpg', 'Mouse silencioso Bluetooth', 'Cliques silenciosos, três níveis de DPI e modo de economia de bateria.', 11990, 28],
            ['notebook.jpg', 'Ultrabook 13" de 1,1 kg', 'Chassi em magnésio, 18h de bateria e leitor biométrico.', 529900, 3],
            ['cadeira.jpg', 'Cadeira presidente em couro sintético', 'Encosto alto, apoio de cabeça integrado e braços fixos.', 89900, 5],
            ['webcam.jpg', 'Webcam com ring light', 'Iluminação em três tons, tripé incluso e foco automático.', 24990, 10],
            ['smartphone.jpg', 'Capa antichoque transparente', 'Bordas reforçadas, proteção para a câmera e material que não amarela.', 3990, 45],
            ['smartwatch.jpg', 'Smartwatch infantil com GPS', 'Localização em tempo real, chamadas para contatos e botão SOS.', 39900, 5],
            ['caixa-de-som.jpg', 'Soundbar 2.1 com subwoofer', 'Subwoofer sem fio, HDMI ARC e modo cinema.', 99900, 5],
            ['camera.jpg', 'Câmera instantânea', 'Revelação em 90 s, flash automático e 10 filmes inclusos.', 64900, 10],

            ['fone.jpg', 'Fone com cancelamento ativo de ruído', 'ANC híbrido, modo transparência e carregamento por USB-C.', 59900, 7],
            ['teclado.jpg', 'Teclado numérico USB', 'Bloco numérico externo com 22 teclas e cabo de 1,5 m.', 6990, 30],
            ['mouse.jpg', 'Mouse trackball', 'Esfera de 34 mm, rolagem por inclinação e base antiderrapante.', 32900, 3],
            ['notebook.jpg', 'Suporte para notebook em alumínio', 'Altura ajustável em 6 níveis e ventilação aberta.', 13990, 24],
            ['cadeira.jpg', 'Banqueta ergonômica alta', 'Ideal para mesa em pé, aro para os pés e assento em tela.', 47900, 8],
            ['webcam.jpg', 'Microfone condensador USB', 'Cápsula cardioide, saída para fone e tripé de mesa.', 34900, 9],
            ['smartphone.jpg', 'Película de vidro temperado', 'Dureza 9H, aplicador incluso e sensibilidade ao toque preservada.', 2990, 60],
            ['smartwatch.jpg', 'Pulseira de silicone extra', 'Compatível com pinos de 22 mm, fecho reforçado e três cores.', 4990, 38],
            ['caixa-de-som.jpg', 'Par de monitores de estúdio 5"', 'Resposta plana, entradas XLR e P10 e ajuste de graves.', 189900, 2],
            ['camera.jpg', 'Tripé de alumínio de 1,7 m', 'Cabeça com nível bolha, travas rápidas e bolsa de transporte.', 21990, 15],

            ['fone.jpg', 'Fone de ouvido com fio P2', 'Cabo reforçado de 1,2 m, controle de volume em linha e microfone.', 4990, 40],
            ['teclado.jpg', 'Teclado retroiluminado de membrana', 'Iluminação em três níveis, teclas silenciosas e resistência a respingos.', 9990, 25],
            ['mouse.jpg', 'Mousepad gamer XL', 'Superfície de tecido 90x40 cm, base emborrachada e bordas costuradas.', 7990, 35],
            ['notebook.jpg', 'Base cooler para notebook', 'Cinco ventoinhas silenciosas, hub USB e inclinação regulável.', 16990, 14],
            ['cadeira.jpg', 'Apoio para os pés ajustável', 'Inclinação em três posições e superfície massageadora.', 12990, 20],
            ['webcam.jpg', 'Ring light de 26 cm com tripé', 'Tripé de até 2,1 m, suporte para celular e três temperaturas de cor.', 15990, 16],
            ['smartphone.jpg', 'Carregador USB-C de 30 W', 'Carga rápida PD, proteção contra sobretensão e cabo de 1 m.', 9990, 32],
            ['smartwatch.jpg', 'Balança digital com bioimpedância', 'Vidro temperado, app com histórico e até 8 perfis.', 17990, 14],
            ['caixa-de-som.jpg', 'Rádio retrô Bluetooth', 'FM digital, acabamento em madeira e entrada auxiliar.', 24990, 12],
            ['camera.jpg', 'Cartão de memória 128 GB UHS-I', 'Leitura de 170 MB/s, à prova de água e de raios X.', 12990, 30],
        ];
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
