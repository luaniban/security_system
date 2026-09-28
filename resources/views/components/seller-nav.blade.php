@php($toShip = auth()->user()->sales()->where('status', \App\Enums\OrderStatus::Paid)->count())

<x-section-nav label="Área do vendedor" :items="[
    ['route' => 'seller.products.index', 'label' => 'Meus produtos', 'active' => 'seller.products.*'],
    ['route' => 'seller.sales.index', 'label' => 'Vendas', 'count' => $toShip],
]" />
