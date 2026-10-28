<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Streamline — Streaming Service</title>
  @include('layouts.css')
</head>

<body>
<a class="skip-link" href="#main">Skip to content</a>

<!-- ========================= HEADER ========================= -->
@include('layouts.header')

<main id="main">

  @yield('contenido')

</main>

<!-- ========================= FOOTER ========================= -->
@include('layouts.footer')

<script src="js/vendor/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>
