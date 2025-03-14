<!-- resources/views/mpaIndicators/manage-indicators.blade.php -->

<div class="w-full p-4">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row items-start md:items-center gap-2 mb-4">
        <h2 class="text-xl font-bold">
            Manage Indicators for {{ $SelectedEntity->Entity }}
        </h2>
        <!-- "Add Indicator" Buttons: Desktop & Mobile -->
        <div class="ml-auto flex items-center gap-2">
            <!-- Desktop Button -->
            <label for="addIndicatorModal" class="btn btn-active hidden md:inline-flex">
                <span class="iconify mr-2" data-icon="mdi:plus" data-inline="true"></span>
                Add New Indicator
            </label>
            <!-- Mobile Button -->
            <label for="addIndicatorModal" class="btn btn-active md:hidden btn-square" aria-label="Add New Indicator">
                <span class="iconify" data-icon="mdi:plus" data-inline="true"></span>
            </label>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card bg-base-100 shadow-xl w-full">
        <!-- Card Header (Title + Entries/Filter) -->
        <div class="card-body border-b">
            <h3 class="text-lg font-semibold mb-4">Indicators List</h3>
            <div class="flex flex-col md:flex-row items-start md:items-center gap-3 text-sm">
                <!-- "Show X entries" -->
                <div class="flex items-center gap-1">
                    <span>Show</span>
                    <select class="select select-bordered select-sm" aria-label="Items per page">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span>entries</span>
                </div>
                <!-- Search field -->
                <div class="md:ml-auto flex items-center gap-2">
                    <span>Search:</span>
                    <input type="text" class="input input-bordered input-sm" placeholder="Search indicators"
                        id="indicatorSearch">
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="table w-full datatable">
                <thead>
                    <tr>
                        <th>Indicator</th>
                        <th>Reporting Period</th>
                        <th>Baseline 2024</th>
                        <th>Target 2025</th>
                        <th>Target 2026</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($indicators as $indicator)
                        <tr>
                            <td>{{ $indicator->Indicator }}</td>
                            <td>{{ $indicator->ReportingPeriod }}</td>
                            <td>{{ $indicator->Baseline2024 ?? '-' }}</td>
                            <td>{{ $indicator->TargetYearTwo2025 ?? '-' }}</td>
                            <td>{{ $indicator->TargetYearThree2026 ?? '-' }}</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <!-- Edit -->
                                    <label for="editIndicatorModal-{{ $indicator->id }}"
                                        class="btn btn-outline btn-square" aria-label="Edit">
                                        <span class="iconify" data-icon="mdi:pencil" data-inline="true"></span>
                                    </label>
                                    <!-- View More -->
                                    <label for="viewMoreModal-{{ $indicator->id }}" class="btn btn-outline btn-square"
                                        aria-label="View">
                                        <span class="iconify" data-icon="mdi:eye-outline" data-inline="true"></span>
                                    </label>
                                    <!-- Delete -->
                                    <form id="delete-form-{{ $indicator->id }}"
                                        action="{{ route('mpaIndicators.DeleteIndicator') }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="id" value="{{ $indicator->id }}">
                                        <button type="button" class="btn btn-outline btn-square"
                                            onclick="confirmDelete('{{ $indicator->id }}')" aria-label="Delete">
                                            <span class="iconify" data-icon="mdi:trash-can-outline"
                                                data-inline="true"></span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <!-- No indicators -->
                        <tr>
                            <td colspan="6" class="text-center py-6">
                                <div class="flex flex-col items-center gap-2">
                                    <img src="{{ asset('static/illustrations/undraw_printing_invoices_5r4r.svg') }}"
                                        alt="No indicators" class="w-32 h-auto">
                                    <p class="text-xl">No indicators found</p>
                                    <p class="text-gray-500">
                                        Try adjusting your search or filter to find what you're looking for.
                                    </p>
                                    <!-- Add Indicator CTA -->
                                    <label for="addIndicatorModal" class="btn btn-active mt-2">
                                        <span class="iconify mr-2" data-icon="mdi:plus" data-inline="true"></span>
                                        Add New Indicator
                                    </label>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>


    </div>
</div>


<!-- ============================== -->
<!-- ADD INDICATOR MODAL (DaisyUI) -->
<!-- ============================== -->
<input type="checkbox" id="addIndicatorModal" class="modal-toggle" />
<div class="modal">
    <div class="modal-box relative w-full max-w-6xl">
        <!-- Close Button -->
        <label for="addIndicatorModal" class="btn btn-sm btn-circle absolute right-2 top-2">
            ✕
        </label>
        <h3 class="text-lg font-bold mb-4">Add New Indicator</h3>

        <form action="{{ route('mpaIndicators.StoreIndicator') }}" method="POST" id="addIndicatorForm">
            @csrf
            <!-- 1st Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
                <!-- Primary Category -->
                <div>
                    <label class="label font-semibold">Primary Category</label>
                    <select class="select select-bordered w-full" name="IndicatorPrimaryCategory" required>
                        <option value="CRF" selected>CRF</option>
                    </select>
                </div>
                <!-- Secondary Category -->
                <div>
                    <label class="label font-semibold">Secondary Category</label>
                    <select class="select select-bordered w-full" name="IndicatorSecondaryCategory" required>
                        <option value="" disabled selected>Select Secondary Category</option>
                        <option value="CRF PDO">CRF PDO</option>
                        <option value="CRF Intermediate">CRF Intermediate</option>
                    </select>
                </div>
                <!-- Indicator (full width) -->
                <div class="lg:col-span-2">
                    <label class="label font-semibold">Indicator</label>
                    <textarea class="textarea textarea-bordered w-full" name="Indicator" required></textarea>
                </div>
            </div>

            <!-- 2nd Row -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <!-- Definition -->
                <div>
                    <label class="label font-semibold">Definition</label>
                    <textarea class="textarea textarea-bordered w-full" name="IndicatorDefinition" rows="3"></textarea>
                </div>
                <!-- Question -->
                <div>
                    <label class="label font-semibold">Question</label>
                    <textarea class="textarea textarea-bordered w-full" name="IndicatorQuestion" rows="3"></textarea>
                </div>
                <!-- Source Of Data -->
                <div>
                    <label class="label font-semibold">Source of Data</label>
                    <input type="text" class="input input-bordered w-full" name="SourceOfData">
                </div>
            </div>

            <!-- 3rd Row -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <!-- Response Type -->
                <div>
                    <label class="label font-semibold">Response Type</label>
                    <select class="select select-bordered w-full" name="ResponseType" required>
                        <option value="" disabled selected>Select Type</option>
                        <option value="Text">Text</option>
                        <option value="Number">Number</option>
                        <option value="Boolean">Boolean</option>
                        <option value="Yes/No">Yes/No</option>
                    </select>
                </div>
                <!-- Reporting Period -->
                <div>
                    <label class="label font-semibold">Reporting Period</label>
                    <select class="select select-bordered w-full" name="ReportingPeriod" required>
                        <option value="" disabled selected>Select Period</option>
                        <option value="Quarterly">Quarterly</option>
                        <option value="Bi-Annual">Bi-Annual</option>
                        <option value="Annually Reported">Annually Reported</option>
                    </select>
                </div>
                <!-- Expected Target -->
                <div>
                    <label class="label font-semibold">Expected Target</label>
                    <input type="text" class="input input-bordered w-full" name="ExpectedTarget">
                </div>
            </div>

            <!-- 4th Row -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <!-- Baseline PAD 2023 -->
                <div>
                    <label class="label font-semibold">Baseline PAD 2023</label>
                    <input type="text" class="input input-bordered w-full" name="BaselinePAD2023">
                </div>
                <!-- Baseline 2024 -->
                <div>
                    <label class="label font-semibold">Baseline 2024</label>
                    <input type="text" class="input input-bordered w-full" name="Baseline2024">
                </div>
                <!-- Target 2024 (Year 1) -->
                <div>
                    <label class="label font-semibold">Target 2024 (Year 1)</label>
                    <input type="text" class="input input-bordered w-full" name="TargetYearOne2024">
                </div>
            </div>

            <!-- 5th Row -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <!-- Target 2025 (Year 2) -->
                <div>
                    <label class="label font-semibold">Target 2025 (Year 2)</label>
                    <input type="text" class="input input-bordered w-full" name="TargetYearTwo2025">
                </div>
                <!-- Target 2026 (Year 3) -->
                <div>
                    <label class="label font-semibold">Target 2026 (Year 3)</label>
                    <input type="text" class="input input-bordered w-full" name="TargetYearThree2026">
                </div>
                <!-- Target 2027 (Year 4) -->
                <div>
                    <label class="label font-semibold">Target 2027 (Year 4)</label>
                    <input type="text" class="input input-bordered w-full" name="TargetYearFour2027">
                </div>
            </div>

            <!-- 6th Row -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <!-- Target 2028 (Year 5) -->
                <div>
                    <label class="label font-semibold">Target 2028 (Year 5)</label>
                    <input type="text" class="input input-bordered w-full" name="TargetYearFive2028">
                </div>
                <!-- Target 2029 (Year 6) -->
                <div>
                    <label class="label font-semibold">Target 2029 (Year 6)</label>
                    <input type="text" class="input input-bordered w-full" name="TargetYearSix2029">
                </div>
                <!-- Target 2030 (Year 7) -->
                <div>
                    <label class="label font-semibold">Target 2030 (Year 7)</label>
                    <input type="text" class="input input-bordered w-full" name="TargetYearSeven2030">
                </div>
            </div>

            <!-- Remarks/Comments -->
            <div class="mb-4">
                <label class="label font-semibold">Remarks / Comments</label>
                <textarea class="textarea textarea-bordered w-full" name="RemarksComments" rows="3"></textarea>
            </div>

            <!-- Modal Footer -->
            <div class="modal-action">
                <!-- Cancel -->
                <label for="addIndicatorModal" class="btn btn-neutral">
                    Cancel
                </label>
                <!-- Add Indicator -->
                <button type="submit" class="btn btn-active">
                    <span class="iconify mr-2" data-icon="mdi:plus" data-inline="true"></span>
                    Add Indicator
                </button>
            </div>
        </form>
    </div>
</div>


<!-- ================================ -->
<!-- EDIT INDICATOR MODALS (DaisyUI) -->
<!-- ================================ -->
@foreach ($indicators as $indicator)
    <!-- Checkbox toggle -->
    <input type="checkbox" id="editIndicatorModal-{{ $indicator->id }}" class="modal-toggle" />
    <div class="modal">
        <div class="modal-box relative w-full max-w-6xl">
            <!-- Close Button -->
            <label for="editIndicatorModal-{{ $indicator->id }}"
                class="btn btn-sm btn-circle absolute right-2 top-2">
                ✕
            </label>
            <h3 class="text-lg font-bold mb-4">
                Edit Indicator (ID: {{ $indicator->id }})
            </h3>

            <form action="{{ route('mpaIndicators.UpdateIndicator') }}" method="POST"
                id="editIndicatorForm-{{ $indicator->id }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" value="{{ $indicator->id }}">
                <input type="hidden" name="EntityID" value="{{ $SelectedEntity->EntityID }}">
                <input type="hidden" name="IID" value="{{ $indicator->IID }}">

                <!-- 1st Row -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <!-- Primary Category -->
                    <div>
                        <label class="label font-semibold">Primary Category</label>
                        <select class="select select-bordered w-full" name="IndicatorPrimaryCategory" required>
                            <option value="RRF" @if ($indicator->PrimaryCategory === 'RRF') selected @endif>
                                RRF
                            </option>
                            <option value="CRF" @if ($indicator->PrimaryCategory === 'CRF') selected @endif>
                                CRF
                            </option>
                        </select>
                    </div>
                    <!-- Secondary Category -->
                    <div>
                        <label class="label font-semibold">Secondary Category</label>
                        <select class="select select-bordered w-full" name="IndicatorSecondaryCategory" required>
                            <option value="" disabled>Select Secondary Category</option>
                            <option value="CRF PDO" @if ($indicator->SecondaryCategory === 'CRF PDO') selected @endif>
                                CRF PDO
                            </option>
                            <option value="CRF Intermediate" @if ($indicator->SecondaryCategory === 'CRF Intermediate') selected @endif>
                                CRF Intermediate
                            </option>
                        </select>
                    </div>
                    <!-- Indicator -->
                    <div>
                        <label class="label font-semibold">Indicator</label>
                        <input type="text" class="input input-bordered w-full" name="Indicator"
                            value="{{ $indicator->Indicator }}" required>
                    </div>
                </div>

                <!-- 2nd Row -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <!-- Definition -->
                    <div>
                        <label class="label font-semibold">Definition</label>
                        <textarea class="textarea textarea-bordered w-full" name="IndicatorDefinition" rows="3">{{ $indicator->IndicatorDefinition }}</textarea>
                    </div>
                    <!-- Question -->
                    <div>
                        <label class="label font-semibold">Question</label>
                        <textarea class="textarea textarea-bordered w-full" name="IndicatorQuestion" rows="3">{{ $indicator->IndicatorQuestion }}</textarea>
                    </div>
                    <!-- Source Of Data -->
                    <div>
                        <label class="label font-semibold">Source Of Data</label>
                        <input type="text" class="input input-bordered w-full" name="SourceOfData"
                            value="{{ $indicator->SourceOfData }}">
                    </div>
                </div>

                <!-- 3rd Row -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <!-- Response Type -->
                    <div>
                        <label class="label font-semibold">Response Type</label>
                        <select class="select select-bordered w-full" name="ResponseType" required>
                            <option value="" disabled>Select Type</option>
                            <option value="Text" @if ($indicator->ResponseType === 'Text') selected @endif>
                                Text
                            </option>
                            <option value="Number" @if ($indicator->ResponseType === 'Number') selected @endif>
                                Number
                            </option>
                            <option value="Boolean" @if ($indicator->ResponseType === 'Boolean') selected @endif>
                                Boolean
                            </option>
                            <option value="Yes/No" @if ($indicator->ResponseType === 'Yes/No') selected @endif>
                                Yes/No
                            </option>
                        </select>
                    </div>
                    <!-- Reporting Period -->
                    <div>
                        <label class="label font-semibold">Reporting Period</label>
                        <select class="select select-bordered w-full" name="ReportingPeriod" required>
                            <option value="Quarterly" @if ($indicator->ReportingPeriod === 'Quarterly') selected @endif>
                                Quarterly
                            </option>
                            <option value="Bi-Annual" @if ($indicator->ReportingPeriod === 'Bi-Annual') selected @endif>
                                Bi-Annual
                            </option>
                            <option value="Annual" @if ($indicator->ReportingPeriod === 'Annual') selected @endif>
                                Annual
                            </option>
                        </select>
                    </div>
                    <!-- Expected Target -->
                    <div>
                        <label class="label font-semibold">Expected Target</label>
                        <input type="text" class="input input-bordered w-full" name="ExpectedTarget"
                            value="{{ $indicator->ExpectedTarget }}">
                    </div>
                </div>

                <!-- 4th Row -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <!-- Baseline PAD 2023 -->
                    <div>
                        <label class="label font-semibold">Baseline PAD 2023</label>
                        <input type="text" class="input input-bordered w-full" name="BaselinePAD2023"
                            value="{{ $indicator->BaselinePAD2023 }}">
                    </div>
                    <!-- Baseline 2024 -->
                    <div>
                        <label class="label font-semibold">Baseline 2024</label>
                        <input type="text" class="input input-bordered w-full" name="Baseline2024"
                            value="{{ $indicator->Baseline2024 }}">
                    </div>
                    <!-- Target 2024 (Year 1) -->
                    <div>
                        <label class="label font-semibold">Target 2024 (Year 1)</label>
                        <input type="text" class="input input-bordered w-full" name="TargetYearOne2024"
                            value="{{ $indicator->TargetYearOne2024 }}">
                    </div>
                </div>

                <!-- 5th Row -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <!-- Target 2025 (Year 2) -->
                    <div>
                        <label class="label font-semibold">Target 2025 (Year 2)</label>
                        <input type="text" class="input input-bordered w-full" name="TargetYearTwo2025"
                            value="{{ $indicator->TargetYearTwo2025 }}">
                    </div>
                    <!-- Target 2026 (Year 3) -->
                    <div>
                        <label class="label font-semibold">Target 2026 (Year 3)</label>
                        <input type="text" class="input input-bordered w-full" name="TargetYearThree2026"
                            value="{{ $indicator->TargetYearThree2026 }}">
                    </div>
                    <!-- Target 2027 (Year 4) -->
                    <div>
                        <label class="label font-semibold">Target 2027 (Year 4)</label>
                        <input type="text" class="input input-bordered w-full" name="TargetYearFour2027"
                            value="{{ $indicator->TargetYearFour2027 }}">
                    </div>
                </div>

                <!-- 6th Row -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <!-- Target 2028 (Year 5) -->
                    <div>
                        <label class="label font-semibold">Target 2028 (Year 5)</label>
                        <input type="text" class="input input-bordered w-full" name="TargetYearFive2028"
                            value="{{ $indicator->TargetYearFive2028 }}">
                    </div>
                    <!-- Target 2029 (Year 6) -->
                    <div>
                        <label class="label font-semibold">Target 2029 (Year 6)</label>
                        <input type="text" class="input input-bordered w-full" name="TargetYearSix2029"
                            value="{{ $indicator->TargetYearSix2029 }}">
                    </div>
                    <!-- Target 2030 (Year 7) -->
                    <div>
                        <label class="label font-semibold">Target 2030 (Year 7)</label>
                        <input type="text" class="input input-bordered w-full" name="TargetYearSeven2030"
                            value="{{ $indicator->TargetYearSeven2030 }}">
                    </div>
                </div>

                <!-- Remarks/Comments -->
                <div class="mb-4">
                    <label class="label font-semibold">Remarks / Comments</label>
                    <textarea class="textarea textarea-bordered w-full" name="RemarksComments" rows="3">{{ $indicator->RemarksComments }}</textarea>
                </div>

                <!-- Modal Footer -->
                <div class="modal-action">
                    <!-- Cancel -->
                    <label for="editIndicatorModal-{{ $indicator->id }}" class="btn btn-neutral">
                        Cancel
                    </label>
                    <!-- Update Indicator -->
                    <button type="submit" class="btn btn-active">
                        <span class="iconify mr-2" data-icon="mdi:check" data-inline="true"></span>
                        Update Indicator
                    </button>
                </div>
            </form>
        </div>
    </div>
@endforeach


<!-- ======================================= -->
<!-- VIEW MORE (FULL DETAILS) MODALS (DaisyUI) -->
<!-- ======================================= -->
@foreach ($indicators as $indicator)
    <input type="checkbox" id="viewMoreModal-{{ $indicator->id }}" class="modal-toggle" />
    <div class="modal">
        <div class="modal-box relative w-full max-w-6xl">
            <!-- Close Button -->
            <label for="viewMoreModal-{{ $indicator->id }}" class="btn btn-sm btn-circle absolute right-2 top-2">
                ✕
            </label>
            <h3 class="text-lg font-bold mb-4">
                Indicator Full Details (ID: {{ $indicator->id }})
            </h3>

            <div class="overflow-x-auto">
                <!-- Break the data into multiple small tables, just like original -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
                    <!-- Table 1 -->
                    <table class="table w-full">
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
                        </tbody>
                    </table>
                    <!-- Table 2 -->
                    <table class="table w-full">
                        <tbody>
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
                    <!-- Table 3 -->
                    <table class="table w-full">
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
                        </tbody>
                    </table>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
                    <!-- Table 4 -->
                    <table class="table w-full">
                        <tbody>
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
                    <!-- Table 5 -->
                    <table class="table w-full">
                        <tbody>
                            <tr>
                                <th>Target Year 1 (2024)</th>
                                <td>{{ $indicator->TargetYearOne2024 }}</td>
                            </tr>
                            <tr>
                                <th>Target Year 2 (2025)</th>
                                <td>{{ $indicator->TargetYearTwo2025 }}</td>
                            </tr>
                            <tr>
                                <th>Target Year 3 (2026)</th>
                                <td>{{ $indicator->TargetYearThree2026 }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <!-- Table 6 -->
                    <table class="table w-full">
                        <tbody>
                            <tr>
                                <th>Target Year 4 (2027)</th>
                                <td>{{ $indicator->TargetYearFour2027 }}</td>
                            </tr>
                            <tr>
                                <th>Target Year 5 (2028)</th>
                                <td>{{ $indicator->TargetYearFive2028 }}</td>
                            </tr>
                            <tr>
                                <th>Target Year 6 (2029)</th>
                                <td>{{ $indicator->TargetYearSix2029 }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <!-- Table 7 -->
                    <table class="table w-full">
                        <tbody>
                            <tr>
                                <th>Target Year 7 (2030)</th>
                                <td>{{ $indicator->TargetYearSeven2030 }}</td>
                            </tr>
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
                <label for="viewMoreModal-{{ $indicator->id }}" class="btn btn-neutral">
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

    // Simple Table Search
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('indicatorSearch');
        const tableRows = document.querySelectorAll('.datatable tbody tr');

        if (searchInput && tableRows) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                tableRows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
        }
    });
</script>
