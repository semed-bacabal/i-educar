<?php

namespace iEducar\Modules\Educacenso\Validator;

use App\Models\Educacenso\Registro20;
use iEducar\Modules\Educacenso\Model\FormaOrganizacaoTurma as ModelFormaOrganizacaoTurma;

class FormaOrganizacaoTurma implements EducacensoValidator
{
    private $turma;

    public function __construct(Registro20 $turma)
    {
        $this->turma = $turma;
    }

    public function isValid(): bool
    {
        if (empty($this->turma->formasOrganizacaoTurma)) {
            return true;
        }

        if (empty($this->turma->etapaEducacenso)) {
            return true;
        }

        if (in_array($this->turma->etapaEducacenso, [1, 2, 3, 24])) {
            return true;
        }

        $validOptionForEducacensoStage = [
            ModelFormaOrganizacaoTurma::SERIE_ANO => [
                14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 25, 26, 27, 28, 29, 35, 36, 37, 38, 39, 40, 41, 56, 64, 68, 69, 70, 71, 72, 73, 74,
            ],
            ModelFormaOrganizacaoTurma::SEMESTRAL => [
                25, 26, 27, 28, 29, 35, 36, 37, 38, 39, 40, 64, 68, 69, 70, 71, 72, 73, 74,
            ],
            ModelFormaOrganizacaoTurma::CICLOS => [
                14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 25, 26, 27, 28, 29, 35, 36, 37, 38, 41, 56,
            ],
            ModelFormaOrganizacaoTurma::NAO_SERIADO => [
                14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 41, 56, 25, 26, 27, 28, 29, 35, 36, 37, 38, 39, 40, 64, 68, 69, 70, 71, 72, 73, 74,
            ],
            ModelFormaOrganizacaoTurma::MODULES => [
                14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 41, 25, 26, 27, 28, 29, 35, 36, 37, 38, 39, 40, 64, 56, 68, 69, 70, 71, 72, 73, 74,
            ],
        ];

        return in_array($this->turma->etapaEducacenso, $validOptionForEducacensoStage[$this->turma->formasOrganizacaoTurma]);
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return 'Teste';
    }
}
