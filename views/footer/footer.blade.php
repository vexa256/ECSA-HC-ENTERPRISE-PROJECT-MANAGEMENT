<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarItems = document.querySelectorAll('.sidebar-item');
        const slidePanel = document.getElementById('slidePanel');
        const mainContent = document.getElementById('mainContent');
        const panelSections = document.querySelectorAll('.panel-section');
        let currentSection = null;

        sidebarItems.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const section = this.getAttribute('data-section');

                if (currentSection === section) {
                    // Close the panel if clicking the same item
                    slidePanel.classList.remove('open');
                    currentSection = null;
                } else {
                    // Open the panel and show the correct section
                    slidePanel.classList.add('open');
                    currentSection = section;

                    panelSections.forEach(panelSection => {
                        if (panelSection.getAttribute('data-section') === section) {
                            panelSection.classList.remove('hidden');
                        } else {
                            panelSection.classList.add('hidden');
                        }
                    });
                }
            });
        });

        // Close the panel when clicking outside
        document.addEventListener('click', function(e) {
            if (!slidePanel.contains(e.target) && !e.target.closest('.sidebar-item')) {
                slidePanel.classList.remove('open');
                currentSection = null;
            }
        });

        // Toggle user dropdown
        const userButton = document.getElementById('userButton');
        const userDropdown = document.getElementById('userDropdown');
        userButton.addEventListener('click', function() {
            userDropdown.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!userButton.contains(e.target) && !userDropdown.contains(e.target)) {
                userDropdown.classList.add('hidden');
            }
        });
    });
</script>

{{-- resources/views/components/dialogs.blade.php --}}

{{-- Success Dialog --}}
{{-- @if (session('status'))
    <div id="successDialog" class="modal modal-bottom sm:modal-middle">
        <div class="modal-box bg-white">
            <h3 class="font-bold text-lg text-success">Success!</h3>
            <p class="py-4">{{ session('status') }}</p>
            <div class="modal-action">
                <button class="btn btn-success" onclick="closeDialog('successDialog')">Close</button>
            </div>
        </div>
    </div>
@endif --}}

{{-- Error Dialog --}}
{{-- @if (session('error'))
    <div id="errorDialog" class="modal modal-bottom sm:modal-middle">
        <div class="modal-box bg-white">
            <h3 class="font-bold text-lg text-error">Error!</h3>
            <p class="py-4">{{ session('error') }}</p>
            <div class="modal-action">
                <button class="btn btn-error" onclick="closeDialog('errorDialog')">Close</button>
            </div>
        </div>
    </div>
@endif --}}

{{-- Validation Error Dialog --}}
{{-- @if ($errors->any())
    <div id="validationErrorDialog" class="modal modal-bottom sm:modal-middle">
        <div class="modal-box bg-white">
            <h3 class="font-bold text-lg text-error">Validation Error!</h3>
            <ul class="py-4 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <div class="modal-action">
                <button class="btn btn-error" onclick="closeDialog('validationErrorDialog')">Close</button>
            </div>
        </div>
    </div>
@endif --}}

{{-- Table Columns Dialog --}}
{{-- @if (session('columns'))
    <div id="columnsDialog" class="modal modal-bottom sm:modal-middle">
        <div class="modal-box bg-white">
            <h3 class="font-bold text-lg text-info">Table Columns</h3>
            <ul class="py-4 list-disc list-inside">
                @foreach (session('columns') as $column)
                    <li>{{ $column->Field }}</li>
                @endforeach
            </ul>
            <div class="modal-action">
                <button class="btn btn-info" onclick="closeDialog('columnsDialog')">Close</button>
            </div>
        </div>
    </div>
@endif --}}

{{-- <script>
    function showDialog(id) {
        document.getElementById(id).classList.add('modal-open');
    }

    function closeDialog(id) {
        document.getElementById(id).classList.remove('modal-open');
    }

    document.addEventListener('DOMContentLoaded', (event) => {
        if (document.getElementById('successDialog')) showDialog('successDialog');
        if (document.getElementById('errorDialog')) showDialog('errorDialog');
        if (document.getElementById('validationErrorDialog')) showDialog('validationErrorDialog');
        if (document.getElementById('columnsDialog')) showDialog('columnsDialog');
    });
</script> --}}


</body>

</html>
