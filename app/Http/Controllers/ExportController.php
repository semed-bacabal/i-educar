<?php

namespace App\Http\Controllers;

use App\Jobs\DatabaseToCsvExporter;
use App\Models\Exporter\Employee;
use App\Models\Exporter\Enrollment;
use App\Models\Exporter\Export;
use App\Models\Exporter\SocialAssistance;
use App\Models\Exporter\Stage;
use App\Models\Exporter\Student;
use App\Models\Exporter\Teacher;
use App\Process;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExportController extends Controller
{
    /**
     * @return View
     */
    public function index(Request $request)
    {
        $this->breadcrumb('Exportações', [
            url('/intranet/educar_configuracoes_index.php') => 'Configurações',
        ]);

        $this->menu(Process::DATA_EXPORT);

        $query = Export::query();

        $query->where('user_id', $request->user()->getKey())
            ->orderByDesc('created_at');

        return view('export.index', [
            'exports' => $query->paginate(),
        ]);
    }

    /**
     * @return View
     */
    public function form(Request $request, Export $export)
    {
        $limit = config('export.limit');

        $count = Export::query()
            ->where('user_id', $request->user()->getKey())
            ->whereNull('url')
            ->where('created_at', '>', now()->subMinutes(30))
            ->count();

        if ($count >= $limit) {
            return redirect()->route('export.index')
                ->withErrors(['Error' => 'Você já possui ' . $limit . ' exportação(ões) pendente(s) e não é possível iniciar uma nova exportação. Por favor, aguarde a conclusão de uma delas antes de iniciar uma nova.']);
        }

        $this->breadcrumb('Nova Exportação', [
            url('/intranet/educar_configuracoes_index.php') => 'Configurações',
            route('export.index') => 'Exportações',
        ]);

        $this->menu(Process::DATA_EXPORT);

        return view('export.new', [
            'export' => $export,
            'exportation' => $export->getExportByCode(
                $request->query('type', 1)
            ),
        ]);
    }

    /**
     * @return RedirectResponse
     */
    public function export(Request $request)
    {
        if (empty($request->filled(['agree']))) {
            return redirect()->route('export.form');
        }

        if (empty($request->all()['fields'])) {
            return redirect('/exportacoes/novo')
                ->withErrors(['Error' => ['Selecione ao menos uma informação que deseja exportar para continuar.']]);
        }

        $export = Export::create(
            $this->filter($request)
        );

        $this->dispatch(
            new DatabaseToCsvExporter($export)
        );

        return redirect()->route('export.index');
    }

    /**
     * @return array
     */
    protected function filter(Request $request)
    {
        $data = $request->merge([
            'hash' => md5(time()),
            'user_id' => $request->user()->getKey(),
        ])->only([
            'model', 'fields', 'hash', 'user_id',
        ]);

        $model = $data['model'];

        if ($model === Student::class) {
            $data = $this->filterStudentEnrrolments($request, $data, 'exporter_student_grouped_registration', 'alunos');
        }

        if ($model === Enrollment::class) {
            $data = $this->filterStudentEnrrolments($request, $data, 'exporter_student', 'matriculas');
        }

        if ($model === Teacher::class) {
            $data = $this->filterTeachers($request, $data);
        }

        if ($model === SocialAssistance::class) {
            $data = $this->filterStudentEnrrolments($request, $data, 'exporter_social_assistance', 'assistencia_social');
        }

        if ($model === Stage::class) {
            $data = $this->filterStages($request, $data);
        }

        if ($model === Employee::class) {
            $data = $this->filterEmployees($request, $data);
        }

        return $data;
    }

    protected function filterStudentEnrrolments(Request $request, $data, $table, $fileName)
    {
        $data['filename'] = $this->buildFileName($fileName);

        if ($status = $request->input('situacao_matricula')) {
            $data['filters'][] = [
                'column' => $table . '.status',
                'operator' => '=',
                'value' => $status,
            ];
        }

        if ($year = $request->input('ano')) {
            $data['filters'][] = [
                'column' => $table . '.year',
                'operator' => '=',
                'value' => intval($year),
            ];
        }
        if ($request->input('ref_cod_escola')) {
            if ($request->get('status') == 2) {
                $data['filters'][] = [
                    'column' => $table . '.school_filter_id',
                    'operator' => '@>',
                    'value' => $request->input('ref_cod_escola'),
                ];
            } else {
                $data['filters'][] = [
                    'column' => $table . '.school_id',
                    'operator' => 'in',
                    'value' => [$request->input('ref_cod_escola')],
                ];
            }
        } elseif ($request->user()->isSchooling()) {
            if ($request->get('status') == 2) {
                $data['filters'][] = [
                    'column' => $table . '.school_filter_id',
                    'operator' => '@>',
                    'value' => $request->user()->schools->pluck('cod_escola')->all(),
                ];
            } else {
                $data['filters'][] = [
                    'column' => $table . '.school_id',
                    'operator' => 'in',
                    'value' => $request->user()->schools->pluck('cod_escola')->all(),
                ];
            }
        }

        return $data;
    }

    /**
     * @param array   $data
     * @return array
     */
    public function filterTeachers(Request $request, $data)
    {
        $data['filename'] = 'professores.csv';

        if ($year = $request->input('ano')) {
            $data['filters'][] = [
                'column' => 'exporter_teacher.year',
                'operator' => '=',
                'value' => intval($year),
            ];
        }

        if ($request->input('ref_cod_escola')) {
            $data['filters'][] = [
                'column' => 'exporter_teacher.school_id',
                'operator' => 'in',
                'value' => [$request->input('ref_cod_escola')],
            ];
        } elseif ($request->user()->isSchooling()) {
            $data['filters'][] = [
                'column' => 'exporter_teacher.school_id',
                'operator' => 'in',
                'value' => $request->user()->schools->pluck('cod_escola')->all(),
            ];
        }

        return $data;
    }

    /**
     * @param array   $data
     * @return array
     */
    public function filterEmployees(Request $request, $data)
    {
        $data['filename'] = 'servidores.csv';

        if ($year = $request->input('ano')) {
            $data['filters'][] = [
                'column' => 'exporter_employee.year_id',
                'operator' => '@>',
                'value' => (int) $year,
            ];
        }

        if ($request->input('ref_cod_escola')) {
            $data['filters'][] = [
                'column' => 'exporter_employee.school_id',
                'operator' => '@>',
                'value' => $request->input('ref_cod_escola'),
            ];
        } elseif ($request->user()->isSchooling()) {
            $data['filters'][] = [
                'column' => 'exporter_employee.school_id',
                'operator' => '@>',
                'value' => $request->user()->schools->pluck('cod_escola'),
            ];
        }

        return $data;
    }

    /**
     * @param array   $data
     * @return array
     */
    public function filterStages(Request $request, $data)
    {
        $data['filename'] = 'calendario.csv';

        if ($year = $request->input('ano')) {
            $data['filters'][] = [
                'column' => 'exporter_stages.year',
                'operator' => '=',
                'value' => intval($year),
            ];
        }

        if ($request->input('ref_cod_escola')) {
            $data['filters'][] = [
                'column' => 'exporter_stages.school_id',
                'operator' => 'in',
                'value' => [$request->input('ref_cod_escola')],
            ];
        } elseif ($request->user()->isSchooling()) {
            $data['filters'][] = [
                'column' => 'exporter_stages.school_id',
                'operator' => 'in',
                'value' => $request->user()->schools->pluck('cod_escola')->all(),
            ];
        }

        return $data;
    }

    private function buildFileName($fileName): string
    {
        return str_replace(' ', '_', $fileName . '_'. Carbon::now()->toDateTimeString() .  '.csv');
    }
}
