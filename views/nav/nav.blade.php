@include('nav.style')

<!-- Sidebar -->
<aside class="fixed top-0 left-0 z-40 h-screen w-16 bg-base-100 border-r border-base-200 flex flex-col shadow-sm">
    <!-- Logo -->
    <div class="h-16 flex-shrink-0 flex items-center justify-center border-b border-base-200">
        <img src="https://api.dicebear.com/7.x/shapes/svg?seed=enterprise" alt="Logo" class="w-8 h-8" />
    </div>

   @include('nav.primarynav')
</aside>

<!-- Enhanced Slide-out Panel with iOS-style blur effect -->
<div id="slidePanel" class="slide-panel">
    <div class="slide-panel-content bg-base-100 bg-opacity-80">
        <!-- Home Section -->
       @include('nav.sechome')

        <!-- Org Section -->
      @include('nav.secorg')

        <!-- Metrics Section -->
      @include('nav.secmetrics')

        <!-- Reports Section -->
     @include('nav.secreports')
        <!-- HR Section -->
     @include('nav.sechr')
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sidebar toggle functionality with haptic feedback simulation
        const sidebarItems = document.querySelectorAll('.sidebar-item');
        const slidePanel = document.getElementById('slidePanel');
        const panelSections = document.querySelectorAll('.panel-section');

        let activeSection = null;

        // Function to simulate haptic feedback
        function simulateHapticFeedback() {
            if (window.navigator && window.navigator.vibrate) {
                navigator.vibrate(5); // Light tap feeling - 5ms vibration
            }
        }

        sidebarItems.forEach(item => {
            item.addEventListener('click', function(e) {
                if (this.getAttribute('href') === '#') {
                    e.preventDefault();
                }

                simulateHapticFeedback();

                const section = this.getAttribute('data-section');

                if (activeSection === section) {
                    // Toggle off if clicking the same section
                    slidePanel.classList.remove('active');
                    activeSection = null;
                } else {
                    // Show the panel and activate the correct section
                    slidePanel.classList.add('active');

                    // Hide all sections first
                    panelSections.forEach(panel => {
                        panel.classList.add('hidden');
                    });

                    // Show the selected section
                    document.querySelector(`.panel-section[data-section="${section}"]`)
                        .classList.remove('hidden');

                    activeSection = section;
                }
            });
        });

        // Close panel when clicking outside
        document.addEventListener('click', function(e) {
            if (slidePanel.classList.contains('active') &&
                !slidePanel.contains(e.target) &&
                !Array.from(sidebarItems).some(item => item.contains(e.target))) {
                slidePanel.classList.remove('active');
                activeSection = null;
            }
        });

        // Add iOS-style smooth animations for panel transitions
        slidePanel.addEventListener('transitionstart', function() {
            this.classList.add('animating');
        });

        slidePanel.addEventListener('transitionend', function() {
            this.classList.remove('animating');
        });
    });
</script>
