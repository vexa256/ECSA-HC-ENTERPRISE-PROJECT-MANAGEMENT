<!-- resources/views/ecsaIndicators/select-strategic-objective.blade.php -->

<div class="w-full px-2 py-2">
    <!-- Card Container -->
    <div class="card w-full bg-base-100 shadow-xl rounded-lg">
        <!-- Card Header -->
        <div class="card-body border-b">
            <h4 class="text-xl font-bold">
                {{ $Desc }}
            </h4>
        </div>

        <!-- Card Body -->
        <div class="card-body">
            <!-- Form -->
            <form action="{{ route('MgtEcsaIndicators') }}" method="GET">
                @csrf
                <!-- Strategic Objective Select -->
                <div class="form-control mb-4">
                    <label class="label font-semibold" for="StrategicObjectiveID">
                        <span class="label-text">Strategic Objective</span>
                    </label>
                    <select id="StrategicObjectiveID" name="StrategicObjectiveID" class="select select-bordered w-full"
                        required>
                        <option value="" disabled selected>Please select...</option>
                        @foreach ($strategicObjectives as $obj)
                            <option value="{{ $obj->StrategicObjectiveID }}">
                                {{ $obj->SO_Number }} {{ $obj->Description }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button type="submit" class="btn btn-active">
                        Attach Indicators
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- SweetAlert2 Notification for messages passed from the controller -->
@if (isset($message) && $message)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Notification',
                text: "{{ $message }}",
                icon: 'info',
                confirmButtonText: 'OK'
            });
        });
    </script>
@endif
