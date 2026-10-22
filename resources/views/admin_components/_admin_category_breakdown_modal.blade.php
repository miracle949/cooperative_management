@php
    $roles = $roles ?? collect();
    $roleCounts = $roleCounts ?? [];
@endphp

<div id="adminCategoryModal" class="modal-overlay hidden">
    <div class="modal max-w-md">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                        <i data-lucide="shield" class="w-5 h-5 text-blue-600"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Admin Categories</h2>
                        <p class="text-xs text-gray-500">Category breakdown</p>
                    </div>
                </div>
                <button data-action="closeAdminCategoryModal" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
                </button>
            </div>
        </div>
        <div class="p-6 space-y-3">
            @forelse($roles as $role)
                @php $count = $roleCounts[$role->slug] ?? 0; @endphp
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-900">{{ $role->name }}</span>
                        @if($role->is_system)
                            <span class="text-xs text-primary-500 font-medium">System</span>
                        @else
                            <span class="text-xs text-gray-400">Custom</span>
                        @endif
                    </div>
                    <span class="text-sm font-bold text-blue-600">{{ $count }}</span>
                </div>
            @empty
                <div class="text-center py-6 text-gray-500 text-sm">No roles defined yet.</div>
            @endforelse
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end">
            <button data-action="closeAdminCategoryModal" class="px-5 py-2.5 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors">Close</button>
        </div>
    </div>
</div>

<script nonce="{{ csp_nonce() }}">
    function openAdminCategoryModal() {
        var m = document.getElementById('adminCategoryModal');
        m.classList.remove('hidden');
        m.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    function closeAdminCategoryModal() {
        var m = document.getElementById('adminCategoryModal');
        m.classList.add('hidden');
        m.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
</script>
