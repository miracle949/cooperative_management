{{--
Role permission selector — categorized sidebar-permission checkboxes.
Shared by the role create form and the edit-role modal.

Expects: $permissionCategories (array of categories, each with a label,
icon and 'items' keyed by permission slug).

Checked state is managed entirely by JavaScript (all checkboxes render
unchecked; openEditRole() populates them when editing a role), so this
markup is safe to reuse in both forms without duplicating logic.
--}}
@foreach($permissionCategories as $catKey => $cat)
<div class="js-perm-category rounded-xl border border-slate-200 bg-white overflow-hidden">
    <div class="flex items-center justify-between px-4 py-2.5 bg-slate-50 border-b border-slate-200">
        <div class="flex items-center gap-2">
            <i data-lucide="{{ $cat['icon'] }}" class="w-4 h-4 text-slate-500"></i>
            <span class="text-sm font-semibold text-slate-900">{{ $cat['label'] }}</span>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" class="js-perm-cat-select text-xs font-medium text-indigo-600 hover:text-indigo-800 transition-colors">Select all</button>
            <button type="button" class="js-perm-cat-clear text-xs font-medium text-slate-400 hover:text-slate-600 transition-colors">Clear</button>
        </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-px bg-slate-100">
        @foreach($cat['items'] as $key => $item)
        <label class="flex items-start gap-3 p-4 bg-white hover:bg-slate-50 transition-colors cursor-pointer">
            <input type="checkbox" name="sidebar_permissions[]" value="{{ $key }}"
                class="role-perm-checkbox mt-0.5 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                data-sensitive="{{ ($item['sensitive'] ?? false) ? '1' : '0' }}">
            <span class="flex-1 min-w-0">
                <span class="flex items-center flex-wrap gap-1.5">
                    <i data-lucide="{{ $item['icon'] }}" class="w-4 h-4 text-slate-400 flex-shrink-0"></i>
                    <span class="text-sm font-medium text-slate-800">{{ $item['label'] }}</span>
                    @if($item['sensitive'] ?? false)
                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wide bg-amber-100 text-amber-700 border border-amber-200 rounded-full px-1.5 py-0.5">
                        <i data-lucide="alert-triangle" class="w-3 h-3"></i>Sensitive
                    </span>
                    @elseif($item['future'] ?? false)
                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wide bg-slate-100 text-slate-500 border border-slate-200 rounded-full px-1.5 py-0.5">Future</span>
                    @endif
                </span>
                <span class="block text-xs text-slate-500 mt-1 leading-relaxed">{{ $item['desc'] }}</span>
                @if($item['submodule'] ?? false)
                <span class="block text-xs text-slate-400 mt-0.5">↳ Managed inside {{ ucfirst($item['submodule']) }}</span>
                @endif
            </span>
        </label>
        @endforeach
    </div>
</div>
@endforeach