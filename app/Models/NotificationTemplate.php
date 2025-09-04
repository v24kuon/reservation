<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class NotificationTemplate extends Model
{
    /** @use HasFactory<\Database\Factories\NotificationTemplateFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'subject',
        'body_text',
        'variables',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get available placeholder variables grouped by related table.
     * Keys are placeholder names (e.g. users_name), values are raw column names for simplicity.
     * This is the simplest implementation using Schema::getColumnListing.
     *
     * @return array<string,array<string,string>>
     */
    public static function getAvailableVariablesByTable(): array
    {
        $tables = [
            'users' => 'ユーザー',
            'lessons' => 'レッスン',
            'stores' => '店舗',
            'lesson_schedules' => 'スケジュール',
            'reservations' => '予約',
            'subscription_plans' => 'プラン',
            'user_subscriptions' => 'ユーザーサブスク',
        ];

        $systemColumns = ['id', 'created_at', 'updated_at', 'deleted_at'];
        $sensitiveByTable = [
            'users' => ['password', 'remember_token', 'email_verified_at'],
        ];

        $groups = [];
        foreach ($tables as $table => $label) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $columns = Schema::getColumnListing($table);
            foreach ($columns as $column) {
                if (in_array($column, $systemColumns, true)) {
                    continue;
                }
                if (isset($sensitiveByTable[$table]) && in_array($column, $sensitiveByTable[$table], true)) {
                    continue;
                }
                $placeholder = $table.'_'.$column; // e.g. users_name
                $groups[$table][$placeholder] = $column;
            }
        }

        return $groups;
    }

    /**
     * Table labels for UI grouping.
     *
     * @return array<string,string>
     */
    public static function getTableLabels(): array
    {
        return [
            'users' => 'ユーザー',
            'lessons' => 'レッスン',
            'stores' => '店舗',
            'lesson_schedules' => 'スケジュール',
            'reservations' => '予約',
            'subscription_plans' => 'プラン',
            'user_subscriptions' => 'ユーザーサブスク',
        ];
    }
}
