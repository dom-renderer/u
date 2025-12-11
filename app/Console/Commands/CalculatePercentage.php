<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CalculatePercentage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calcualte:percentage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate Percentage';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        \App\Models\ChecklistTask::whereIn('status', [2, 3])->where(function ($builder) {
            $builder->whereNull('extra_info')
            ->orWhere('extra_info', '{}')
            ->orWhere('extra_info', '[]')
            ->orWhere('extra_info', '');
        })
        ->chunk(1000, function ($tasks) {
            $lastTaskId = null;
            
                foreach ($tasks as $task) {
                    $metaData = [];

                    if (!empty($task->data)) {
                        $lastTaskId = $task->id;

                        try {
                            $pointsQuestionVariants = \App\Helpers\Helper::categorizePoints($task->data);

                            $theForm = \App\Helpers\Helper::getVersionForm($task->version_id);
                            $metaData['total_question'] = \App\Helpers\Helper::getTotalQuestionsCount($theForm);
                            $metaData['point_positive'] = count($pointsQuestionVariants['positive']);
                            $metaData['point_negative'] = count($pointsQuestionVariants['negative']);
                            $metaData['point_na'] = count($pointsQuestionVariants['na']);
                            $metaData['point_valid'] = count(\App\Helpers\Helper::selectPointsQuestions($task->data)) - $metaData['point_na'];
                            $metaData['point_failed'] = abs(count(array_column($pointsQuestionVariants['negative'], 'value')));
                            $metaData['point_achieved'] = array_sum(array_filter(array_column($pointsQuestionVariants['positive'], 'value')));

                            if ($metaData['point_valid'] > 0) {
                                $percentage = number_format(($metaData['point_achieved'] / $metaData['point_valid']) * 100, 2);
                            } else {
                                $percentage = 0;
                            }

                            $task->update([
                                'percentage' => $percentage,
                                'extra_info' => $metaData
                            ]);

                            $this->info("Task {$task->id} updated : " . json_encode($metaData) . ' and percentage ' . $percentage . '%');

                        } catch (\Exception $e) {
                            try {
                                $this->error($e->getMessage() . " Task Number : {$lastTaskId}");

                                \App\Models\Error::updateOrCreate([
                                    'title' => 'App\Console\Commands\CalculatePercentage',
                                    'description' => $e->getMessage() . ' line ' . $e->getLine(),
                                    'ids' => [$lastTaskId]
                                ]);
                                
                            } catch (\Exception $inner) {
                                \Log::error('Failed to log error: ' . $inner->getMessage(), [
                                    'original_error' => $e->getMessage(),
                                    'task_id' => $lastTaskId,
                                ]);
                            }
                        }
                    }
                }

            sleep(1);
        });
        
    }
}
