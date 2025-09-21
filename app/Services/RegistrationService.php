<?php

namespace App\Services;

use App\Models\LegacyEnrollment;
use App\Models\LegacyRegistration;
use App\Models\LegacySchoolClass;
use App\Models\LegacySchoolClassGrade;
use App\Models\LegacyTransferRequest;
use App\User;
use App_Model_MatriculaSituacao;
use Avaliacao_Model_NotaComponenteMediaDataMapper;
use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class RegistrationService
{
    /**
     * @var User
     */
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return Collection
     */
    public function findAll(array $ids)
    {
        return LegacyRegistration::query()
            ->whereIn('cod_matricula', $ids)
            ->get();
    }

    /**
     * @param LegacySchoolClass $schoolClass
     * @return Collection
     */
    public function getRegistrationsNotEnrolled($schoolClass)
    {
        $grades = [
            $schoolClass->grade_id,
        ];

        if ($schoolClass->multiseriada == 1) {
            $grades = LegacySchoolClassGrade::query()
                ->where('turma_id', $schoolClass->getKey())
                ->get()
                ->pluck('serie_id')
                ->toArray();
        }

        return LegacyRegistration::query()
            ->with('student.person', 'lastEnrollment')
            ->whereIn('ref_ref_cod_serie', $grades)
            ->where('ref_ref_cod_escola', $schoolClass->school_id)
            ->where('ativo', 1)
            ->where('ultima_matricula', 1)
            ->where('ano', $schoolClass->year)
            ->whereIn('aprovado', [1, 2, 3])
            ->whereDoesntHave('enrollments', function ($query) use ($schoolClass) {
                /** @var Builder $query */
                $query->where('ativo', 1);
                $query->whereHas('schoolClass', function ($query) use ($schoolClass) {
                    /** @var Builder $query */
                    $query->where('ref_ref_cod_escola', $schoolClass->school_id);
                    $query->where('ativo', 1);
                });
            })
            ->get();
    }

    /**
     * Atualiza a situação de uma matrícula
     *
     * @param array              $data
     */
    public function updateStatus(LegacyRegistration $registration, $data)
    {
        $status = $data['nova_situacao'];

        $registration->aprovado = $status;
        $registration->save();

        $this->checkUpdatedStatusAction($data, $registration);
    }

    /**
     * @param array              $data
     */
    private function checkUpdatedStatusAction($data, LegacyRegistration $registration)
    {
        $newStatus = $data['nova_situacao'];

        switch ($newStatus) {
            case App_Model_MatriculaSituacao::TRANSFERIDO:
                $this->markEnrollmentsAsTransferred($registration);
                $this->createTransferRequest(
                    $data['transferencia_data'],
                    $data['transferencia_tipo'],
                    $data['transferencia_observacoes'],
                    $registration
                );
                $registration->data_cancel = DateTime::createFromFormat('d/m/Y', $data['transferencia_data']);
                $registration->saveOrFail();
                $this->processDisciplineScoreSituation($registration, $newStatus);
                break;

            case App_Model_MatriculaSituacao::RECLASSIFICADO:
                $this->markEnrollmentsAsReclassified($registration);
                break;

            case App_Model_MatriculaSituacao::ABANDONO:
                $this->markEnrollmentsAsAbandoned($registration);
                $this->processDisciplineScoreSituation($registration, $newStatus);
                break;

            case App_Model_MatriculaSituacao::FALECIDO:
                $this->markEnrollmentsAsDeceased($registration);
                $this->processDisciplineScoreSituation($registration, $newStatus);
                break;
        }
    }

    private function processDisciplineScoreSituation(LegacyRegistration $registration, $newStatus)
    {
        $registrationScoreId = $registration->registrationStores()->value('id');

        if ($registrationScoreId) {
            (new Avaliacao_Model_NotaComponenteMediaDataMapper)->updateSituation($registrationScoreId, $newStatus);
        }
    }

    private function markEnrollmentsAsTransferred(LegacyRegistration $registration)
    {
        foreach ($this->getActiveEnrollments($registration) as $enrollment) {
            app(EnrollmentService::class)->markAsTransferred($enrollment);
        }
    }

    private function markEnrollmentsAsReclassified(LegacyRegistration $registration)
    {
        foreach ($this->getActiveEnrollments($registration) as $enrollment) {
            app(EnrollmentService::class)->markAsReclassified($enrollment);
        }
    }

    private function markEnrollmentsAsAbandoned(LegacyRegistration $registration)
    {
        foreach ($this->getActiveEnrollments($registration) as $enrollment) {
            app(EnrollmentService::class)->markAsAbandoned($enrollment);
        }
    }

    private function markEnrollmentsAsDeceased(LegacyRegistration $registration)
    {
        foreach ($this->getActiveEnrollments($registration) as $enrollment) {
            app(EnrollmentService::class)->markAsDeceased($enrollment);
        }
    }

    /**
     * @return LegacyEnrollment
     */
    private function getActiveEnrollments(LegacyRegistration $registration)
    {
        return $registration->activeEnrollments;
    }

    /**
     * @param string             $date
     * @param int            $type
     * @param string             $comments
     * @param LegacyRegistration $registration
     */
    private function createTransferRequest($date, $type, $comments, $registration)
    {
        LegacyTransferRequest::create([
            'ref_cod_transferencia_tipo' => $type,
            'ref_usuario_cad' => $this->user->getKey(),
            'ref_cod_matricula_saida' => $registration->getKey(),
            'observacao' => $comments,
            'data_cadastro' => now(),
            'ativo' => 1,
            'data_transferencia' => DateTime::createFromFormat('d/m/Y', $date),
        ]);
    }

    /**
     * Atualiza a data de entrada de uma matrícula
     */
    public function updateRegistrationDate(LegacyRegistration $registration, DateTime $date)
    {
        $date = $date->format('Y-m-d');

        $registration->data_matricula = $date;
        $registration->save();

        return $registration;
    }

    /**
     * Atualiza a date de enturmação de todas as enturmações de uma matrícula
     */
    public function updateEnrollmentsDate(LegacyRegistration $registration, DateTime $date, bool $ignoreRelocation)
    {
        $date = $date->format('Y-m-d');

        foreach ($registration->enrollments as $enrollment) {
            if ($ignoreRelocation === false && $enrollment->remanejado) {
                continue;
            }

            $enrollment->data_enturmacao = $date;
            $enrollment->save();

            $sequencial = $enrollment->sequencial;
            $this->processEnrollmentsDates($registration, $sequencial, $date);
        }
    }

    private function processEnrollmentsDates(LegacyRegistration $registration, int $sequencial, string $date): void
    {
        $allEnrollments = $registration->enrollments()->get();

        $nextEnrollments = $allEnrollments->filter(
            fn ($item) => $item->sequencial > $sequencial
        );

        $previousEnrollments = $allEnrollments->filter(
            fn ($item) => $item->sequencial < $sequencial
        );

        $this->updateNextEnrollmentsRegistrationDate($nextEnrollments, $date);
        $this->updatePreviousEnrollmentsRegistrationDate($previousEnrollments, $date);
    }

    private function updateNextEnrollmentsRegistrationDate(Collection $nextEnrollments, $date)
    {
        foreach ($nextEnrollments as $enrollment) {
            if (strtotime($enrollment->data_enturmacao->format('Y-m-d')) < strtotime($date)) {
                $enrollment->data_enturmacao = $date;
            }

            if ($enrollment->data_exclusao !== null &&
                strtotime($enrollment->data_exclusao->format('Y-m-d')) < strtotime($date)
            ) {
                $enrollment->data_exclusao = $date;
            }

            $enrollment->save();
        }
    }

    private function updatePreviousEnrollmentsRegistrationDate(Collection $previousEnrollments, string $date)
    {
        foreach ($previousEnrollments as $enrollment) {
            if (strtotime($enrollment->data_enturmacao->format('Y-m-d')) > strtotime($date)) {
                $enrollment->data_enturmacao = $date;
            }

            if ($enrollment->data_exclusao !== null &&
                strtotime($enrollment->data_exclusao->format('Y-m-d')) > strtotime($date)) {
                $enrollment->data_exclusao = $date;
            }

            $enrollment->save();
        }
    }

    /**
     * Atualiza a data de saida de uma matrícula
     *
     *
     * @return LegacyRegistration
     */
    public function updateCancelDate(LegacyRegistration $registration, DateTime $date)
    {
        $date = $date->format('Y-m-d');

        $registration->data_cancel = $date;
        $registration->save();

        return $registration;
    }
}
