<?php

namespace App\Filament\Pages;

use App\Models\Project;
use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;

class TeamMembersPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Thành viên theo nhóm';

    protected static ?string $title = 'Thành viên theo nhóm';

    protected static ?string $navigationGroup = 'Nhóm làm việc';

    protected static string $view = 'filament.pages.team-members-page';

    #[Url]
    public string $search = '';

    #[Url]
    public ?int $group_id = null;

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->is_leader() === true;
    }

    public function getUsersWithTeams(): Collection
    {
        $query = User::query()->with(['projects:id,name']);

        if ($this->search !== '') {
            $query->where(function ($builder): void {
                $builder->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if (! empty($this->group_id)) {
            $query->whereHas('projects', function ($builder): void {
                $builder->where('projects.id', $this->group_id);
            });
        }

        return $query->orderBy('name')->get();
    }

    public function getGroupOptions(): array
    {
        return Project::query()->orderBy('name')->pluck('name', 'id')->toArray();
    }
}
