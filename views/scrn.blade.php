@include('header.header')
<div class="min-h-screen bg-gray-100">

    @include('nav.nav')
    <!-- Main Content Area -->
    <div class="pl-16 transition-all duration-300" id="mainContent">
        <!-- Top Bar -->
        @include('header.top')

        <!-- Main Content -->
        <main class="p-6">
            @isset($Page)
                @include($Page)
            @endisset
        </main>
    </div>
</div>

@if(session('download_file'))
<script>
    // Function to trigger download
    function triggerDownload(url, filename) {
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        setTimeout(() => {
            document.body.removeChild(link);
        }, 100);
    }

    // Trigger download when page loads
    window.addEventListener('DOMContentLoaded', function() {
        const downloadUrl = "{{ session('download_file') }}";
        const filename = "{{ session('download_filename') }}";
        console.log("Triggering download:", downloadUrl);

        // Small delay to ensure the page is fully loaded
        setTimeout(() => {
            triggerDownload(downloadUrl, filename);
        }, 1000);
    });
</script>
@endif


@include('footer.footer')
