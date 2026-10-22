@extends('layouts.admin')

@section('title', 'FAQs')

@section('content')
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">FAQs</h1>
            <p class="text-gray-500 mt-1">Manage the questions members see on their FAQ page.</p>
        </div>
        <button type="button" id="faq-add-btn" class="btn btn-primary">
            <i data-lucide="plus" class="w-4 h-4"></i> Add FAQ
        </button>
    </div>

    @forelse ($grouped as $category => $items)
        <div class="card mb-5">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900">{{ $category }}</h2>
                <span class="text-xs text-gray-500">{{ $items->count() }}
                    {{ \Illuminate\Support\Str::plural('question', $items->count()) }}</span>
            </div>

            <div class="divide-y divide-gray-100">
                @foreach ($items as $faq)
                    <div class="px-5 py-4 flex flex-col md:flex-row md:items-start gap-3 {{ $faq->is_active ? '' : 'bg-gray-50' }}">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="font-medium text-gray-900">{{ $faq->question }}</p>
                                @unless ($faq->is_active)
                                    <span class="badge badge-gray">Hidden</span>
                                @endunless
                            </div>
                            <p class="text-sm text-gray-600 mt-1 whitespace-pre-line">{{ $faq->answer }}</p>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <button type="button" class="btn btn-outline btn-sm"
                                data-faq-edit="{{ json_encode($faq->only(['id', 'category', 'question', 'answer', 'sort_order', 'is_active'])) }}">
                                <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Edit
                            </button>
                            <button type="button" class="btn btn-outline btn-sm" data-faq-toggle="{{ $faq->id }}">
                                {{ $faq->is_active ? 'Hide' : 'Show' }}
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" data-faq-delete="{{ $faq->id }}">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="card p-10 text-center">
            <p class="font-medium text-gray-900">No FAQs yet</p>
            <p class="text-sm text-gray-500 mt-1">Add your first question so members can find answers without contacting staff.
            </p>
        </div>
    @endforelse

    {{-- Add / edit modal --}}
    <div id="faqModal" class="modal-overlay hidden">
        <div class="modal max-w-lg">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900" id="faq-modal-title">Add FAQ</h2>
                <button type="button" data-close-modal="faqModal" class="p-1 rounded hover:bg-gray-100">
                    <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
                </button>
            </div>

            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="faq-category">Category</label>
                    <input type="text" id="faq-category" list="faq-categories" maxlength="100" class="input"
                        placeholder="Pick an existing category or type a new one">
                    <datalist id="faq-categories">
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="faq-question">Question</label>
                    <input type="text" id="faq-question" maxlength="255" class="input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="faq-answer">Answer</label>
                    <textarea id="faq-answer" rows="5" maxlength="2000" class="input"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="faq-order">Display order</label>
                        <input type="number" id="faq-order" min="0" class="input" placeholder="Auto (last)">
                    </div>
                    <label class="flex items-center gap-2 mt-6 text-sm text-gray-700 cursor-pointer">
                        <input type="checkbox" id="faq-active" checked class="w-4 h-4">
                        Visible to members
                    </label>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-2">
                <button type="button" data-close-modal="faqModal" class="btn btn-outline">Cancel</button>
                <button type="button" id="faq-save" class="btn btn-primary">Save FAQ</button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script nonce="{{ csp_nonce() }}">
        (function () {
            const token = '{{ csrf_token() }}';
            const urls = {
                store: "{{ route('admin.faqs.store') }}",
                update: "{{ route('admin.faqs.update', '__ID__') }}",
                toggle: "{{ route('admin.faqs.toggle', '__ID__') }}",
                destroy: "{{ route('admin.faqs.destroy', '__ID__') }}",
            };
            let editingId = null;

            const $ = (id) => document.getElementById(id);

            async function send(url, method, body) {
                const res = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                    body: body ? JSON.stringify(body) : undefined,
                });
                const data = await res.json().catch(() => ({}));
                return { ok: res.ok && data.success, data };
            }

            function fail(data) {
                const firstError = data.errors ? Object.values(data.errors)[0][0] : null;
                showToast('Could not save', firstError || data.message || 'Something went wrong.', 'error');
            }

            function openForm(faq) {
                editingId = faq ? faq.id : null;
                $('faq-modal-title').textContent = faq ? 'Edit FAQ' : 'Add FAQ';
                $('faq-category').value = faq ? faq.category : '';
                $('faq-question').value = faq ? faq.question : '';
                $('faq-answer').value = faq ? faq.answer : '';
                $('faq-order').value = faq ? faq.sort_order : '';
                $('faq-active').checked = faq ? !!faq.is_active : true;
                openModal('faqModal');
            }

            $('faq-add-btn').addEventListener('click', () => openForm(null));

            document.addEventListener('click', async function (e) {
                const closeBtn = e.target.closest('[data-close-modal]');
                if (closeBtn) {
                    closeModal(closeBtn.dataset.closeModal);
                    return;
                }

                const editBtn = e.target.closest('[data-faq-edit]');
                if (editBtn) {
                    openForm(JSON.parse(editBtn.dataset.faqEdit));
                    return;
                }

                const toggleBtn = e.target.closest('[data-faq-toggle]');
                if (toggleBtn) {
                    const r = await send(urls.toggle.replace('__ID__', toggleBtn.dataset.faqToggle), 'POST');
                    if (r.ok) {
                        showToast('Updated', r.data.message, 'success');
                        setTimeout(() => window.location.reload(), 600);
                    } else fail(r.data);
                    return;
                }

                const deleteBtn = e.target.closest('[data-faq-delete]');
                if (deleteBtn) {
                    if (!confirm('Delete this FAQ? Members will no longer see it.')) return;
                    const r = await send(urls.destroy.replace('__ID__', deleteBtn.dataset.faqDelete), 'DELETE');
                    if (r.ok) {
                        showToast('Deleted', r.data.message, 'success');
                        setTimeout(() => window.location.reload(), 600);
                    } else fail(r.data);
                }
            });

            $('faq-save').addEventListener('click', async function () {
                const btn = this;
                btn.disabled = true;

                const payload = {
                    category: $('faq-category').value,
                    question: $('faq-question').value,
                    answer: $('faq-answer').value,
                    sort_order: $('faq-order').value === '' ? null : parseInt($('faq-order').value, 10),
                    is_active: $('faq-active').checked,
                };

                const url = editingId ? urls.update.replace('__ID__', editingId) : urls.store;
                const r = await send(url, 'POST', payload);

                if (r.ok) {
                    showToast('Saved', r.data.message, 'success');
                    setTimeout(() => window.location.reload(), 600);
                } else {
                    fail(r.data);
                    btn.disabled = false;
                }
            });
        })();
    </script>
@endsection