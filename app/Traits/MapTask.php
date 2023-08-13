<?php

namespace App\Traits;

use App\Models\GroupTask;
use App\Models\Task;

trait MapTask
{
    public static function mapTasks($primaryKey, $id, $groupTasks, $firstRelatedModelNamespace, $secondRelationModelNamespace)
    {
        // Minions TODO: update logic if in need
        $firstRelatedModel   = app($firstRelatedModelNamespace);
        $secondRelationModel = app($secondRelationModelNamespace);

        $firstRelationModelDone  = [];
        $secondRelationModelDone = [];
        foreach ($groupTasks as $group_task => $tasks) {
            $groupTask = GroupTask::firstOrCreate([
                'name' => $group_task
            ], [
                'status' => GroupTask::ACTIVE
            ]);

            // Minions TODO: Update model after updated DB
            $firstRelatedModelRecord = $firstRelatedModel::updateOrCreate([
                $primaryKey     => $id,
                'group_task_id' => $groupTask->id
            ]);

            $firstRelationModelDone[] = $firstRelatedModelRecord->id;
            foreach ($tasks as $task) {
                $task = Task::firstOrCreate([
                    'group_task_id' => $groupTask->id,
                    'name'          => $task['name']
                ], [
                    'status' => Task::ACTIVE
                ]);

                // Minions TODO: Update model after updated DB
                $secondRelationModelRecord = $secondRelationModel::updateOrCreate([
                    $primaryKey     => $id,
                    'group_task_id' => $groupTask->id,
                    'task_id'       => $task->id
                ], [
                    'qty'         => $task['qty'],
                    'total_price' => $task['price'],
                    'unit_price'  => $task['price'] / $task['qty'],
                ]);

                $secondRelationModelDone[] = $secondRelationModelRecord->id;
            }
        }

        // Handle delete case
        $firstRelatedModel::where($primaryKey, $id)->whereNotIn('id', $firstRelationModelDone)->delete();
        $secondRelationModel::where($primaryKey, $id)->whereNotIn('id', $secondRelationModelDone)->delete();
    }
}
