<!-- Current Report Summary Card with iOS-inspired daisyUI design -->
<div class="p-4 w-full max-w-4xl mx-auto">
    <div class="card bg-base-100 shadow-xl rounded-2xl overflow-hidden border border-base-200">
        <!-- Card Header with iOS-style gradient -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-4">
            <div class="text-center">
                <h2 class="text-xl font-semibold text-primary">
                    Current Reporting Summary
                </h2>
                <p class="text-sm text-base-content opacity-70 mt-1">
                    (Metrics apply exclusively to the selected report and year)
                </p>
            </div>
        </div>

        <!-- Card Body with iOS-style table -->
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <!-- Table Header -->
                    <thead>
                        <tr class="bg-base-200">
                            <th class="text-center font-medium text-base-content">Entity</th>
                            <th class="text-center font-medium text-base-content">Report Name</th>
                            <th class="text-center font-medium text-base-content">Year</th>
                        </tr>
                    </thead>

                    <!-- Table Body -->
                    <tbody>
                        <tr class="hover:bg-base-200 transition-colors duration-200">
                            <td class="text-center">{{ $entity->Entity }}</td>
                            <td class="text-center">{{ $timeline->ReportName }}</td>
                            <td class="text-center">{{ $timeline->Year }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- iOS-style footer with subtle gradient -->
        <div class="card-actions justify-end p-4 bg-gradient-to-r from-indigo-50 to-blue-50 border-t border-base-200">
            <div class="badge badge-outline badge-primary">Summary</div>
        </div>
    </div>
</div>
