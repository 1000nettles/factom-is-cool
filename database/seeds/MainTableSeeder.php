<?php

use Illuminate\Database\Seeder;
use App\Models\Entities\Process;
use App\Models\Entities\Command;
use App\Models\Entities\CommandsParam;
use App\Models\Services\MainService;

class MainTableSeeder extends Seeder
{

    /**
     * All Factom processes
     *
     * @var array
     */
    protected $processes    = [
        'factomd'
    ];

    /**
     * All of the commands for the factomd process and their params
     *
     * @var array
     */
    protected $factomdCommands = [
        'directory-block'   => ['KeyMR'],
        'directory-block-head'  => null,
        'heights'   => null,
        'raw-data' => ['Hash'],
        'dblock-by-height' => ['Height'],
        'ablock-by-height' => ['Height'],
        'ecblock-by-height' => ['Height'],
        'fblock-by-height' => ['Height'],
        'factoid-block' => ['KeyMR'],
        'entrycredit-block' => ['KeyMR'],
        'admin-block' => ['KeyMR'],
        'entry-block' => ['KeyMR'],
        'entry' => ['Hash'],
        'pending-entries' => null,
        'transaction' => ['Hash'],
        'ack' => ['Hash', 'ChainID', 'FullTransaction'],
        'factoid-ack' => ['TxID'],
        'entry-ack' => ['TxID'],
        'receipt' => ['Hash'],
        'pending-transactions' => ['Address'],
        'chain-head' => ['ChainID'],
        'entry-credit-balance' => ['Address'],
        'factoid-balance' => ['Address'],
        'entry-credit-rate' => null,
        'properties' => null,
        'factoid-submit' => ['Transaction'],
        'commit-chain' => ['Message'],
        'reveal-chain' => ['Entry'],
        'commit-entry' => ['Message'],
        'reveal-entry' => ['Entry'],
        'send-raw-message' => ['Message']
    ];

    /**
     * Run all the database seeds.
     *
     * @throws Exception
     */
    public function run()
    {
        foreach ($this->processes as $process) {
            $processModel = new Process();
            $processModel->name = $process;
            $processModel->save();
        }

        $mainService = new MainService();
        $factomdProcessId = $mainService->getFactomdProcessId();

        foreach ($this->factomdCommands as $rawCommandKey => $rawCommandValue) {
            $command = new Command();
            $command->process_id = $factomdProcessId;
            $command->identifier = $rawCommandKey;
            $command->save();

            if ($rawCommandValue && is_array($rawCommandValue)) {
                foreach ($rawCommandValue as $rawCommandsParam) {
                    $commandsParam = new CommandsParam();
                    $commandsParam->command_id = $command->id;
                    $commandsParam->identifier = $rawCommandsParam;
                    $commandsParam->save();
                }
            }
        }
    }

}
