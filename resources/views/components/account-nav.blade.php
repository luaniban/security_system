<x-section-nav label="Minha conta" :items="[
    ['route' => 'profile.edit', 'label' => 'Dados pessoais'],
    ['route' => 'addresses.index', 'label' => 'Endereços'],
    ['route' => 'cards.index', 'label' => 'Cartões'],
    ['route' => 'orders.index', 'label' => 'Minhas compras', 'active' => 'orders.*'],
]" />
