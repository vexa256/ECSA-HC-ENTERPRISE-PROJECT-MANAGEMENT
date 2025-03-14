<!-- resources/views/mpaRRF/manage-rrf-indicators.blade.php -->

<div class="w-full p-4">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row items-start md:items-center gap-2 mb-4">
        <div>
            <h2 class="text-xl font-bold">
                {{ $SelectedEntity->Entity }} Indicators Management
            </h2>
            <p class="text-gray-500 mt-1">
                Manage and track {{ $SelectedEntity->Entity }} indicators.
            </p>
        </div>
        <!-- "Add Indicator" Buttons: Desktop & Mobile -->
        <div class="ml-auto flex items-center gap-2">
            <!-- Desktop Button -->
            <label for="addRrfIndicatorModal" class="btn btn-active hidden md:inline-flex">
                <span class="iconify mr-2" data-icon="mdi:plus" data-inline="true"></span>
                Add New {{ $SelectedEntity->Entity }} Indicator
            </label>
            <!-- Mobile Button -->
            <label for="addRrfIndicatorModal" class="btn btn-active md:hidden btn-square"
                aria-label="Add New {{ $SelectedEntity->Entity }} Indicator">
                <span class="iconify" data-icon="mdi:plus" data-inline="true"></span>
            </label>
        </div>
    </div>

    <!-- Alerts -->
    @if (session('success'))
        <div class="alert alert-success mb-4">
            <div class="flex-1">
                <span class="iconify mr-2" data-icon="mdi:check-circle-outline"></span>
                {{ session('success') }}
            </div>
            <button class="btn btn-neutral btn-sm ml-2" onclick="this.parentElement.remove()">Close</button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-error mb-4">
            <div class="flex-1">
                <span class="iconify mr-2" data-icon="mdi:alert-circle-outline"></span>
                {{ session('error') }}
            </div>
            <button class="btn btn-neutral btn-sm ml-2" onclick="this.parentElement.remove()">Close</button>
        </div>
    @endif

    <!-- Main Card -->
    <div class="card bg-base-100 shadow-xl w-full">
        <!-- Card Header -->
        <div class="card-body border-b pb-4">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-2">
                <h3 class="text-lg font-semibold">
                    {{ $SelectedEntity->Entity }} Indicators
                </h3>
                <!-- Search field -->
                <div class="w-full md:w-auto">
                    <input type="text" class="input input-bordered w-full md:w-60" placeholder="Search indicators..."
                        id="indicatorSearch">
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="overflow-x-auto">
            <table class="table w-full" id="indicatorsTable">
                <thead>
                    <tr>
                        <th>Indicator</th>
                        <th>Category</th>
                        <th>Reporting Period</th>
                        <th>Baseline 2024</th>
                        <th>Target 2030</th>
                        <th class="w-1">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($indicators as $indicator)
                        <tr>
                            <td>{{ $indicator->Indicator }}</td>
                            <td>{{ $indicator->SecondaryCategory }}</td>
                            <td>{{ $indicator->ReportingPeriod }}</td>
                            <td>{{ $indicator->Baseline2024 }}</td>
                            <td>{{ $indicator->TargetYearSeven2030 }}</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <!-- Edit -->
                                    <label for="editRrfIndicatorModal-{{ $indicator->id }}"
                                        class="btn btn-active btn-square" title="Edit">
                                        <span class="iconify" data-icon="mdi:pencil" data-inline="true"></span>
                                    </label>
                                    <!-- View Details -->
                                    <label for="viewMoreRrfModal-{{ $indicator->id }}"
                                        class="btn btn-neutral btn-square" title="View Details">
                                        <span class="iconify" data-icon="mdi:eye-outline" data-inline="true"></span>
                                    </label>
                                    <!-- Delete -->
                                    <form id="delete-form-{{ $indicator->id }}"
                                        action="{{ route('mpaRRF.DeleteRRFIndicator') }}" method="POST"
                                        class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="id" value="{{ $indicator->id }}">
                                        <button type="button" class="btn btn-outline btn-square"
                                            onclick="confirmDelete('{{ $indicator->id }}')" title="Delete">
                                            <span class="iconify" data-icon="mdi:trash-can-outline"
                                                data-inline="true"></span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <!-- No indicators found -->
                        <tr>
                            <td colspan="6" class="text-center py-6">
                                <div class="flex flex-col items-center gap-2">
                                    <img src="{{ asset('static/illustrations/undraw_printing_invoices_5r4r.svg') }}"
                                        alt="No indicators" class="w-32 h-auto">
                                    <p class="text-xl">No indicators found</p>
                                    <p class="text-gray-500">
                                        Start by adding a new {{ $SelectedEntity->Entity }} indicator using the button
                                        above.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ================================ -->
<!-- ADD INDICATOR MODAL (DaisyUI) -->
<!-- ================================ -->
<input type="checkbox" id="addRrfIndicatorModal" class="modal-toggle" />
<div class="modal">
    <div class="modal-box relative w-full max-w-6xl">
        <!-- Close Button -->
        <label for="addRrfIndicatorModal" class="btn btn-sm btn-circle absolute right-2 top-2">
            ✕
        </label>

        <h3 class="text-lg font-bold mb-4">
            Add New {{ $SelectedEntity->Entity }} Indicator
        </h3>

        <form action="{{ route('mpaRRF.StoreRRFIndicator') }}" method="POST" id="addRrfIndicatorForm">
            @csrf
            <input type="hidden" name="EntityID" value="{{ $SelectedEntity->EntityID }}">
            <input type="hidden" name="IID" value="{{ md5(uniqid(now(), true)) }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <!-- Primary Category -->
                <div>
                    <label class="label font-semibold">Primary Category</label>
                    <input type="text" class="input input-bordered w-full" name="IndicatorPrimaryCategory"
                        value="{{ $SelectedEntity->EntityID }}" required readonly>
                </div>
                <!-- Secondary Category -->
                <div>
                    <label class="label font-semibold">Secondary Category</label>
                    <select class="select select-bordered w-full" name="IndicatorSecondaryCategory" required>
                        <option value="" disabled selected>Select Secondary Category</option>
                        <option value="{{ $SelectedEntity->EntityID }} PDO">
                            {{ $SelectedEntity->EntityID }} Project Development Objective (PDO) indicators
                        </option>
                        <option value="{{ $SelectedEntity->EntityID }} Intermediate">
                            {{ $SelectedEntity->EntityID }} Intermediate Results Indicators (IRI)
                        </option>
                    </select>
                </div>
                <!-- Indicator -->
                <div class="md:col-span-2">
                    <label class="label font-semibold">Indicator</label>
                    <input type="text" class="input input-bordered w-full" name="Indicator" required>
                </div>
                <!-- Indicator Definition -->
                <div>
                    <label class="label font-semibold">Indicator Definition</label>
                    <textarea class="textarea textarea-bordered w-full" name="IndicatorDefinition" rows="3"></textarea>
                </div>
                <!-- Indicator Question -->
                <div>
                    <label class="label font-semibold">Indicator Question</label>
                    <textarea class="textarea textarea-bordered w-full" name="IndicatorQuestion" rows="3"></textarea>
                </div>
                <!-- Source of Data -->
                <div>
                    <label class="label font-semibold">Source of Data</label>
                    <input type="text" class="input input-bordered w-full" name="SourceOfData">
                </div>
                <!-- Response Type -->
                <div>
                    <label class="label font-semibold">Response Type</label>
                    <select class="select select-bordered w-full" name="ResponseType" required>
                        <option value="" disabled selected>Select Type</option>
                        <option value="Text">Text</option>
                        <option value="Number">Number</option>
                        <option value="Boolean">Boolean</option>
                        <option value="Percentage">Percentage</option>
                        <option value="Yes/No">Yes/No</option>
                    </select>
                </div>
                <!-- Reporting Period -->
                <div>
                    <label class="label font-semibold">Reporting Period</label>
                    <input type="text" class="input input-bordered w-full" name="ReportingPeriod"
                        placeholder="e.g., 2023-2030">
                </div>
                <!-- Expected Target -->
                <div>
                    <label class="label font-semibold">Expected Target</label>
                    <input type="text" class="input input-bordered w-full" name="ExpectedTarget">
                </div>
            </div>

            <!-- Yearly Targets -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="label font-semibold">Baseline PAD 2023</label>
                    <input type="text" class="input input-bordered w-full" name="BaselinePAD2023">
                </div>
                <div>
                    <label class="label font-semibold">Baseline 2024</label>
                    <input type="text" class="input input-bordered w-full" name="Baseline2024">
                </div>
                <div>
                    <label class="label font-semibold">Target 2024 (Year 1)</label>
                    <input type="text" class="input input-bordered w-full" name="TargetYearOne2024">
                </div>
                <div>
                    <label class="label font-semibold">Target 2025 (Year 2)</label>
                    <input type="text" class="input input-bordered w-full" name="TargetYearTwo2025">
                </div>
                <div>
                    <label class="label font-semibold">Target 2026 (Year 3)</label>
                    <input type="text" class="input input-bordered w-full" name="TargetYearThree2026">
                </div>
                <div>
                    <label class="label font-semibold">Target 2027 (Year 4)</label>
                    <input type="text" class="input input-bordered w-full" name="TargetYearFour2027">
                </div>
                <div>
                    <label class="label font-semibold">Target 2028 (Year 5)</label>
                    <input type="text" class="input input-bordered w-full" name="TargetYearFive2028">
                </div>
                <div>
                    <label class="label font-semibold">Target 2029 (Year 6)</label>
                    <input type="text" class="input input-bordered w-full" name="TargetYearSix2029">
                </div>
                <div>
                    <label class="label font-semibold">Target 2030 (Year 7)</label>
                    <input type="text" class="input input-bordered w-full" name="TargetYearSeven2030">
                </div>
            </div>

            <div class="mb-4">
                <label class="label font-semibold">Remarks / Comments</label>
                <textarea class="textarea textarea-bordered w-full" name="RemarksComments" rows="3"></textarea>
            </div>

            <!-- Modal Footer -->
            <div class="modal-action">
                <!-- Cancel -->
                <label for="addRrfIndicatorModal" class="btn btn-neutral">
                    Cancel
                </label>
                <!-- Save -->
                <button type="submit" class="btn btn-active">
                    <span class="iconify mr-2" data-icon="mdi:content-save" data-inline="true"></span>
                    Save Indicator
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================================ -->
<!-- EDIT INDICATOR MODALS (DaisyUI) -->
<!-- ================================ -->
@foreach ($indicators as $indicator)
    <input type="checkbox" id="editRrfIndicatorModal-{{ $indicator->id }}" class="modal-toggle" />
    <div class="modal">
        <div class="modal-box relative w-full max-w-6xl">
            <!-- Close Button -->
            <label for="editRrfIndicatorModal-{{ $indicator->id }}"
                class="btn btn-sm btn-circle absolute right-2 top-2">
                ✕
            </label>
            <h3 class="text-lg font-bold mb-4">
                Edit {{ $SelectedEntity->Entity }} Indicator (ID: {{ $indicator->id }})
            </h3>

            <form action="{{ route('mpaRRF.UpdateRRFIndicator') }}" method="POST"
                id="editRrfIndicatorForm-{{ $indicator->id }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" value="{{ $indicator->id }}">
                <input type="hidden" name="EntityID" value="{{ $SelectedEntity->EntityID }}">
                <input type="hidden" name="IID" value="{{ $indicator->IID }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <!-- Primary Category -->
                    <div>
                        <label class="label font-semibold">Primary Category</label>
                        <input type="text" class="input input-bordered w-full" name="IndicatorPrimaryCategory"
                            value="{{ $indicator->PrimaryCategory }}" required readonly>
                    </div>
                    <!-- Secondary Category -->
                    <div>
                        <label class="label font-semibold">Secondary Category</label>
                        <select class="select select-bordered w-full" name="IndicatorSecondaryCategory" required>
                            <option value="{{ $SelectedEntity->EntityID }} PDO"
                                @if ($indicator->SecondaryCategory === $SelectedEntity->EntityID . ' PDO') selected @endif>
                                {{ $SelectedEntity->EntityID }} Project Development Objective (PDO) indicators
                            </option>
                            <option value="{{ $SelectedEntity->EntityID }} Intermediate"
                                @if ($indicator->SecondaryCategory === $SelectedEntity->EntityID . ' Intermediate') selected @endif>
                                {{ $SelectedEntity->EntityID }} Intermediate Results Indicators (IRI)
                            </option>
                        </select>
                    </div>
                    <!-- Indicator -->
                    <div class="md:col-span-2">
                        <label class="label font-semibold">Indicator</label>
                        <input type="text" class="input input-bordered w-full" name="Indicator"
                            value="{{ $indicator->Indicator }}" required>
                    </div>
                    <!-- Indicator Definition -->
                    <div>
                        <label class="label font-semibold">Indicator Definition</label>
                        <textarea class="textarea textarea-bordered w-full" name="IndicatorDefinition" rows="3">{{ $indicator->IndicatorDefinition }}</textarea>
                    </div>
                    <!-- Indicator Question -->
                    <div>
                        <label class="label font-semibold">Indicator Question</label>
                        <textarea class="textarea textarea-bordered w-full" name="IndicatorQuestion" rows="3">{{ $indicator->IndicatorQuestion }}</textarea>
                    </div>
                    <!-- Source of Data -->
                    <div>
                        <label class="label font-semibold">Source of Data</label>
                        <input type="text" class="input input-bordered w-full" name="SourceOfData"
                            value="{{ $indicator->SourceOfData }}">
                    </div>
                    <!-- Response Type -->
                    <div>
                        <label class="label font-semibold">Response Type</label>
                        <select class="select select-bordered w-full" name="ResponseType" required>
                            <option value="Text" @if ($indicator->ResponseType === 'Text') selected @endif>Text</option>
                            <option value="Number" @if ($indicator->ResponseType === 'Number') selected @endif>Number</option>
                            <option value="Boolean" @if ($indicator->ResponseType === 'Boolean') selected @endif>Boolean</option>
                            <option value="Percentage" @if ($indicator->ResponseType === 'Percentage') selected @endif>Percentage
                            </option>
                            <option value="Yes/No" @if ($indicator->ResponseType === 'Yes/No') selected @endif>Yes/No</option>
                        </select>
                    </div>
                    <!-- Reporting Period -->
                    <div>
                        <label class="label font-semibold">Reporting Period</label>
                        <input type="text" class="input input-bordered w-full" name="ReportingPeriod"
                            value="{{ $indicator->ReportingPeriod }}">
                    </div>
                    <!-- Expected Target -->
                    <div>
                        <label class="label font-semibold">Expected Target</label>
                        <input type="text" class="input input-bordered w-full" name="ExpectedTarget"
                            value="{{ $indicator->ExpectedTarget }}">
                    </div>
                </div>

                <!-- Yearly Targets -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="label font-semibold">Baseline PAD 2023</label>
                        <input type="text" class="input input-bordered w-full" name="BaselinePAD2023"
                            value="{{ $indicator->BaselinePAD2023 }}">
                    </div>
                    <div>
                        <label class="label font-semibold">Baseline 2024</label>
                        <input type="text" class="input input-bordered w-full" name="Baseline2024"
                            value="{{ $indicator->Baseline2024 }}">
                    </div>
                    <div>
                        <label class="label font-semibold">Target 2024 (Year 1)</label>
                        <input type="text" class="input input-bordered w-full" name="TargetYearOne2024"
                            value="{{ $indicator->TargetYearOne2024 }}">
                    </div>
                    <div>
                        <label class="label font-semibold">Target 2025 (Year 2)</label>
                        <input type="text" class="input input-bordered w-full" name="TargetYearTwo2025"
                            value="{{ $indicator->TargetYearTwo2025 }}">
                    </div>
                    <div>
                        <label class="label font-semibold">Target 2026 (Year 3)</label>
                        <input type="text" class="input input-bordered w-full" name="TargetYearThree2026"
                            value="{{ $indicator->TargetYearThree2026 }}">
                    </div>
                    <div>
                        <label class="label font-semibold">Target 2027 (Year 4)</label>
                        <input type="text" class="input input-bordered w-full" name="TargetYearFour2027"
                            value="{{ $indicator->TargetYearFour2027 }}">
                    </div>
                    <div>
                        <label class="label font-semibold">Target 2028 (Year 5)</label>
                        <input type="text" class="input input-bordered w-full" name="TargetYearFive2028"
                            value="{{ $indicator->TargetYearFive2028 }}">
                    </div>
                    <div>
                        <label class="label font-semibold">Target 2029 (Year 6)</label>
                        <input type="text" class="input input-bordered w-full" name="TargetYearSix2029"
                            value="{{ $indicator->TargetYearSix2029 }}">
                    </div>
                    <div>
                        <label class="label font-semibold">Target 2030 (Year 7)</label>
                        <input type="text" class="input input-bordered w-full" name="TargetYearSeven2030"
                            value="{{ $indicator->TargetYearSeven2030 }}">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="label font-semibold">Remarks / Comments</label>
                    <textarea class="textarea textarea-bordered w-full" name="RemarksComments" rows="3">{{ $indicator->RemarksComments }}</textarea>
                </div>

                <!-- Modal Footer -->
                <div class="modal-action">
                    <!-- Cancel -->
                    <label for="editRrfIndicatorModal-{{ $indicator->id }}" class="btn btn-neutral">
                        Cancel
                    </label>
                    <!-- Update Indicator -->
                    <button type="submit" class="btn btn-active">
                        <span class="iconify mr-2" data-icon="mdi:content-save" data-inline="true"></span>
                        Update Indicator
                    </button>
                </div>
            </form>
        </div>
    </div>
@endforeach


<!-- ============================== -->
<!-- VIEW MORE RRF MODALS (DaisyUI) -->
<!-- ============================== -->
@foreach ($indicators as $indicator)
    <input type="checkbox" id="viewMoreRrfModal-{{ $indicator->id }}" class="modal-toggle" />
    <div class="modal">
        <div class="modal-box relative w-full max-w-6xl">
            <!-- Close Button -->
            <label for="viewMoreRrfModal-{{ $indicator->id }}" class="btn btn-sm btn-circle absolute right-2 top-2">
                ✕
            </label>
            <h5 class="text-lg font-bold mb-4">
                {{ $SelectedEntity->Entity }} Indicator Full Details (ID: {{ $indicator->id }})
            </h5>

            <div class="overflow-x-auto">
                <!-- Two-column tables (Definition, SourceOfData, etc.) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <table class="table w-full border">
                        <tbody>
                            <tr>
                                <th>Primary Category</th>
                                <td>{{ $indicator->PrimaryCategory }}</td>
                            </tr>
                            <tr>
                                <th>Secondary Category</th>
                                <td>{{ $indicator->SecondaryCategory }}</td>
                            </tr>
                            <tr>
                                <th>Indicator</th>
                                <td>{{ $indicator->Indicator }}</td>
                            </tr>
                            <tr>
                                <th>Definition</th>
                                <td>{{ $indicator->IndicatorDefinition }}</td>
                            </tr>
                            <tr>
                                <th>Question</th>
                                <td>{{ $indicator->IndicatorQuestion }}</td>
                            </tr>
                            <tr>
                                <th>Remarks / Comments</th>
                                <td>{{ $indicator->RemarksComments }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <table class="table w-full border">
                        <tbody>
                            <tr>
                                <th>Source Of Data</th>
                                <td>{{ $indicator->SourceOfData }}</td>
                            </tr>
                            <tr>
                                <th>Response Type</th>
                                <td>{{ $indicator->ResponseType }}</td>
                            </tr>
                            <tr>
                                <th>Reporting Period</th>
                                <td>{{ $indicator->ReportingPeriod }}</td>
                            </tr>
                            <tr>
                                <th>Expected Target</th>
                                <td>{{ $indicator->ExpectedTarget }}</td>
                            </tr>
                            <tr>
                                <th>Baseline PAD 2023</th>
                                <td>{{ $indicator->BaselinePAD2023 }}</td>
                            </tr>
                            <tr>
                                <th>Baseline 2024</th>
                                <td>{{ $indicator->Baseline2024 }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Yearly Targets -->
                <div class="mb-4">
                    <h4 class="font-semibold mb-2">Yearly Targets</h4>
                    <table class="table w-full border">
                        <thead>
                            <tr>
                                <th>Year</th>
                                <th>Target</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>2024 (Year 1)</td>
                                <td>{{ $indicator->TargetYearOne2024 }}</td>
                            </tr>
                            <tr>
                                <td>2025 (Year 2)</td>
                                <td>{{ $indicator->TargetYearTwo2025 }}</td>
                            </tr>
                            <tr>
                                <td>2026 (Year 3)</td>
                                <td>{{ $indicator->TargetYearThree2026 }}</td>
                            </tr>
                            <tr>
                                <td>2027 (Year 4)</td>
                                <td>{{ $indicator->TargetYearFour2027 }}</td>
                            </tr>
                            <tr>
                                <td>2028 (Year 5)</td>
                                <td>{{ $indicator->TargetYearFive2028 }}</td>
                            </tr>
                            <tr>
                                <td>2029 (Year 6)</td>
                                <td>{{ $indicator->TargetYearSix2029 }}</td>
                            </tr>
                            <tr>
                                <td>2030 (Year 7)</td>
                                <td>{{ $indicator->TargetYearSeven2030 }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Timestamps -->
                <div>
                    <h4 class="font-semibold mb-2">Timestamps</h4>
                    <table class="table w-full border">
                        <tbody>
                            <tr>
                                <th>Created At</th>
                                <td>{{ $indicator->created_at }}</td>
                            </tr>
                            <tr>
                                <th>Updated At</th>
                                <td>{{ $indicator->updated_at }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-action">
                <label for="viewMoreRrfModal-{{ $indicator->id }}" class="btn btn-neutral">
                    Close
                </label>
            </div>
        </div>
    </div>
@endforeach

<!-- ===================== -->
<!-- Scripts -->
<!-- ===================== -->
<script>
    // SweetAlert2 Delete Confirmation
    function confirmDelete(indicatorId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + indicatorId).submit();
            }
        });
    }

    // Simple Search
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('indicatorSearch');
        const tableRows = document.querySelectorAll('#indicatorsTable tbody tr');

        if (searchInput && tableRows) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                tableRows.forEach(row => {
                    const rowText = row.textContent.toLowerCase();
                    row.style.display = rowText.includes(searchTerm) ? '' : 'none';
                });
            });
        }
    });
</script>
