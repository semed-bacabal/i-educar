<?php

namespace App\Models;

use App\Contracts\Enum;

class RegistrationStatus implements Enum
{
    public const APPROVED = 1;

    public const REPROVED = 2;

    public const ONGOING = 3;

    public const TRANSFERRED = 4;

    public const RECLASSIFIED = 5;

    public const ABANDONED = 6;

    public const IN_EXAM = 7;

    public const APPROVED_PAST_EXAM = 8;

    public const APPROVED_WITHOUT_EXAM = 10;

    public const PRE_REGISTRATION = 11;

    public const APPROVED_WITH_DEPENDENCY = 12;

    public const APPROVED_BY_BOARD = 13;

    public const REPROVED_BY_ABSENCE = 14;

    public const DECEASED = 15;

    /**
     * Situações das enturmações
     *
     * @return array<int, string>
     */
    public function getDescriptiveValues(): array
    {
        return [
            self::APPROVED => 'Aprovado',
            self::REPROVED => 'Retido',
            self::ONGOING => 'Cursando',
            self::TRANSFERRED => 'Transferido',
            self::RECLASSIFIED => 'Reclassificado',
            self::ABANDONED => 'Deixou de Frequentar',
            self::IN_EXAM => 'Em exame',
            self::APPROVED_PAST_EXAM => 'Aprovado após exame',
            self::APPROVED_WITHOUT_EXAM => 'Aprovado sem exame',
            self::PRE_REGISTRATION => 'Pré-matrícula',
            self::APPROVED_WITH_DEPENDENCY => 'Aprovado com dependência',
            self::APPROVED_BY_BOARD => 'Aprovado pelo conselho',
            self::REPROVED_BY_ABSENCE => 'Reprovado por faltas',
            self::DECEASED => 'Falecido',
        ];
    }

    /**
     * @return array<int, int>
     */
    public static function getStatusInactive(): array
    {
        return [
            self::ABANDONED,
            self::TRANSFERRED,
            self::DECEASED,
        ];
    }

    /**
     * Situações da matrícula com as situações da enturmação
     *
     * @return string[]
     */
    public static function getRegistrationAndEnrollmentStatus(): array
    {
        $values = (new self)->getDescriptiveValues();

        $values[self::REPROVED] = 'Reprovado'; // Situação "Reprovado" da matrícula

        return $values;
    }

    /**
     * Situações da matrícula
     *
     * @return string[]
     */
    public static function getRegistrationStatus(): array
    {
        return [
            self::APPROVED => 'Aprovado',
            self::REPROVED => 'Reprovado',
            self::ONGOING => 'Cursando',
            self::TRANSFERRED => 'Transferido',
            self::RECLASSIFIED => 'Reclassificado',
            self::ABANDONED => 'Deixou de Frequentar',
            self::APPROVED_WITH_DEPENDENCY => 'Aprovado com dependência',
            self::APPROVED_BY_BOARD => 'Aprovado pelo conselho',
            self::REPROVED_BY_ABSENCE => 'Reprovado por faltas',
            self::DECEASED => 'Falecido',
        ];
    }
}
