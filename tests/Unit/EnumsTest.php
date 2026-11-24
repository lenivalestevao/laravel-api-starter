<?php

namespace Tests\Unit;

use App\Enums\ProjectStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use PHPUnit\Framework\TestCase;

class EnumsTest extends TestCase
{
    public function test_task_status_values(): void
    {
        $this->assertSame(['todo', 'doing', 'done'], array_column(TaskStatus::cases(), 'value'));
    }

    public function test_task_priority_values(): void
    {
        $this->assertSame(['low', 'medium', 'high'], array_column(TaskPriority::cases(), 'value'));
    }

    public function test_project_status_values(): void
    {
        $this->assertSame(['active', 'archived'], array_column(ProjectStatus::cases(), 'value'));
    }

    public function test_try_from_rejects_unknown_values(): void
    {
        $this->assertNull(TaskStatus::tryFrom('blocked'));
        $this->assertSame(TaskStatus::Done, TaskStatus::tryFrom('done'));
    }
}
