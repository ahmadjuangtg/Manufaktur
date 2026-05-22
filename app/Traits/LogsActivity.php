<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait LogsActivity
{
    protected static function bootLogsActivity()
    {
        static::created(function ($model) {
            static::logActivity($model, 'CREATED', 'Membuat ' . $model->getSubjectName() . ' baru');
        });

        static::updated(function ($model) {
            // Get dirty changes
            $dirty = $model->getDirty();
            if (empty($dirty)) return;

            // Exclude common fields
            $exclude = ['updated_at', 'remember_token', 'password'];
            if (property_exists($model, 'excludeFromLogs')) {
                $exclude = array_merge($exclude, $model->excludeFromLogs);
            }

            $old = [];
            $new = [];

            foreach ($dirty as $key => $value) {
                if (in_array($key, $exclude)) continue;
                $old[$key] = $model->getOriginal($key);
                $new[$key] = $value;
            }

            if (empty($new)) return;

            $properties = [
                'old' => $old,
                'new' => $new,
            ];

            static::logActivity($model, 'UPDATED', 'Memperbarui ' . $model->getSubjectName(), $properties);
        });

        static::deleted(function ($model) {
            static::logActivity($model, 'DELETED', 'Menghapus ' . $model->getSubjectName());
        });
    }

    protected static function logActivity($model, $action, $description, $properties = null)
    {
        $userId = Auth::id();
        
        ActivityLog::create([
            'user_id' => $userId,
            'action' => $action,
            'subject_type' => get_class($model),
            'subject_id' => $model->getKey(),
            'description' => $description . ' (' . ($model->getRecordLabel() ?? $model->getKey()) . ')',
            'properties' => $properties,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    protected function getSubjectName()
    {
        $class = class_basename($this);
        // Translate class names to friendly Indonesian names
        $map = [
            'Item' => 'Data Barang',
            'User' => 'Akun Pengguna',
            'Role' => 'Role Hak Akses',
            'StockMutation' => 'Mutasi Gudang',
            'WorkOrder' => 'Work Order Produksi',
            'StockTransaction' => 'Transaksi Stok',
        ];

        return $map[$class] ?? $class;
    }

    protected function getRecordLabel()
    {
        if (isset($this->name)) return $this->name;
        if (isset($this->reference_no)) return $this->reference_no;
        if (isset($this->wo_number)) return $this->wo_number;
        if (isset($this->code)) return $this->code;
        return null;
    }
}
