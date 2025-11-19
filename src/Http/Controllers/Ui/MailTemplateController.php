<?php

namespace ProgressiveStudios\GraphMail\Http\Controllers\Ui;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use ProgressiveStudios\GraphMail\Models\MailTemplate;

class MailTemplateController extends Controller
{
    public function index()
    {
        $templates = MailTemplate::query()
            ->orderBy('module')
            ->orderBy('name')
            ->paginate(20);

        $stats = MailTemplate::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN active = 1 THEN 1 ELSE 0 END) as active_count')
            ->selectRaw('SUM(CASE WHEN active = 0 THEN 1 ELSE 0 END) as inactive_count')
            ->first();

        return view('graph-mail::graph-mail.templates.index', [
            'templates'     => $templates,
            'total'         => (int) $stats->total,
            'activeCount'   => (int) $stats->active_count,
            'inactiveCount' => (int) $stats->inactive_count,
        ]);
    }

    public function create()
    {
        $template = new MailTemplate();

        return $this->formView('graph-mail::graph-mail.templates.create', $template);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        // hidden input + checkbox -> normalize to bool
        $data['active'] = $request->boolean('active');

        // transform comma-separated strings into arrays for JSON columns
        $to  = $this->parseEmailList($data['to']  ?? null);
        $cc  = $this->parseEmailList($data['cc']  ?? null);
        $bcc = $this->parseEmailList($data['bcc'] ?? null);

        $template = new MailTemplate();

        $template->fill([
            'key'             => $data['key'],
            'name'            => $data['name'],
            'to'              => $to,
            'cc'              => $cc,
            'bcc'             => $bcc,
            'module'          => $data['module'] ?? null,
            'mailable_class'  => $data['mailable_class'] ?? null,
            'view'            => $data['view'] ?? null,
            'default_subject' => $data['default_subject'] ?? null,
            'active'          => $data['active'],
        ]);

        $decodedJson = $this->decodeJsonField($data['default_data'] ?? null);

        // Dacă JSON-ul e valid, îl salvăm; dacă nu, îl ignorăm complet (câmp null).
        if ($decodedJson !== null) {
            $template->default_data = $decodedJson;
        }

        $template->save();

        $flash = [
            'success' => __('Template-ul a fost creat cu succes.'),
        ];

        if ($request->filled('default_data') && $decodedJson === null) {
            $flash['warning'] = __('Datele JSON nu sunt valide, câmpul „Date implicite” a fost ignorat la salvare.');
        }

        return redirect()
            ->route('graphmail.templates.index')
            ->with($flash);
    }

    public function edit(MailTemplate $template)
    {
        return $this->formView('graph-mail::graph-mail.templates.edit', $template);
    }

    public function update(Request $request, MailTemplate $template)
    {
        $data = $this->validateData($request, $template->id);

        // hidden input + checkbox -> normalize to bool
        $data['active'] = $request->boolean('active');

        // transform comma-separated strings into arrays for JSON columns
        $to  = $this->parseEmailList($data['to']  ?? null);
        $cc  = $this->parseEmailList($data['cc']  ?? null);
        $bcc = $this->parseEmailList($data['bcc'] ?? null);

        $template->fill([
            // cheia e readonly în form, dar o lăsăm să se „reconfirme”
            'key'             => $data['key'],
            'name'            => $data['name'],
            'to'              => $to,
            'cc'              => $cc,
            'bcc'             => $bbox ?? $bcc, // small typo fix: use $bcc
            'module'          => $data['module'] ?? null,
            'mailable_class'  => $data['mailable_class'] ?? null,
            'view'            => $data['view'] ?? null,
            'default_subject' => $data['default_subject'] ?? null,
            'active'          => $data['active'],
        ]);

        $decodedJson = $this->decodeJsonField($data['default_data'] ?? null);

        /**
         * Reguli:
         * - dacă textarea e goală => ștergem default_data (setăm null)
         * - dacă textarea are conținut și JSON e valid => îl suprascriem
         * - dacă textarea are conținut și JSON e invalid => lăsăm valoarea veche
         */
        if ($request->filled('default_data')) {
            if ($decodedJson !== null) {
                $template->default_data = $decodedJson;
            }
        } else {
            $template->default_data = null;
        }

        $template->save();

        $flash = [
            'success' => __('Template-ul a fost actualizat cu succes.'),
        ];

        if ($request->filled('default_data') && $decodedJson === null) {
            $flash['warning'] = __('Datele JSON nu sunt valide, câmpul „Date implicite” a fost lăsat neschimbat.');
        }

        return redirect()
            ->route('graphmail.templates.index')
            ->with($flash);
    }

    public function destroy(MailTemplate $template)
    {
        $template->delete();

        return redirect()
            ->route('graphmail.templates.index')
            ->with('success', __('Template-ul a fost șters cu succes.'));
    }

    protected function validateData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'key'             => [
                'required',
                'string',
                'max:255',
                Rule::unique('mail_templates', 'key')->ignore($id),
            ],
            'name'            => ['required', 'string', 'max:255'],
            'module'          => ['nullable', 'string', 'max:255'],
            'mailable_class'  => ['nullable', 'string', 'max:255'],
            'view'            => ['required', 'string', 'max:255'],
            'to'              => ['nullable', 'string', 'max:255'],
            'cc'              => ['nullable', 'string', 'max:255'],
            'bcc'             => ['nullable', 'string', 'max:255'],
            'default_subject' => ['nullable', 'string', 'max:255'],
            'default_data'    => ['nullable', 'string'], // JSON în textarea
            'active'          => ['nullable', 'boolean'],
        ]);
    }

    protected function decodeJsonField(?string $value): ?array
    {
        if (!$value) {
            return null;
        }

        $decoded = json_decode($value, true);

        // dacă nu e JSON valid, îl lăsăm null sau poți arunca ValidationException
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $decoded;
    }

    /**
     * Transformă un string de forma
     * "a@example.com, b@example.com" într-un array
     * pentru coloanele JSON.
     */
    protected function parseEmailList(?string $value): ?array
    {
        if ($value === null) {
            return null;
        }

        $parts = array_filter(array_map(function ($item) {
            $item = trim($item);
            return $item !== '' ? $item : null;
        }, explode(',', $value)));

        if (empty($parts)) {
            return null;
        }

        return array_values(array_unique($parts));
    }

    /**
     * Shared logic for create/edit views to avoid duplication.
     */
    protected function formView(string $view, MailTemplate $template)
    {
        $isEdit = $template->exists;

        // default_data JSON
        $jsonValue = old('default_data');
        if ($jsonValue === null && $template->default_data) {
            $jsonValue = json_encode(
                $template->default_data,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            );
        }

        // For to/cc/bcc: turn arrays back into comma-separated strings
        $toValue = old('to');
        if ($toValue === null && is_array($template->to)) {
            $toValue = implode(', ', $template->to);
        }

        $ccValue = old('cc');
        if ($ccValue === null && is_array($template->cc)) {
            $ccValue = implode(', ', $template->cc);
        }

        $bccValue = old('bcc');
        if ($bccValue === null && is_array($template->bcc)) {
            $bccValue = implode(', ', $template->bcc);
        }

        [$inputClass, $textareaClass] = $this->formFieldClasses();

        return view($view, compact(
            'template',
            'isEdit',
            'inputClass',
            'textareaClass',
            'jsonValue',
            'toValue',
            'ccValue',
            'bccValue'
        ));
    }

    /**
     * Centralized Tailwind classes for inputs and text areas to keep them in sync.
     */
    protected function formFieldClasses(): array
    {
        $inputClass = 'block w-full rounded-xl
                       border border-gray-300 dark:border-gray-700
                       bg-white dark:bg-gray-900/80
                       text-sm text-gray-900 dark:text-gray-100
                       px-3 py-2
                       shadow-sm
                       focus:border-indigo-500 focus:ring-indigo-500';

        $textareaClass = 'flex-1 block w-full rounded-xl
                          border border-gray-300 dark:border-gray-700
                          bg-white dark:bg-gray-900/80
                          text-xs font-mono leading-relaxed
                          px-3 py-3
                          shadow-sm
                          focus:border-indigo-500 focus:ring-indigo-500';

        return [$inputClass, $textareaClass];
    }
}
