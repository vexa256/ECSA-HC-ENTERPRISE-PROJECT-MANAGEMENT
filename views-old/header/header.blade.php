<!-- CSS files -->
<link href="{{ asset('dist/css/tabler.min.css?1738096685') }}" rel="stylesheet" />
<link href="{{ asset('dist/css/tabler-flags.min.css?1738096685') }}" rel="stylesheet" />
<link href="{{ asset('dist/css/tabler-socials.min.css?1738096685') }}" rel="stylesheet" />
<link href="{{ asset('dist/css/tabler-payments.min.css?1738096685') }}" rel="stylesheet" />
<link href="{{ asset('dist/css/tabler-vendors.min.css?1738096685') }}" rel="stylesheet" />
<link href="{{ asset('dist/css/tabler-marketing.min.css?1738096685') }}" rel="stylesheet" />
<link href="{{ asset('dist/css/demo.min.css?1738096685') }}" rel="stylesheet" />
<style>
    @import url('https://rsms.me/inter/inter.css');
</style>
<!-- Fonts -->
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

<!-- Scripts -->
{{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>



<link
    href="https://cdn.datatables.net/v/dt/jq-3.7.0/jszip-3.10.1/dt-2.2.1/b-3.2.1/b-colvis-3.2.1/b-html5-3.2.1/b-print-3.2.1/r-3.0.3/datatables.min.css"
    rel="stylesheet">
<script src="{{ asset('dist/libs/tom-select/dist/js/tom-select.base.min.js?1738096684') }}" defer></script>


<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>


<script src="
https://cdn.jsdelivr.net/npm/sweetalert2@11.16.0/dist/sweetalert2.all.min.js
"></script>
<link href="
https://cdn.jsdelivr.net/npm/sweetalert2@11.16.0/dist/sweetalert2.min.css
" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />

<style>
    .animated-background {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -1;
        background: linear-gradient(45deg, #f3f3f3, #e9ecef);
        background-size: 400% 400%;
        animation: gradientBG 15s ease infinite;
    }

    @keyframes gradientBG {
        0% {
            background-position: 0% 50%;
        }

        50% {
            background-position: 100% 50%;
        }

        100% {
            background-position: 0% 50%;
        }
    }

    .form-floating-custom {
        position: relative;
    }

    .form-floating-icon {
        position: absolute;
        top: 50%;
        right: 10px;
        transform: translateY(-50%);
        color: #6c757d;
    }

    .btn-pill {
        border-radius: 50px;
    }

    .report-selection-form {
        transition: all 0.3s ease;
    }

    .report-selection-form:hover {
        transform: translateY(-5px);
    }

    .card {
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, .175) !important;
    }
</style>
<style>
    .dt-info {

        display: none !important;
    }

    .ts-wrapper {

        z-index: 100000 !important;
    }

    .ts-dropdown {
        z-index: 9999 !important;
    }
</style>

</head>

<body class=" layout-fluid">

    <script src="{{ asset('dist/js/demo-theme.min.js?1738096685') }}"></script>
