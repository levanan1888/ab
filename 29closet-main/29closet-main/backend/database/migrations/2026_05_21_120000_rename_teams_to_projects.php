<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('teams') && !Schema::hasTable('projects')) {
            Schema::rename('teams', 'projects');
        }

        if (Schema::hasTable('team_user') && !Schema::hasTable('project_user')) {
            Schema::rename('team_user', 'project_user');
        }

        if (Schema::hasTable('project_user') && Schema::hasColumn('project_user', 'team_id')) {
            Schema::table('project_user', function (Blueprint $table): void {
                $table->renameColumn('team_id', 'project_id');
            });
        }

        if (Schema::hasTable('project_user') && Schema::hasColumn('project_user', 'role_in_team')) {
            Schema::table('project_user', function (Blueprint $table): void {
                $table->renameColumn('role_in_team', 'role_in_project');
            });
        }

        if (Schema::hasTable('tasks') && Schema::hasColumn('tasks', 'team_id')) {
            Schema::table('tasks', function (Blueprint $table): void {
                $table->renameColumn('team_id', 'project_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tasks') && Schema::hasColumn('tasks', 'project_id')) {
            Schema::table('tasks', function (Blueprint $table): void {
                $table->renameColumn('project_id', 'team_id');
            });
        }

        if (Schema::hasTable('project_user') && Schema::hasColumn('project_user', 'role_in_project')) {
            Schema::table('project_user', function (Blueprint $table): void {
                $table->renameColumn('role_in_project', 'role_in_team');
            });
        }

        if (Schema::hasTable('project_user') && Schema::hasColumn('project_user', 'project_id')) {
            Schema::table('project_user', function (Blueprint $table): void {
                $table->renameColumn('project_id', 'team_id');
            });
        }

        if (Schema::hasTable('project_user') && !Schema::hasTable('team_user')) {
            Schema::rename('project_user', 'team_user');
        }

        if (Schema::hasTable('projects') && !Schema::hasTable('teams')) {
            Schema::rename('projects', 'teams');
        }
    }
};
