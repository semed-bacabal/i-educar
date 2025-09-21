<?php

namespace iEducar\Packages\Educacenso\Jobs;

use App\Models\EducacensoImport as EducacensoImportModel;
use DateTime;
use iEducar\Packages\Educacenso\Services\ImportServiceFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Throwable;

class EducacensoImportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var EducacensoImportModel
     */
    private $educacensoImport;

    /**
     * @var array
     */
    private $importArray;

    /**
     * @var string
     */
    private $databaseConnection;

    /**
     * @var DateTime
     */
    private $registrationDate;

    public $timeout = 600;

    /**
     * Create a new job instance.
     *
     * @param string                $databaseConnection
     * @param DateTime              $registrationDate
     */
    public function __construct(EducacensoImportModel $educacensoImport, $importArray, $databaseConnection, $registrationDate)
    {
        $this->educacensoImport = $educacensoImport;
        $this->importArray = $importArray;
        $this->databaseConnection = $databaseConnection;
        $this->registrationDate = $registrationDate;
    }

    /**
     * Execute the job.
     *
     * @return void
     *
     * @throws Throwable
     */
    public function handle(): void
    {
        DB::setDefaultConnection($this->databaseConnection);
        DB::beginTransaction();

        try {
            $importService = ImportServiceFactory::createImportService($this->educacensoImport->year, $this->registrationDate);
            $importService->import($this->importArray, $this->educacensoImport->user);
            $importService->adaptData();
        } catch (Throwable $e) {
            DB::rollBack();

            throw $e;
        }

        $educacensoImport = $this->educacensoImport;
        $educacensoImport->finished = true;
        $educacensoImport->save();

        DB::commit();
    }

    public function tags()
    {
        return [
            $this->databaseConnection,
            'educacenso-import',
        ];
    }
}
