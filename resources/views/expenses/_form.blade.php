@php
    $expense = $expense ?? null;

    $labelClass = 'block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5';
    $inputClass = 'w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition';
    $textareaClass = 'w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg resize-none text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition';
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 space-y-4">
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 bg-gray-50/60">
                <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Expense Information</h3>
            </div>

            <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="{{ $labelClass }}">Reference</label>
                    <input type="text"
                           name="reference"
                           value="{{ old('reference', $expense?->reference ?? $nextReference ?? '') }}"
                           class="{{ $inputClass }}">
                    @error('reference')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="{{ $labelClass }}">Category</label>
                    <select name="category" class="{{ $inputClass }}">
                        <option value="">Select category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}"
                                @selected(old('category', $expense?->category) === $category)>
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                    @error('category')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="{{ $labelClass }}">Amount</label>
                    <input type="number"
                           name="amount"
                           min="0.01"
                           step="0.01"
                           value="{{ old('amount', $expense?->amount) }}"
                           placeholder="0.00"
                           class="{{ $inputClass }}">
                    @error('amount')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="{{ $labelClass }}">Date</label>
                    <input type="date"
                           name="date"
                           value="{{ old('date', optional($expense?->date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                           class="{{ $inputClass }}">
                    @error('date')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="{{ $labelClass }}">Note</label>
                    <textarea name="note"
                              rows="4"
                              placeholder="Write expense note..."
                              class="{{ $textareaClass }}">{{ old('note', $expense?->note) }}</textarea>
                    @error('note')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 bg-gray-50/60">
                <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Document</h3>
            </div>

            <div class="p-5 space-y-4">
                <div>
                    <label class="{{ $labelClass }}">Attachment</label>
                    <input type="file"
                           name="document"
                           class="w-full text-xs text-gray-500 bg-gray-50 border border-gray-200 rounded-lg cursor-pointer
                                  file:h-8 file:mr-3 file:px-3 file:rounded-md file:border-0
                                  file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700
                                  hover:file:bg-blue-100 transition">

                    @error('document')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror

                    @if($expense?->document)
                        <a href="{{ asset('storage/'.$expense->document) }}"
                           target="_blank"
                           class="mt-2 inline-flex text-xs text-blue-600 hover:underline">
                            View current document
                        </a>
                    @endif
                </div>

                <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2.5 text-xs text-amber-700">
                    Allowed: JPG, PNG, PDF, DOC, DOCX, XLS, XLSX. Max size: 5MB.
                </div>
            </div>
        </div>
    </div>
</div>
