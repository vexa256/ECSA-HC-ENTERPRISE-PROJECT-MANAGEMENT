<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ECSA-HC Reporting Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script> --}}


    <script src="https://cdnjs.cloudflare.com/ajax/libs/iconify/2.0.0/iconify.min.js"></script>


    {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap'); */

        body {
            font-family: 'Inter', sans-serif;
        }

        /* Enhanced Slide-out Panel Styles */
        .slide-panel {
            position: fixed;
            top: 0;
            left: 4rem;
            height: 100vh;
            width: 0;
            background: white;
            transition: all 0.3s ease-in-out;
            overflow: hidden;
            z-index: 1000;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.1);
        }

        .slide-panel.open {
            width: 400px;
        }

        .slide-panel-content {
            width: 400px;
            height: 100%;
            padding: 2rem;
            overflow-y: auto;
        }

        .shortcut-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .shortcut-item {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            border-radius: 0.5rem;
            background-color: #f3f4f6;
            transition: all 0.2s ease;
        }

        .shortcut-item:hover {
            background-color: #e5e7eb;
        }

        .link-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-top: 2rem;
        }

        .link-item {
            padding: 1rem;
            border-radius: 0.5rem;
            background-color: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: all 0.2s ease;
        }

        .link-item:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
    </style>


    <style>
        /* Targeting the scrollbar for WebKit browsers (Chrome, Safari, newer versions of Edge) */
        ::-webkit-scrollbar {
            width: 8px;
            /* for vertical scrollbars */
            height: 8px;
            /* for horizontal scrollbars */
        }

        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            transition: background 0.2s ease;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.4);
        }

        /* For Firefox */
        * {
            scrollbar-width: thin;
            scrollbar-color: rgba(0, 0, 0, 0.2) rgba(0, 0, 0, 0.05);
        }

        /* For Internet Explorer */
        body {
            -ms-overflow-style: -ms-autohiding-scrollbar;
        }

        /* Optional: Hide scrollbar when not scrolling (iOS-like behavior) */
        ::-webkit-scrollbar {
            display: none;
        }

        *:hover::-webkit-scrollbar {
            display: block;
        }

        /* Ensure the page itself is scrollable */
        html,
        body {
            overflow-y: auto;
        }
    </style>



</head>

<body>
