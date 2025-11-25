<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ config('app.name', 'MediTrack') }}</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

  {{-- Custom CSS --}}
  <!-- <link rel="stylesheet" href="{{ asset('css/style.css') }}"> -->
</head>

<body>
  <main class="py-4">
    @yield('content')
  </main>

  <!-- jQuery FIRST -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <!-- DataTables JS -->
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

  <script>
    $(document).ready(function() {
      try {
        var tables = $('table.datatable');
        tables.each(function() {
          var $t = $(this);
          if ($.fn.DataTable.isDataTable(this)) return;
          $t.DataTable({
            responsive: true,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            searching: true,
            ordering: true,
            order: [],
          });
        });
      } catch (e) {
        console.error('DataTables init error', e);
      }
    });
  </script>

  {{-- Page-specific scripts: rendered after jQuery/DataTables libraries --}}
  @yield('scripts')

</body>
</html>
