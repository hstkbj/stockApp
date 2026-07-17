<!DOCTYPE html>
<html lang="Fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>StockApp</title>

        <!-- Favicon -->
		<link rel="shortcut icon" href="{{asset('assets/css/bootstrap-datetimepicker.min.css')}}">

		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="{{asset('assets/css/bootstrap.min.css')}}">

        <!-- Font family -->
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

		<!-- Fontawesome CSS -->
		<link rel="stylesheet" href="{{asset('assets/plugins/fontawesome/css/fontawesome.min.css')}}">
		<link rel="stylesheet" href="{{asset('assets/plugins/fontawesome/css/all.min.css')}}">

		<!-- Feather CSS -->
		<link rel="stylesheet" href="{{asset('assets/plugins/feather/feather.css')}}">

		<!-- Datepicker CSS -->
		<link rel="stylesheet" href="{{asset('assets/css/bootstrap-datetimepicker.min.css')}}">

		<!-- Datatables CSS -->
		<link rel="stylesheet" href="{{asset('assets/plugins/datatables/datatables.min.css')}}">

		<!-- Main CSS -->
		<link rel="stylesheet" href="{{asset('assets/css/style.css')}}">

		<!-- Layout JS -->
		<script src="{{asset('assets/js/layout.js')}}"></script>

        @vite(['resources/css/app.css','resources/js/app.js'])

    </head>
    <body>

        <div id="app"></div>

        <!-- jQuery -->
		<script src="{{asset('assets/js/jquery-3.7.1.min.js')}}"></script>

		<!-- Bootstrap Core JS -->
		<script src="{{asset('assets/js/bootstrap.bundle.min.js')}}"></script>

		<!-- Feather Icon JS -->
		<script src="{{asset('assets/js/feather.min.js')}}"></script>

		<!-- Slimscroll JS -->
		<script defer src="{{asset('assets/plugins/slimscroll/jquery.slimscroll.min.js')}}"></script>

		<!-- Chart JS -->
		{{-- <script src="{{asset('assets/plugins/apexchart/apexcharts.min.js')}}"></script>
		<script src="{{asset('assets/plugins/apexchart/chart-data.js')}}"></script> --}}

        <!-- script for tinymce plugin -->
        <script defer src="{{asset('assets/vendor/libs/tinymce/tinymce.bundle.js')}}"></script>

		<!-- Theme Settings JS -->
		<script src="{{asset('assets/js/theme-settings.js')}}"></script>
		<script src="{{asset('assets/js/greedynav.js')}}"></script>

		<!-- Custom JS -->
		<script src="{{asset('assets/js/script.js')}}"></script>

    </body>
</html>
