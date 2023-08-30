@push('css_lib')
@include('layouts.datatables_css')
@endpush

{!! $dataTable->table(['width' => '100%', 'id' => 'tbl-order']) !!}

@push('scripts_lib')
@include('layouts.datatables_js')
{!! $dataTable->scripts() !!}
@endpush

@push('scripts_lib')
  <script>
      $(function (){

          var $table = $('#tbl-order');

          $table.on('preXhr.dt', function ( e, settings, data ) {
              data.filter = {
                  order_status_id: $('#order_status_id').val(),
                  method: $('#payment_method_id').val(),
                  delivery_type: $('#delivery_type_id').val(),
                  driver_id: $('#driver_id').val(),
                  market_id: $('#market_id').val(),
                  search: $('#search').val(),
                  start_date: $('#start_date').val(),
                  end_date: $('#end_date').val(),
              };
          });

          $('#form-filter-orders').submit(function(e) {
              e.preventDefault();
              $table.DataTable().draw();
          });

          $('#btn-clear').click(function() {

              $('#order_status_id').val('');
              $('#payment_method_id').val(''),
              $('#delivery_type_id').val(''),
              $('#driver_id').val(''),
              $('#market_id').val(''),
              $('#search').val(''),
              $('#start_date').val(''),
              $('#end_date').val(''),
              $table.DataTable().draw();
          });

      });
  </script>
@endpush
