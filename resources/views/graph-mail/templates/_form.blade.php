{{-- main grid --}}
<div class="grid md:grid-cols-3 gap-4 lg:gap-6 mb-6">
    {{-- LEFT COLUMN: main sections --}}
    <div class="md:col-span-2 flex flex-col gap-4 lg:gap-6">

        {{-- Template details --}}
        <section
                class="rounded-2xl bg-white/90 dark:bg-gray-950/80 border border-gray-100/70
           dark:border-gray-800/80 shadow-sm p-4 sm:p-5">
            <h2 class="font-semibold text-base sm:text-lg mb-1">
                Template Core
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                Essential information for identifying and managing this template.
            </p>

            <div class="grid gap-4 sm:grid-cols-2">
                <!-- Key -->
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wide">
                        Unique key
                    </label>
                    <input
                            type="text"
                            name="key"
                            value="{{ old('key', $template->key) }}"
                            class="{{ $inputClass }}"
                            @if($isEdit) readonly @endif
                    >
                    @error('key')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                    @if($isEdit)
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">
                            The key cannot be changed after creation.
                        </p>
                    @endif
                </div>

                <!-- Name -->
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wide">
                        Display name
                    </label>
                    <input
                            type="text"
                            name="name"
                            value="{{ old('name', $template->name) }}"
                            class="{{ $inputClass }}"
                    >
                    @error('name')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Module -->
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wide">
                        Module (optional)
                    </label>
                    <input
                            type="text"
                            name="module"
                            value="{{ old('module', $template->module) }}"
                            class="{{ $inputClass }}"
                            placeholder="e.g. billing, auth, marketing"
                    >
                    @error('module')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Active -->
                <div class="mt-2 sm:mt-6">
                    <input type="hidden" name="active" value="0">

                    <div class="flex items-start gap-2">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input
                                    type="checkbox"
                                    name="active"
                                    value="1"
                                    class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 focus:ring-offset-0"
                                    {{ old('active', $template->active) ? 'checked' : '' }}
                            >
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-200">
                        Active template
                    </span>
                        </label>

                        <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-snug">
                            Only active templates will be used when sending emails.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section
                class="rounded-2xl bg-white/90 dark:bg-gray-950/80 border border-gray-100/70
           dark:border-gray-800/80 shadow-sm p-4 sm:p-5">
            <h2 class="font-semibold text-base sm:text-lg mb-1">
                Delivery Defaults
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                Default email metadata used when sending with this template.
            </p>

            <div class="grid gap-4 sm:grid-cols-2">
                <!-- Subject -->
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wide">
                        Default subject (optional)
                    </label>
                    <input
                            type="text"
                            name="default_subject"
                            value="{{ old('default_subject', $template->default_subject) }}"
                            class="{{ $inputClass }}"
                    >
                    @error('default_subject')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- To -->
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wide">
                        To (optional)
                    </label>
                    <input
                            type="text"
                            name="to"
                            value="{{ old('to', $toValue) }}"
                            class="{{ $inputClass }}"
                            placeholder="e.g. asd@example.com, test@example.com"
                    >
                    @error('to')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- CC -->
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wide">
                        CC (optional)
                    </label>
                    <input
                            type="text"
                            name="cc"
                            value="{{ old('cc', $ccValue) }}"
                            class="{{ $inputClass }}"
                            placeholder="e.g. asd@example.com, test@example.com"
                    >
                    @error('cc')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- BCC -->
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wide">
                        BCC (optional)
                    </label>
                    <input
                            type="text"
                            name="bcc"
                            value="{{ old('bcc', $bccValue) }}"
                            class="{{ $inputClass }}"
                            placeholder="e.g. asd@example.com, test@example.com"
                    >
                    @error('bcc')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        {{-- Render configuration --}}
        <section
                class="rounded-2xl bg-white/90 dark:bg-gray-950/80 border border-gray-100/70
                   dark:border-gray-800/80 shadow-sm p-4 sm:p-5">
            <h2 class="font-semibold text-base sm:text-lg mb-1">
                Render configuration
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                Associate the template with a mailable class and a Blade view.
            </p>

            <div class="grid gap-4 sm:grid-cols-2">
                {{-- mailable_class --}}
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wide">
                        Mailable class (optional)
                    </label>
                    <input
                            type="text"
                            name="mailable_class"
                            value="{{ old('mailable_class', $template->mailable_class) }}"
                            placeholder="e.g. App\Mail\InvoicePaidMail"
                            class="{{ $inputClass }}"
                    >
                    @error('mailable_class')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- view --}}
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wide">
                        Blade view (optional)
                    </label>
                    <input
                            type="text"
                            name="view"
                            value="{{ old('view', $template->view) }}"
                            placeholder="e.g. mails.invoice-paid"
                            class="{{ $inputClass }}"
                    >
                    @error('view')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

        </section>
    </div>

    {{-- RIGHT COLUMN: JSON --}}
    <section
            class="rounded-2xl bg-white/90 dark:bg-gray-950/80 border border-gray-100/70
               dark:border-gray-800/80 shadow-sm p-4 sm:p-5 flex flex-col">
        <h2 class="font-semibold text-base sm:text-lg mb-1">
            Default data (JSON)
        </h2>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
            JSON structure with the default variables used in the view.
        </p>

        <textarea
                name="default_data"
                rows="12"
                class="{{ $textareaClass }}"
                placeholder='{"user": {"name": "Test"}, "link": "https://example.com"}'
        >{{ $jsonValue }}</textarea>

        @error('default_data')
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror

        <p class="mt-2 text-[11px] text-gray-500 dark:text-gray-400">
            If the JSON is not valid, the field will be ignored when saving.
        </p>
    </section>
</div>

{{-- buttons --}}
<div class="mt-6 lg:mt-8 flex items-center justify-end gap-3">
    <a href="{{ route('graphmail.templates.index') }}"
       class="inline-flex items-center rounded-xl border border-gray-200 px-3 py-1.5 text-xs sm:text-sm
              font-medium text-gray-700 hover:bg-gray-50
              dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800/80">
        Cancel
    </a>

    <button type="submit"
            class="inline-flex items-center rounded-xl bg-indigo-600 px-4 py-2 text-xs sm:text-sm
                   font-medium text-white shadow-sm hover:bg-indigo-700
                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
        {{ $isEdit ? 'Save changes' : 'Create template' }}
    </button>
</div>
