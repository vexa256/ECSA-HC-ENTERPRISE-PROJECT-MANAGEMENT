<div class="panel-section hidden" data-section="hr">
    <h3 class="text-xl font-semibold mb-4 text-base-content">HR</h3>
    <div class="grid grid-cols-2 gap-3 mb-6">
        <a href="{{ route('appraisal_appraisal_cycles.index') }}"
            class="menu-item flex items-center p-3 bg-base-100 rounded-lg hover:bg-accent hover:text-accent-content transition-all duration-300">
            <span class="menu-icon iconify text-xl text-accent mr-3" data-icon="lucide:refresh-cw"></span>
            <span class="text-sm font-medium text-base-content">Appraisal Cycle</span>
        </a>
        <a href="{{ route('staff_management.index') }}"
            class="menu-item flex items-center p-3 bg-base-100 rounded-lg hover:bg-secondary hover:text-secondary-content transition-all duration-300">
            <span class="menu-icon iconify text-xl text-secondary mr-3" data-icon="lucide:users"></span>
            <span class="text-sm font-medium text-base-content">Staff</span>
        </a>
    </div>
    <div class="space-y-4">
        <div>
            <h4 class="text-xs font-semibold text-base-content opacity-70 uppercase mb-2">Administration &
                Setup</h4>
            <div class="space-y-2">
                <a href="{{ url('/') }}"
                    class="menu-item flex items-center p-2 rounded-md hover:bg-neutral hover:text-neutral-content transition-all duration-300">
                    <span class="menu-icon iconify text-lg text-neutral mr-2" data-icon="lucide:users"></span>
                    <span class="text-sm text-base-content">User Management</span>
                </a>
                <a href="{{ route('appraisal_positions.index') }}"
                    class="menu-item flex items-center p-2 rounded-md hover:bg-accent hover:text-accent-content transition-all duration-300">
                    <span class="menu-icon iconify text-lg text-accent mr-2"
                        data-icon="lucide:briefcase"></span>
                    <span class="text-sm text-base-content">Position Management</span>
                </a>
            </div>
        </div>
        <div>
            <h4 class="text-xs font-semibold text-base-content opacity-70 uppercase mb-2">Appraisal
                Configuration</h4>
            <div class="space-y-2">
                <a href="{{ route('appraisal_form_types.index') }}"
                    class="menu-item flex items-center p-2 rounded-md hover:bg-secondary hover:text-secondary-content transition-all duration-300">
                    <span class="menu-icon iconify text-lg text-secondary mr-2"
                        data-icon="lucide:file-text"></span>
                    <span class="text-sm text-base-content">Appraisal Forms</span>
                </a>
                <a href="{{ route('appraisal_performance_factors.index') }}"
                    class="menu-item flex items-center p-2 rounded-md hover:bg-error hover:text-error-content transition-all duration-300">
                    <span class="menu-icon iconify text-lg text-error mr-2" data-icon="lucide:target"></span>
                    <span class="text-sm text-base-content">Performance Factors</span>
                </a>
                <a href="{{ route('appraisal_rating_scales.index') }}"
                    class="menu-item flex items-center p-2 rounded-md hover:bg-primary hover:text-primary-content transition-all duration-300">
                    <span class="menu-icon iconify text-lg text-primary mr-2"
                        data-icon="lucide:bar-chart-2"></span>
                    <span class="text-sm text-base-content">Rating Scales</span>
                </a>
            </div>
        </div>
        <div>
            <h4 class="text-xs font-semibold text-base-content opacity-70 uppercase mb-2">Cycle Management</h4>
            <div class="space-y-2">
                <a href="{{ route('appraisal_cycle_initiation.index') }}"
                    class="menu-item flex items-center p-2 rounded-md hover:bg-accent hover:text-accent-content transition-all duration-300">
                    <span class="menu-icon iconify text-lg text-accent mr-2"
                        data-icon="lucide:play-circle"></span>
                    <span class="text-sm text-base-content">Initiate Cycle</span>
                </a>
            </div>
        </div>
        <div>
            <h4 class="text-xs font-semibold text-base-content opacity-70 uppercase mb-2">Performance Execution
            </h4>
            <div class="space-y-2">
                <a href="{{ url('/') }}"
                    class="menu-item flex items-center p-2 rounded-md hover:bg-neutral hover:text-neutral-content transition-all duration-300">
                    <span class="menu-icon iconify text-lg text-neutral mr-2" data-icon="lucide:eye"></span>
                    <span class="text-sm text-base-content">Launch/View Appraisals</span>
                </a>
                <a href="{{ url('/') }}"
                    class="menu-item flex items-center p-2 rounded-md hover:bg-accent hover:text-accent-content transition-all duration-300">
                    <span class="menu-icon iconify text-lg text-accent mr-2"
                        data-icon="lucide:check-square"></span>
                    <span class="text-sm text-base-content">Sign-off Management</span>
                </a>
                <a href="{{ url('/') }}"
                    class="menu-item flex items-center p-2 rounded-md hover:bg-secondary hover:text-secondary-content transition-all duration-300">
                    <span class="menu-icon iconify text-lg text-secondary mr-2"
                        data-icon="lucide:edit-3"></span>
                    <span class="text-sm text-base-content">Enter Appraisal</span>
                </a>
                <a href="{{ url('/') }}"
                    class="menu-item flex items-center p-2 rounded-md hover:bg-error hover:text-error-content transition-all duration-300">
                    <span class="menu-icon iconify text-lg text-error mr-2" data-icon="lucide:users"></span>
                    <span class="text-sm text-base-content">360° Feedback</span>
                </a>
                <a href="{{ url('/') }}"
                    class="menu-item flex items-center p-2 rounded-md hover:bg-primary hover:text-primary-content transition-all duration-300">
                    <span class="menu-icon iconify text-lg text-primary mr-2" data-icon="lucide:send"></span>
                    <span class="text-sm text-base-content">Submit Appraisal</span>
                </a>
                <a href="{{ url('/') }}"
                    class="menu-item flex items-center p-2 rounded-md hover:bg-accent hover:text-accent-content transition-all duration-300">
                    <span class="menu-icon iconify text-lg text-accent mr-2"
                        data-icon="lucide:check-circle"></span>
                    <span class="text-sm text-base-content">Finalize Appraisal</span>
                </a>
            </div>
        </div>
        <div>
            <h4 class="text-xs font-semibold text-base-content opacity-70 uppercase mb-2">Additional Processes
                & Reports</h4>
            <div class="space-y-2">
                <a href="{{ route('appraisal_appraisal_generation.index') }}"
                    class="menu-item flex items-center p-2 rounded-md hover:bg-neutral hover:text-neutral-content transition-all duration-300">
                    <span class="menu-icon iconify text-lg text-neutral mr-2"
                        data-icon="lucide:file-plus"></span>
                    <span class="text-sm text-base-content">Appraisal Generation</span>
                </a>
                <a href="{{ url('/') }}"
                    class="menu-item flex items-center p-2 rounded-md hover:bg-accent hover:text-accent-content transition-all duration-300">
                    <span class="menu-icon iconify text-lg text-accent mr-2"
                        data-icon="lucide:bar-chart-2"></span>
                    <span class="text-sm text-base-content">Reports & Analytics</span>
                </a>
                <a href="{{ url('/') }}"
                    class="menu-item flex items-center p-2 rounded-md hover:bg-secondary hover:text-secondary-content transition-all duration-300">
                    <span class="menu-icon iconify text-lg text-secondary mr-2"
                        data-icon="lucide:trending-up"></span>
                    <span class="text-sm text-base-content">Development Plans</span>
                </a>
                <a href="{{ url('/') }}"
                    class="menu-item flex items-center p-2 rounded-md hover:bg-error hover:text-error-content transition-all duration-300">
                    <span class="menu-icon iconify text-lg text-error mr-2"
                        data-icon="lucide:clipboard"></span>
                    <span class="text-sm text-base-content">Performance Plans</span>
                </a>
                <a href="{{ url('/') }}"
                    class="menu-item flex items-center p-2 rounded-md hover:bg-primary hover:text-primary-content transition-all duration-300">
                    <span class="menu-icon iconify text-lg text-primary mr-2"
                        data-icon="lucide:layers"></span>
                    <span class="text-sm text-base-content">Bulk Operations</span>
                </a>
                <a href="{{ url('/') }}"
                    class="menu-item flex items-center p-2 rounded-md hover:bg-accent hover:text-accent-content transition-all duration-300">
                    <span class="menu-icon iconify text-lg text-accent mr-2" data-icon="lucide:bell"></span>
                    <span class="text-sm text-base-content">Automated Notifications</span>
                </a>
            </div>
        </div>
    </div>
</div>
