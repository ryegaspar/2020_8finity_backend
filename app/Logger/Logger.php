<?php

namespace App\Logger;

use App\Models\Log;
use Illuminate\Support\Facades\Auth;

class Logger
{
    private $model;
    private $model_name;
    private $action;
    private $loggable_fields;
    private $loggable_actions;
    private $should_log = true;
    private $user;

    public function __construct($model, $action)
    {
        $this->model = $model;
        $this->action = $action;

        $this->model_name = get_class($model);
        $this->loggable_actions = $model->loggable_actions;
        $this->loggable_fields = $model->loggable_fields;
        $this->user = auth('admin')->user();

        $this->prepareData();
    }

    public function record()
    {
        if (! $this->should_log) {
            return;
        }

        if ($this->action === 'updated' &&
            empty(array_intersect_key($this->model->getChanges(), array_flip($this->loggable_fields)))) {
            return;
        }

        $this->logAction();
    }

    private function prepareData()
    {
        if ((is_array($this->loggable_actions) && ! in_array($this->action, $this->loggable_actions))
            || (is_string($this->loggable_actions) && $this->loggable_actions !== $this->action)
        ) {
            $this->should_log = false;
        }
    }

    private function logAction()
    {
        $data = null;

        if ($this->action === 'created' || $this->action === 'deleted') {
            $data = array_intersect_key($this->model->getAttributes(), array_flip($this->loggable_fields));
        } elseif ($this->action === 'updated') {
            $data = [
                'before' => array_intersect_key(
                    $this->model->getOriginal(),
                    array_intersect_key($this->model->getChanges(), array_flip($this->loggable_fields))
                ),
                'after'  => array_intersect_key(
                    $this->model->getChanges(),
                    array_flip($this->loggable_fields)
                ),
            ];
        }

        $this->model->logs()->create([
            'admin_id' => auth('admin')->user()?->id,
            'action'   => $this->action,
            'changes'  => json_encode($data)
        ]);
    }
}
