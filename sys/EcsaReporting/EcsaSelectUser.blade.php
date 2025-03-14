<div class="p-4 md:p-6">
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col gap-4">
            <div class="text-left">
                <h2 class="text-2xl font-bold">
                    Select ECSA-HC User to Begin Reporting
                </h2>
                <p class="text-sm text-base-content/70 mt-1">{{ $Desc }}</p>
            </div>
        </div>
    </div>
</div>

<div class="px-4 md:px-6 pb-6">
    <div class="max-w-7xl mx-auto">
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    <div class="md:col-span-8">
                        <form action="{{ route('Ecsa_SelectCluster') }}" method="GET">
                            @csrf
                            <div class="form-control w-full mb-4">
                                <label class="label" for="UserID">
                                    <span class="label-text">Select ECSA-HC User</span>
                                </label>
                                <select class="select select-bordered w-full @error('UserID') select-error @enderror"
                                    id="UserID" name="UserID" required>
                                    <option value="">Select a user...</option>
                                    @foreach ($users as $user)
                                        @if (Auth::user()->AccountRole === 'Admin' || $user->UserID === Auth::user()->UserID)
                                            <option value="{{ $user->UserID }}"
                                                {{ old('UserID') == $user->UserID ? 'selected' : '' }}>
                                                {{ $user->name }} - {{ $user->email }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('UserID')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>

                            <div class="mt-6">
                                <button type="submit" class="btn btn-primary w-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"></path>
                                        <path d="M6 21v-2a4 4 0 0 1 4 -4h4"></path>
                                        <path d="M15 19l2 2l4 -4"></path>
                                    </svg>
                                    Continue with Selected User
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var el;
        window.TomSelect && (new TomSelect(el = document.getElementById('UserID'), {
            copyClassesToDropdown: false,
            dropdownParent: 'body',
            controlInput: '<input>',
            render: {
                item: function(data, escape) {
                    if (data.customProperties) {
                        return '<div><span class="dropdown-item-indicator">' + data
                            .customProperties + '</span>' + escape(data.text) + '</div>';
                    }
                    return '<div>' + escape(data.text) + '</div>';
                },
                option: function(data, escape) {
                    if (data.customProperties) {
                        return '<div><span class="dropdown-item-indicator">' + data
                            .customProperties + '</span>' + escape(data.text) + '</div>';
                    }
                    return '<div>' + escape(data.text) + '</div>';
                },
            },
        }));
    });
</script>
