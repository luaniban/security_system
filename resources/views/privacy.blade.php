<x-app-layout>
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 px-4">
        <x-card class="prose prose-sm max-w-none space-y-4">
            <h1 class="text-2xl font-semibold">Política de Privacidade</h1>
            <p class="text-sm text-gray-600">Sistema acadêmico desenvolvido para a disciplina de Segurança de Sistemas. Não utilize dados reais.</p>

            <h2 class="text-lg font-semibold">Quais dados coletamos</h2>
            <ul class="list-disc pl-5 text-sm text-gray-700">
                <li>Nome, e-mail, CPF e telefone, para identificação e emissão de pedidos.</li>
                <li>Endereços de entrega, para o envio dos produtos.</li>
                <li>Cartões de crédito (número e validade), para pagamento. O CVV nunca é armazenado.</li>
                <li>Registros de acesso (IP, navegador e data) para segurança, conforme o Marco Civil da Internet.</li>
            </ul>

            <h2 class="text-lg font-semibold">Como protegemos</h2>
            <ul class="list-disc pl-5 text-sm text-gray-700">
                <li>CPF, telefone, endereços e cartões são criptografados no banco de dados (AES-256).</li>
                <li>Senhas são armazenadas apenas como hash bcrypt.</li>
                <li>Dados sensíveis são exibidos mascarados, inclusive para administradores.</li>
                <li>O vendedor só vê o nome e o endereço de entrega do comprador, nada além disso.</li>
            </ul>

            <h2 class="text-lg font-semibold">Seus direitos (LGPD, art. 18)</h2>
            <p class="text-sm text-gray-700">Você pode consultar e corrigir seus dados no seu perfil e excluir sua conta a qualquer momento.</p>
        </x-card>
    </div>
</x-app-layout>
