<?php

namespace App\Traits;

use App\Models\GroupTask;
use App\Models\OutlineAgreementHasGroupTask;
use App\Models\Task;

trait MapTaskNew
{
    public static function mapTasks($primaryKey, $id, $groupTasks, $firstRelatedModelNamespace)
    {
        $firstRelatedModel = app($firstRelatedModelNamespace);

        $firstRelationModelDone = [];

        // TODO: $groupTasks = ['group_task_id' (nullable),'tasks']
        foreach ($groupTasks as $group_task => $item) {

            $tasks = $item['tasks'];

            if (isset($item['group_task_id'])) {
                // existing group task -> update
                $groupTask = GroupTask::find($item['group_task_id']);
            } else {
                // new group task -> create
                $groupTask = GroupTask::create([
                    'group_task_name' => $group_task,
                    'status'          => GroupTask::ACTIVE,
                ]);
            }

            $relatedModelQuery = $firstRelatedModel::where($primaryKey, $id)->where('group_task_id', $groupTask->id)->first();

            if (isset($relatedModelQuery)) {
                $relatedModelQuery->update([
                    'actual_qty'   => $groupTask->qty ?? 1,
                    'actual_price' => $groupTask->unit_price ?? 0,
                    'position'     => $groupTask->position ?? null,
                ]);
                $relatedModelQuery->refresh();
                $firstRelatedModelRecord = $relatedModelQuery;
            } else {
                $firstRelatedModelRecord = $firstRelatedModel::create([
                    $primaryKey     => $id,
                    'group_task_id' => $groupTask->id,
                    'actual_qty'    => $groupTask->qty ?? 1,
                    'actual_price'  => $groupTask->unit_price ?? 0,
                    'position'      => $groupTask->position ?? null,
                    'status'        => $firstRelatedModel::ACTIVE,
                ]);

            }

            $firstRelationModelDone[] = $firstRelatedModelRecord->id;

            foreach ($tasks as $task) {
                if (isset($task['id'])) {
                    Task::updateOrCreate([
                        'group_task_id' => $groupTask->id,
                        'id'            => $task['id'],
                    ], [
                        'name'        => $task['name'],
                        'task_no'     => isset($task['task_no']) ? $task['task_no'] : null,
                        'qty'         => isset($task['qty']) ? $task['qty'] : null,
                        'unit'        => isset($task['unit']) ? $task['unit'] : null,
                        'unit_price'  => isset($task['unit_price']) ? $task['unit_price'] : null,
                        'total_price' => $task['qty'] * $task['unit_price'] ?? null,
                        'status'      => Task::ACTIVE,
                    ]);
                } else {
                    Task::create([
                        'group_task_id' => $groupTask->id,
                        'name'          => $task['name'],
                        'task_no'       => isset($task['task_no']) ? $task['task_no'] : null,
                        'qty'           => isset($task['qty']) ? $task['qty'] : null,
                        'unit'          => isset($task['unit']) ? $task['unit'] : null,
                        'unit_price'    => isset($task['unit_price']) ? $task['unit_price'] : null,
                        'total_price'   => $task['qty'] * $task['unit_price'] ?? null,
                        'status'        => Task::ACTIVE,
                    ]);
                }
            }
        }

        // Handle delete case
        $firstRelatedModel::where($primaryKey, $id)->whereNotIn('id', $firstRelationModelDone)->forceDelete();
    }
}
