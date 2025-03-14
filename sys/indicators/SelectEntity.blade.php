<!-- resources/views/mpaIndicators/select-entity.blade.php -->
<div class="w-full">
    <div class="card w-full bg-base-100 shadow-xl">
        <!-- Card Body -->
        <div class="card-body">
            <!-- Header -->
            <h2 class="card-title text-lg font-bold mb-4">
                {{ $Desc }}
            </h2>

            <!-- Form to select MPA Entity (calls ShowEntityIndicators) -->
            <form action="{{ route('mpaIndicators.ShowEntityIndicators') }}" method="GET" class="space-y-4">
                @csrf

                <!-- Select Entity -->
                <div class="form-control">
                    <label for="EntityID" class="label font-semibold">Select Entity</label>
                    <select id="EntityID" name="EntityID" class="select select-bordered w-full" required>
                        <option value="" disabled selected>Please select...</option>
                        @foreach ($entities as $ent)
                            <option value="{{ $ent->EntityID }}">
                                {{ $ent->Entity }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button type="submit" class="btn btn-neutral btn-sm">
                        Manage Indicators
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
