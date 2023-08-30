@push('css_lib')
    @include('layouts.datatables_css')
@endpush

{!! $dataTable->table(['width' => '100%', 'id' => 'tbl-order']) !!}

@push('scripts_lib')
    @include('layouts.datatables_js')
    {!! $dataTable->scripts() !!}
@endpush